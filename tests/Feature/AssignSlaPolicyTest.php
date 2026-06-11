<?php

use App\Actions\Helpdesk\AssignSlaPolicy;
use App\Enums\TicketPriority;
use App\Enums\TicketType;
use App\Models\SlaPolicy;
use App\Models\Ticket;

test('it assigns the most specific policy matching type and priority', function (): void {
    $specific = SlaPolicy::factory()->create([
        'ticket_type' => 'incident',
        'priority' => 'high',
        'first_response_target_minutes' => 30,
        'resolution_target_minutes' => 240,
    ]);
    // A less specific (type-agnostic) policy that must be ignored when the specific one matches.
    SlaPolicy::factory()->create([
        'ticket_type' => null,
        'priority' => 'high',
        'first_response_target_minutes' => 999,
        'resolution_target_minutes' => 999,
    ]);

    $ticket = Ticket::factory()->create([
        'type' => TicketType::Incident,
        'priority' => TicketPriority::High,
        'submitted_at' => now(),
    ]);

    app(AssignSlaPolicy::class)->handle($ticket->fresh());
    $ticket->refresh();

    expect($ticket->first_response_due_at->equalTo($ticket->submitted_at->copy()->addMinutes($specific->first_response_target_minutes)))->toBeTrue()
        ->and($ticket->resolution_due_at->equalTo($ticket->submitted_at->copy()->addMinutes($specific->resolution_target_minutes)))->toBeTrue();
});

test('it falls back to a type-agnostic policy for the priority', function (): void {
    $fallback = SlaPolicy::factory()->create([
        'ticket_type' => null,
        'priority' => 'low',
        'first_response_target_minutes' => 120,
        'resolution_target_minutes' => 1440,
    ]);

    $ticket = Ticket::factory()->create([
        'type' => TicketType::ServiceRequest,
        'priority' => TicketPriority::Low,
        'submitted_at' => now(),
    ]);

    app(AssignSlaPolicy::class)->handle($ticket->fresh());
    $ticket->refresh();

    expect($ticket->resolution_due_at->equalTo($ticket->submitted_at->copy()->addMinutes($fallback->resolution_target_minutes)))->toBeTrue();
});

test('it leaves due dates null when no policy matches', function (): void {
    $ticket = Ticket::factory()->create([
        'type' => TicketType::Incident,
        'priority' => TicketPriority::Critical,
        'submitted_at' => now(),
        'first_response_due_at' => null,
        'resolution_due_at' => null,
    ]);

    app(AssignSlaPolicy::class)->handle($ticket->fresh());
    $ticket->refresh();

    expect($ticket->first_response_due_at)->toBeNull()
        ->and($ticket->resolution_due_at)->toBeNull();
});
