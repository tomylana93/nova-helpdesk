<?php

use App\Actions\Helpdesk\AssignSlaPolicy;
use App\Enums\TicketPriority;
use App\Enums\TicketType;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    // Clear cache to ensure HTTP calls are made during tests
    Cache::forget('id_holidays_2026');
    Date::setTestNow('2026-06-15 09:30:00'); // Monday June 15, 2026
});

afterEach(function (): void {
    Date::setTestNow();
});

test('it assigns the most specific policy matching type and priority', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

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
        'submitted_at' => Date::now(),
    ]);

    app(AssignSlaPolicy::class)->handle($ticket->fresh());
    $ticket->refresh();

    // 09:30:00 + 30 mins = 10:00:00
    // 09:30:00 + 240 mins = 150 mins in [09:30, 12:00) + 90 mins in [13:00, 18:00) = 14:30:00
    expect($ticket->first_response_due_at->toDateTimeString())->toBe('2026-06-15 10:00:00')
        ->and($ticket->resolution_due_at->toDateTimeString())->toBe('2026-06-15 14:30:00');
});

test('it falls back to a type-agnostic policy for the priority', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

    $fallback = SlaPolicy::factory()->create([
        'ticket_type' => null,
        'priority' => 'low',
        'first_response_target_minutes' => 120,
        'resolution_target_minutes' => 1440, // 3 full working days (3 * 480 mins)
    ]);

    $ticket = Ticket::factory()->create([
        'type' => TicketType::ServiceRequest,
        'priority' => TicketPriority::Low,
        'submitted_at' => Date::now(),
    ]);

    app(AssignSlaPolicy::class)->handle($ticket->fresh());
    $ticket->refresh();

    // 09:30:00 + 120 mins = 11:30:00
    // 09:30:00 + 1440 mins (3 working days):
    // Day 1 (Mon): 09:30 to 18:00 (minus break 12:00-13:00) is 7.5 working hours = 450 minutes. (990 mins remaining)
    // Day 2 (Tue): 8 working hours = 480 minutes. (510 mins remaining)
    // Day 3 (Wed): 8 working hours = 480 minutes. (30 mins remaining)
    // Day 4 (Thu): 30 working minutes starting at 09:00 = 09:30.
    // Total due: 2026-06-18 09:30:00
    expect($ticket->resolution_due_at->toDateTimeString())->toBe('2026-06-18 09:30:00');
});

test('it leaves due dates null when no policy matches', function (): void {
    $ticket = Ticket::factory()->create([
        'type' => TicketType::Incident,
        'priority' => TicketPriority::Critical,
        'submitted_at' => Date::now(),
        'first_response_due_at' => null,
        'resolution_due_at' => null,
    ]);

    app(AssignSlaPolicy::class)->handle($ticket->fresh());
    $ticket->refresh();

    expect($ticket->first_response_due_at)->toBeNull()
        ->and($ticket->resolution_due_at)->toBeNull();
});
