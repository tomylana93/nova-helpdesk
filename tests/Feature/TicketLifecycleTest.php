<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

function lifecycleTicket(TicketStatus $status, string $requesterId, string $agentId): Ticket
{
    return Ticket::factory()->create([
        'requester_id' => $requesterId,
        'assigned_to' => $agentId,
        'status' => $status,
        'category_id' => TicketCategory::factory()->create()->id,
    ]);
}

function updatePayload(Ticket $ticket, TicketStatus $status): array
{
    return [
        'subject' => $ticket->subject,
        'description' => $ticket->description,
        'status' => $status->value,
        'priority' => TicketPriority::High->value,
        'category_id' => $ticket->category_id,
    ];
}

// --- Agent transitions via the edit form ---

test('agent resolving a ticket stamps resolved_at and notifies the requester', function (): void {
    Notification::fake();
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::InProgress, $requester->id, $agent->id);

    $this->actingAs($agent)
        ->patch(route('tickets.update', $ticket), updatePayload($ticket, TicketStatus::Resolved))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Resolved)
        ->and($ticket->resolved_at)->not->toBeNull();

    Notification::assertSentTo($requester, TicketNotification::class, fn ($n): bool => $n->type === 'status_changed');
});

test('an illegal status transition is rejected', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Open, $requester->id, $agent->id);

    $this->actingAs($agent)
        ->patch(route('tickets.update', $ticket), updatePayload($ticket, TicketStatus::Resolved))
        ->assertSessionHasErrors('status');

    expect($ticket->fresh()->status)->toBe(TicketStatus::Open);
});

// --- Requester reopen / confirm ---

test('requester can reopen a resolved ticket and the agent is notified', function (): void {
    Notification::fake();
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Resolved, $requester->id, $agent->id);
    $ticket->update(['resolved_at' => now()]);

    $this->actingAs($requester)
        ->post(route('tickets.reopen', $ticket))
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Reopened)
        ->and($ticket->resolved_at)->toBeNull();

    Notification::assertSentTo($agent, TicketNotification::class, fn ($n): bool => $n->type === 'status_changed');
});

test('requester can confirm a resolved ticket which closes it', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Resolved, $requester->id, $agent->id);

    $this->actingAs($requester)
        ->post(route('tickets.confirm-resolved', $ticket))
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Closed)
        ->and($ticket->closed_at)->not->toBeNull();
});

test('requester cannot reopen a ticket they do not own', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $other = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Resolved, $requester->id, $agent->id);

    $this->actingAs($other)
        ->post(route('tickets.reopen', $ticket))
        ->assertForbidden();
});

test('requester cannot reopen a ticket that is still in progress', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::InProgress, $requester->id, $agent->id);

    $this->actingAs($requester)
        ->post(route('tickets.reopen', $ticket))
        ->assertForbidden();
});

// --- Agent approval of service requests ---

test('an it agent can approve a pending service request', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::PendingApproval, $requester->id, $agent->id);

    $this->actingAs($agent)
        ->post(route('tickets.approve', $ticket), ['decision_note' => 'Approved'])
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe(TicketStatus::InProgress);
});

test('a non-assigned it agent cannot approve a pending service request', function (): void {
    $assignedAgent = createAgentUser();
    $otherAgent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::PendingApproval, $requester->id, $assignedAgent->id);

    $this->actingAs($otherAgent)
        ->post(route('tickets.approve', $ticket), ['decision_note' => 'Approved'])
        ->assertForbidden();

    expect($ticket->fresh()->status)->toBe(TicketStatus::PendingApproval);
});

// --- Agent transitions via the dedicated lifecycle endpoint ---

test('an agent transitions a ticket through the lifecycle endpoint and notifies the requester', function (): void {
    Notification::fake();
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Open, $requester->id, $agent->id);

    $this->actingAs($agent)
        ->post(route('tickets.transition', $ticket), ['status' => TicketStatus::InProgress->value])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe(TicketStatus::InProgress);
    Notification::assertSentTo($requester, TicketNotification::class, fn ($n): bool => $n->type === 'status_changed');
});

test('an agent transition to in progress marks first response once', function (): void {
    $this->travelTo(Date::parse('2026-06-11 10:00:00'));

    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Open, $requester->id, $agent->id);

    $this->actingAs($agent)
        ->post(route('tickets.transition', $ticket), ['status' => TicketStatus::InProgress->value])
        ->assertRedirect();

    expect($ticket->fresh()->first_responded_at?->toDateTimeString())->toBe('2026-06-11 10:00:00');

    $this->travelTo(Date::parse('2026-06-11 10:10:00'));

    $this->actingAs($agent)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'Working on this now.',
            'visibility' => 'public',
        ])
        ->assertRedirect();

    expect($ticket->fresh()->first_responded_at?->toDateTimeString())->toBe('2026-06-11 10:00:00');
});

test('the lifecycle endpoint rejects an illegal status move', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Open, $requester->id, $agent->id);

    $this->actingAs($agent)
        ->post(route('tickets.transition', $ticket), ['status' => TicketStatus::Resolved->value])
        ->assertSessionHasErrors('status');

    expect($ticket->fresh()->status)->toBe(TicketStatus::Open);
});

test('a requester cannot use the agent transition endpoint', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = lifecycleTicket(TicketStatus::Open, $requester->id, $agent->id);

    $this->actingAs($requester)
        ->post(route('tickets.transition', $ticket), ['status' => TicketStatus::InProgress->value])
        ->assertForbidden();
});
