<?php

use App\Enums\AdminPermission;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createAgentUser(): User
{
    $role = Role::findOrCreate(UserRole::ItAgent->value, 'web');
    $permissions = [
        AdminPermission::ViewTickets->value,
        AdminPermission::CreateTickets->value,
        AdminPermission::UpdateTickets->value,
        AdminPermission::ManageApprovals->value,
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role->syncPermissions($permissions);

    return tap(User::factory()->create(), fn ($u) => $u->syncRoles([UserRole::ItAgent->value]));
}

function createRequesterUser(): User
{
    $role = Role::findOrCreate(UserRole::Requester->value, 'web');
    $permissions = [
        AdminPermission::ViewTickets->value,
        AdminPermission::CreateTickets->value,
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role->syncPermissions($permissions);

    return tap(User::factory()->create(), fn ($u) => $u->syncRoles([UserRole::Requester->value]));
}

// --- INDEX ---

test('requester sees only their own tickets', function (): void {
    $requester = createRequesterUser();
    $other = User::factory()->create();

    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'My Ticket']);
    Ticket::factory()->create(['requester_id' => $other->id, 'subject' => 'Other Ticket']);

    $response = $this->actingAs($requester)->get(route('tickets.index'));

    $response->assertStatus(200);
});

test('agent can access ticket index', function (): void {
    $agent = createAgentUser();

    $response = $this->actingAs($agent)->get(route('tickets.index'));

    $response->assertStatus(200);
});

test('unauthenticated user is redirected from ticket index', function (): void {
    $this->get(route('tickets.index'))->assertRedirect(route('login'));
});

// --- CREATE / STORE ---

test('requester can submit an incident ticket', function (): void {
    $requester = createRequesterUser();

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'My printer is broken',
            'description' => 'Cannot print from any device.',
            'priority' => TicketPriority::Medium->value,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket = Ticket::query()->where('subject', 'My printer is broken')->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket?->requester_id)->toBe($requester->id)
        ->and($ticket?->status)->toBe(TicketStatus::New)
        ->and($ticket?->ticket_number)->toStartWith('INC-');
});

test('service_request ticket starts in waiting_for_approval status', function (): void {
    $requester = createRequesterUser();

    $this->actingAs($requester)->post(route('tickets.store'), [
        'type' => TicketType::ServiceRequest->value,
        'subject' => 'Need new laptop',
        'description' => 'My laptop is too old.',
        'priority' => TicketPriority::Low->value,
    ]);

    $ticket = Ticket::query()->where('subject', 'Need new laptop')->first();

    expect($ticket?->status)->toBe(TicketStatus::WaitingForApproval);
});

test('ticket submission validates required fields', function (): void {
    $requester = createRequesterUser();

    $response = $this
        ->actingAs($requester)
        ->from(route('tickets.create'))
        ->post(route('tickets.store'), []);

    $response->assertSessionHasErrors(['type', 'subject', 'description', 'priority']);
});

// --- SHOW ---

test('requester can view their own ticket', function (): void {
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $this->actingAs($requester)->get(route('tickets.show', $ticket))->assertStatus(200);
});

test('requester cannot view another users ticket', function (): void {
    $requester = createRequesterUser();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $other->id]);

    $this->actingAs($requester)->get(route('tickets.show', $ticket))->assertForbidden();
});

test('agent can view any ticket', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create();

    $this->actingAs($agent)->get(route('tickets.show', $ticket))->assertStatus(200);
});

// --- EDIT / UPDATE ---

test('agent can update a ticket', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::New]);

    $response = $this
        ->actingAs($agent)
        ->patch(route('tickets.update', $ticket), [
            'subject' => 'Updated subject',
            'description' => 'Updated description',
            'status' => TicketStatus::Triaged->value,
            'priority' => TicketPriority::High->value,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Triaged)
        ->and($ticket->priority)->toBe(TicketPriority::High);
});

test('requester cannot update a ticket', function (): void {
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $this->actingAs($requester)->patch(route('tickets.update', $ticket), [
        'subject' => 'Updated subject',
        'description' => 'Updated description',
        'status' => TicketStatus::Closed->value,
        'priority' => TicketPriority::Low->value,
    ])->assertForbidden();
});

// --- COMMENTS ---

test('requester can post a public comment on their ticket', function (): void {
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'I still have the issue.',
            'visibility' => 'public',
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    expect($ticket->comments()->where('body', 'I still have the issue.')->exists())->toBeTrue();
});

// --- APPROVAL ---

test('agent can approve a waiting_for_approval ticket', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::WaitingForApproval]);

    $this
        ->actingAs($agent)
        ->post(route('tickets.approve', $ticket), ['decision_note' => 'Looks good'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::InProgress);
    expect($ticket->approval)->not->toBeNull();
    expect($ticket->approval?->status)->toBe('approved');
});

test('agent can reject a waiting_for_approval ticket', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::WaitingForApproval]);

    $this
        ->actingAs($agent)
        ->post(route('tickets.reject', $ticket), ['decision_note' => 'Not justified'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Closed);
    expect($ticket->approval?->status)->toBe('rejected');
});
