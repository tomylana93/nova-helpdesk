<?php

use App\Enums\AdminPermission;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Events\SlaEscalated;
use App\Events\TicketCreated;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\Event;
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

test('ticket creation sends notifications and broadcasts', function (): void {
    Notification::fake();
    Event::fake([TicketCreated::class, SlaEscalated::class]);

    $requester = User::factory()->create();
    $requester->syncRoles([UserRole::Requester->value]);

    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $category = TicketCategory::factory()->create();

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'Printer Jammed',
            'description' => 'Help with printer please.',
            'priority' => TicketPriority::Low->value,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'category_id' => $category->id,
        ]);

    $response->assertRedirect();

    $ticket = Ticket::query()->latest()->first();

    // Verify requester was notified
    Notification::assertSentTo($requester, TicketNotification::class, function ($notification) use ($ticket): bool {
        return $notification->type === 'created' && $notification->ticket->id === $ticket->id;
    });

    // Verify unassigned ticket creation broadcasts Event
    Event::assertDispatched(TicketCreated::class, function ($event) use ($ticket): bool {
        return $event->ticket->id === $ticket->id;
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

    $admin = User::factory()->create();
    $admin->syncRoles([UserRole::SuperAdmin->value]);

    $requester = User::factory()->create();
    $requester->syncRoles([UserRole::Requester->value]);

    $ticket1 = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'status' => TicketStatus::WaitingForApproval,
    ]);

    // Approve
    $this->actingAs($admin)
        ->post(route('tickets.approve', $ticket1), ['decision_note' => 'Approved'])
        ->assertRedirect();

    Notification::assertSentTo($requester, TicketNotification::class, function ($notification): bool {
        return $notification->type === 'approval_decision';
    });

    // Reject
    Notification::fake();
    $ticket2 = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'status' => TicketStatus::WaitingForApproval,
    ]);

    $this->actingAs($admin)
        ->post(route('tickets.reject', $ticket2), ['decision_note' => 'Rejected'])
        ->assertRedirect();

    Notification::assertSentTo($requester, TicketNotification::class, function ($notification): bool {
        return $notification->type === 'approval_decision';
    });
});

test('check-sla scheduled command checks warnings and breaches', function (): void {
    Notification::fake();
    Event::fake([TicketCreated::class, SlaEscalated::class]);

    $agent = User::factory()->create();
    $agent->syncRoles([UserRole::ItAgent->value]);

    $requester = User::factory()->create();

    // 1. SLA Warning Ticket (due in 25 minutes)
    $warningTicket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'first_response_due_at' => now()->addMinutes(25),
        'status' => TicketStatus::New,
    ]);

    // 2. SLA Breached Ticket (due 10 minutes ago)
    $breachedTicket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'resolution_due_at' => now()->subMinutes(10),
        'status' => TicketStatus::InProgress,
    ]);

    // Run SLA check command
    $this->artisan('helpdesk:check-sla')->assertSuccessful();

    // Verify warnings triggered
    Event::assertDispatched(SlaEscalated::class, function ($event) use ($warningTicket): bool {
        return $event->ticket->id === $warningTicket->id && $event->escalationType === 'warning';
    });

    // Verify breaches triggered
    Event::assertDispatched(SlaEscalated::class, function ($event) use ($breachedTicket): bool {
        return $event->ticket->id === $breachedTicket->id && $event->escalationType === 'breached';
    });

    // Verify activities logged to prevent duplicate alerts
    expect($warningTicket->activities()->where('event', 'sla_first_response_warning_sent')->exists())->toBeTrue();
    expect($breachedTicket->activities()->where('event', 'sla_resolution_breached')->exists())->toBeTrue();

    // Re-run command: should not trigger any new events (due to logged activities)
    Event::fake([TicketCreated::class, SlaEscalated::class]);
    $this->artisan('helpdesk:check-sla')->assertSuccessful();
    Event::assertNotDispatched(SlaEscalated::class);
});
