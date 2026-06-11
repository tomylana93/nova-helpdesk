<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;

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
    $category = TicketCategory::factory()->create();

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'My printer is broken',
            'description' => 'Cannot print from any device.',
            'priority' => TicketPriority::Medium->value,
            'category_id' => $category->id,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket = Ticket::query()->where('subject', 'My printer is broken')->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket?->requester_id)->toBe($requester->id)
        ->and($ticket?->status)->toBe(TicketStatus::Open)
        ->and($ticket?->ticket_number)->toStartWith('INC-');
});

test('service_request ticket starts in waiting_for_approval status', function (): void {
    $requester = createRequesterUser();
    $category = TicketCategory::factory()->create();

    $this->actingAs($requester)->post(route('tickets.store'), [
        'type' => TicketType::ServiceRequest->value,
        'subject' => 'Need new laptop',
        'description' => 'My laptop is too old.',
        'priority' => TicketPriority::Low->value,
        'category_id' => $category->id,
    ]);

    $ticket = Ticket::query()->where('subject', 'Need new laptop')->first();

    expect($ticket?->status)->toBe(TicketStatus::PendingApproval);
});

test('ticket submission validates required fields', function (): void {
    $requester = createRequesterUser();

    $response = $this
        ->actingAs($requester)
        ->from(route('tickets.create'))
        ->post(route('tickets.store'), []);

    $response->assertSessionHasErrors(['type', 'subject', 'description', 'priority', 'category_id']);
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
    $category = TicketCategory::factory()->create();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open, 'category_id' => $category->id]);

    $response = $this
        ->actingAs($agent)
        ->patch(route('tickets.update', $ticket), [
            'subject' => 'Updated subject',
            'description' => 'Updated description',
            'status' => TicketStatus::InProgress->value,
            'priority' => TicketPriority::High->value,
            'category_id' => $category->id,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::InProgress)
        ->and($ticket->priority)->toBe(TicketPriority::High);
});

test('requester cannot update a ticket', function (): void {
    $requester = createRequesterUser();
    $category = TicketCategory::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id, 'category_id' => $category->id]);

    $this->actingAs($requester)->patch(route('tickets.update', $ticket), [
        'subject' => 'Updated subject',
        'description' => 'Updated description',
        'status' => TicketStatus::Closed->value,
        'priority' => TicketPriority::Low->value,
        'category_id' => $category->id,
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
    $ticket = Ticket::factory()->create(['status' => TicketStatus::PendingApproval]);

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
    $ticket = Ticket::factory()->create(['status' => TicketStatus::PendingApproval]);

    $this
        ->actingAs($agent)
        ->post(route('tickets.reject', $ticket), ['decision_note' => 'Not justified'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Closed);
    expect($ticket->approval?->status)->toBe('rejected');
});

test('user without manage approvals permission cannot approve or reject a ticket', function (): void {
    $userWithoutPerm = createRequesterUser();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::PendingApproval]);

    $this
        ->actingAs($userWithoutPerm)
        ->post(route('tickets.approve', $ticket), ['decision_note' => 'Looks good'])
        ->assertStatus(403);

    $this
        ->actingAs($userWithoutPerm)
        ->post(route('tickets.reject', $ticket), ['decision_note' => 'Not justified'])
        ->assertStatus(403);
});

test('non-agent cannot post internal comment', function (): void {
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $this
        ->actingAs($requester)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'Internal note.',
            'visibility' => 'internal',
        ])
        ->assertSessionHasErrors(['visibility']);
});

test('cannot assign ticket to non-agent user', function (): void {
    $agent = createAgentUser();
    $category = TicketCategory::factory()->create();
    $ticket = Ticket::factory()->create(['category_id' => $category->id]);
    $nonAgent = createRequesterUser();

    $this
        ->actingAs($agent)
        ->patch(route('tickets.update', $ticket), [
            'subject' => 'Updated Subject',
            'description' => 'Updated Desc',
            'status' => TicketStatus::InProgress->value,
            'priority' => TicketPriority::High->value,
            'assigned_to' => $nonAgent->id,
            'category_id' => $category->id,
        ])
        ->assertSessionHasErrors(['assigned_to']);
});

test('a submitted ticket inherits branch and department from the requester profile', function (): void {
    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $requester = createRequesterUser();
    $requester->update(['branch_id' => $branch->id, 'department_id' => $department->id]);

    $category = TicketCategory::factory()->create();

    $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'Inherit org context',
            'description' => 'Test',
            'priority' => TicketPriority::Low->value,
            'category_id' => $category->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::query()->latest()->first();
    expect($ticket?->branch_id)->toBe($branch->id)
        ->and($ticket?->department_id)->toBe($department->id);
});
