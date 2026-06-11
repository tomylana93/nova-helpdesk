<?php

use App\Enums\AdminPermission;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $requesterRole = Role::findOrCreate(UserRole::Requester->value, 'web');
    $agentRole = Role::findOrCreate(UserRole::ItAgent->value, 'web');
    $adminRole = Role::findOrCreate(UserRole::SuperAdmin->value, 'web');

    foreach (AdminPermission::cases() as $perm) {
        Permission::findOrCreate($perm->value, 'web');
    }

    $requesterRole->syncPermissions([
        AdminPermission::ViewTickets->value,
        AdminPermission::CreateTickets->value,
    ]);

    $agentRole->syncPermissions([
        AdminPermission::ViewTickets->value,
        AdminPermission::CreateTickets->value,
        AdminPermission::UpdateTickets->value,
        AdminPermission::ManageApprovals->value,
    ]);

    $adminRole->syncPermissions(AdminPermission::cases());
});

test('ticket creation notifies requester and auto-assigns to an agent', function (): void {
    Notification::fake();

    $agent = createAgentUser();
    $requester = createRequesterUser();

    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $category = TicketCategory::factory()->create();

    $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'Printer Jammed',
            'description' => 'Help with printer please.',
            'priority' => TicketPriority::Low->value,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'category_id' => $category->id,
        ])
        ->assertRedirect();

    $ticket = Ticket::query()->latest()->first();

    expect($ticket?->assigned_to)->toBe($agent->id);

    // Requester gets the submission confirmation.
    Notification::assertSentTo($requester, TicketNotification::class, function ($notification) use ($ticket): bool {
        return $notification->type === 'created' && $notification->ticket->id === $ticket->id;
    });

    // The auto-assigned agent gets the assignment notification.
    Notification::assertSentTo($agent, TicketNotification::class, function ($notification) use ($ticket): bool {
        return $notification->type === 'assigned' && $notification->ticket->id === $ticket->id;
    });
});

test('updating status sends notification to requester', function (): void {
    Notification::fake();

    $agent = User::factory()->create();
    $agent->syncRoles([UserRole::ItAgent->value]);

    $requester = User::factory()->create();
    $requester->syncRoles([UserRole::Requester->value]);

    $category = TicketCategory::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id, 'category_id' => $category->id]);

    $response = $this
        ->actingAs($agent)
        ->patch(route('tickets.update', $ticket), [
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'status' => TicketStatus::InProgress->value,
            'priority' => TicketPriority::High->value,
            'category_id' => $category->id,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    Notification::assertSentTo($requester, TicketNotification::class, function ($notification) use ($ticket): bool {
        return $notification->type === 'status_changed' && $notification->ticket->id === $ticket->id;
    });
});

test('adding comment sends notification', function (): void {
    Notification::fake();

    $requester = User::factory()->create();
    $requester->syncRoles([UserRole::Requester->value]);

    $agent = User::factory()->create();
    $agent->syncRoles([UserRole::ItAgent->value]);

    $ticket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to' => $agent->id,
    ]);

    // Requester comments -> notify assignee
    $this->actingAs($requester)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'Still broken.',
            'visibility' => 'public',
        ])
        ->assertRedirect();

    Notification::assertSentTo($agent, TicketNotification::class, function ($notification): bool {
        return $notification->type === 'comment';
    });

    // Agent comments -> notify requester
    $this->actingAs($agent)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'I will check now.',
            'visibility' => 'public',
        ])
        ->assertRedirect();

    Notification::assertSentTo($requester, TicketNotification::class, function ($notification): bool {
        return $notification->type === 'comment';
    });
});

test('approving and rejecting tickets sends notifications', function (): void {
    Notification::fake();

    $agent = createAgentUser();

    $requester = User::factory()->create();
    $requester->syncRoles([UserRole::Requester->value]);

    $ticket1 = Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'requester_id' => $requester->id,
        'status' => TicketStatus::PendingApproval,
    ]);

    // Approve
    $this->actingAs($agent)
        ->post(route('tickets.approve', $ticket1), ['decision_note' => 'Approved'])
        ->assertRedirect();

    Notification::assertSentTo($requester, TicketNotification::class, function ($notification): bool {
        return $notification->type === 'approval_decision';
    });

    // Reject
    Notification::fake();
    $ticket2 = Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'requester_id' => $requester->id,
        'status' => TicketStatus::PendingApproval,
    ]);

    $this->actingAs($agent)
        ->post(route('tickets.reject', $ticket2), ['decision_note' => 'Rejected'])
        ->assertRedirect();

    Notification::assertSentTo($requester, TicketNotification::class, function ($notification): bool {
        return $notification->type === 'approval_decision';
    });
});

test('check-sla notifies only the assigned agent for warnings and breaches', function (): void {
    Notification::fake();

    $agent = createAgentUser();
    $requester = User::factory()->create();

    // 1. SLA Warning Ticket (first response due in 25 minutes), assigned to the agent.
    $warningTicket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to' => $agent->id,
        'first_response_due_at' => now()->addMinutes(25),
        'status' => TicketStatus::Open,
    ]);

    // 2. SLA Breached Ticket (resolution due 10 minutes ago), assigned to the agent.
    $breachedTicket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to' => $agent->id,
        'resolution_due_at' => now()->subMinutes(10),
        'status' => TicketStatus::InProgress,
    ]);

    $this->artisan('helpdesk:check-sla')->assertSuccessful();

    // The assigned agent is the only SLA target (persisted notification, no role fan-out).
    Notification::assertSentTo($agent, TicketNotification::class, fn ($n): bool => $n->type === 'sla_warning' && $n->ticket->id === $warningTicket->id);
    Notification::assertSentTo($agent, TicketNotification::class, fn ($n): bool => $n->type === 'sla_breached' && $n->ticket->id === $breachedTicket->id);

    // Activities logged to prevent duplicate alerts.
    expect($warningTicket->activities()->where('event', 'sla_first_response_warning_sent')->exists())->toBeTrue();
    expect($breachedTicket->activities()->where('event', 'sla_resolution_breached')->exists())->toBeTrue();

    // Re-run command: idempotent via logged activities, so no new notifications.
    Notification::fake();
    $this->artisan('helpdesk:check-sla')->assertSuccessful();
    Notification::assertNothingSent();
});

test('check-sla notifies nobody for an unassigned overdue ticket', function (): void {
    Notification::fake();

    // An active agent exists, but the ticket is unassigned (no auto-assign target at creation).
    createAgentUser();
    $requester = User::factory()->create();

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to' => null,
        'resolution_due_at' => now()->subMinutes(10),
        'status' => TicketStatus::InProgress,
    ]);

    $this->artisan('helpdesk:check-sla')->assertSuccessful();

    // No role fan-out: unassigned overdue tickets surface via the agent's Overdue inbox filter instead.
    Notification::assertNothingSent();
});

test('new ticket is auto-assigned to an agent and the super admin is never notified', function (): void {
    Notification::fake();

    $requester = createRequesterUser();
    $agent = createAgentUser();
    $admin = tap(User::factory()->create(), fn (User $u): User => grantSuperAdmin($u));

    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $category = TicketCategory::factory()->create();

    $this->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'Printer Jammed',
            'description' => 'Help with printer please.',
            'priority' => TicketPriority::Low->value,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'category_id' => $category->id,
        ])
        ->assertRedirect();

    $ticket = Ticket::query()->latest()->first();
    expect($ticket?->assigned_to)->toBe($agent->id);

    // The assigned agent is notified; the super admin is never a lifecycle target.
    Notification::assertSentTo($agent, TicketNotification::class, fn ($n): bool => $n->type === 'assigned');
    Notification::assertNotSentTo($admin, TicketNotification::class);

    Notification::assertSentTo($requester, TicketNotification::class, fn ($n): bool => $n->type === 'created');
});

test('service request is assigned to an agent for approval, never the super admin', function (): void {
    Notification::fake();

    $requester = createRequesterUser();
    $agent = createAgentUser();
    $admin = tap(User::factory()->create(), fn (User $u): User => grantSuperAdmin($u));

    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $category = TicketCategory::factory()->create();

    $this->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::ServiceRequest->value,
            'subject' => 'New laptop request',
            'description' => 'Please provide a laptop.',
            'priority' => TicketPriority::Low->value,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'category_id' => $category->id,
        ])
        ->assertRedirect();

    $ticket = Ticket::query()->latest()->first();
    expect($ticket?->status)->toBe(TicketStatus::PendingApproval)
        ->and($ticket?->assigned_to)->toBe($agent->id);

    // The assigned agent is the approver and is notified; super admin is not involved.
    Notification::assertSentTo($agent, TicketNotification::class, fn ($n): bool => $n->type === 'approval_request');
    Notification::assertNotSentTo($admin, TicketNotification::class);
});

test('private notification channel only authorizes its owner', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Pull the registered authorization callback for the user notification channel.
    // The HTTP /broadcasting/auth endpoint cannot be used here because the test suite
    // runs on the "null" broadcaster, which bypasses channel authorization entirely.
    $broadcaster = Broadcast::driver();
    $channels = new ReflectionClass($broadcaster)->getProperty('channels');

    $callback = $channels->getValue($broadcaster)['App.Models.User.{id}'];

    // Owner is authorized for their own channel; another user is rejected.
    // Guards against the UUID-to-int collapse bug where every id became 0.
    expect($callback($userA, (string) $userA->id))->toBeTrue();
    expect($callback($userA, (string) $userB->id))->toBeFalse();
});
