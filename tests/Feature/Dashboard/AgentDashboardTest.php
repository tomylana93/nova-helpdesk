<?php

use App\Actions\Dashboard\Builders\AgentDashboard;
use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->builder = app(AgentDashboard::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('builds agent payload with live snapshot and compliance gauge', function (): void {
    $agent = User::factory()->create();
    $other = User::factory()->create();

    Ticket::factory()->create(['assigned_to' => $agent->id, 'status' => TicketStatus::InProgress]); // assigned active
    Ticket::factory()->create(['assigned_to' => null, 'status' => TicketStatus::Open]); // unassigned active
    // assigned to $other so they do not inflate the unassigned pool
    Ticket::factory()->create(['assigned_to' => $other->id, 'status' => TicketStatus::PendingApproval]); // pending approval
    Ticket::factory()->create([
        'assigned_to' => $other->id,
        'status' => TicketStatus::Open,
        'resolution_due_at' => Date::parse('2026-06-10 09:00:00'), // overdue -> breached
    ]);

    // resolved-by-me in period: 1 within due
    Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-13 09:00:00'),
    ]);

    $payload = $this->builder->handle($agent, $this->period);

    $live = collect($payload['live'])->keyBy('key');
    expect($live['assigned']['value'])->toBe(1)
        ->and($live['unassigned']['value'])->toBe(1)
        ->and($live['pending_approval']['value'])->toBe(1)
        ->and($live['sla_breached']['value'])->toBe(1)
        ->and($payload['compliance']['rate'])->toBe(100)
        ->and($payload['compliance']['totalResolved'])->toBe(1)
        ->and($payload['breakdown']['type'])->toBe('status')
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'resolved')['value'])->toBe(1);
});
