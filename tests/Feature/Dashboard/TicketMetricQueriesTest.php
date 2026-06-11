<?php

use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Actions\Dashboard\Support\TicketMetricQueries;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->queries = app(TicketMetricQueries::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('counts tickets created within the period', function (): void {
    Ticket::factory()->count(3)->create(['created_at' => Date::parse('2026-06-10 09:00:00')]);
    Ticket::factory()->create(['created_at' => Date::parse('2026-05-10 09:00:00')]); // out of period

    $count = $this->queries->countCreated(Ticket::query(), $this->period->start(), $this->period->end());

    expect($count)->toBe(3);
});

test('counts tickets resolved within the period', function (): void {
    Ticket::factory()->count(2)->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
    ]);
    Ticket::factory()->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-05-12 09:00:00'),
    ]);

    $count = $this->queries->countResolved(Ticket::query(), $this->period->start(), $this->period->end());

    expect($count)->toBe(2);
});

test('computes compliance breakdown within the period', function (): void {
    // 3 resolved in period: 2 within due, 1 breached
    Ticket::factory()->count(2)->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-13 09:00:00'),
    ]);
    Ticket::factory()->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-11 09:00:00'),
    ]);

    $result = $this->queries->compliance(Ticket::query(), $this->period->start(), $this->period->end());

    expect($result)->toBe([
        'resolvedWithinDue' => 2,
        'totalResolved' => 3,
        'rate' => 67,
    ]);
});

test('daily trend buckets created and resolved by day', function (): void {
    Ticket::factory()->create(['created_at' => Date::parse('2026-06-01 09:00:00')]);
    Ticket::factory()->create(['created_at' => Date::parse('2026-06-01 14:00:00')]);
    Ticket::factory()->create([
        'created_at' => Date::parse('2026-05-20 09:00:00'),
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-02 09:00:00'),
    ]);

    $points = $this->queries->trend(Ticket::query(), Ticket::query(), $this->period);

    expect($points)->toHaveCount(30) // June has 30 days
        ->and($points[0])->toBe(['label' => '01', 'created' => 2, 'resolved' => 0])
        ->and($points[1])->toBe(['label' => '02', 'created' => 0, 'resolved' => 1]);
});
