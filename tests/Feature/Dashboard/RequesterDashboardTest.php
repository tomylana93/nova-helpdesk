<?php

use App\Actions\Dashboard\Builders\RequesterDashboard;
use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->builder = app(RequesterDashboard::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('builds requester payload scoped to own tickets', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    // own: 2 active, 1 resolved this month
    Ticket::factory()->create(['requester_id' => $user->id, 'status' => TicketStatus::Open]);
    Ticket::factory()->create(['requester_id' => $user->id, 'status' => TicketStatus::InProgress]);
    Ticket::factory()->create([
        'requester_id' => $user->id,
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'priority' => TicketPriority::High,
    ]);
    // someone else's ticket must be excluded
    Ticket::factory()->create(['requester_id' => $other->id, 'status' => TicketStatus::Open]);

    $payload = $this->builder->handle($user, $this->period);

    expect($payload['live'])->toBe([['key' => 'active', 'value' => 2]])
        ->and($payload['compliance'])->toBeNull()
        ->and($payload['breakdown']['type'])->toBe('priority')
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'created')['value'])->toBe(3)
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'resolved')['value'])->toBe(1)
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'created')['sentiment'])->toBe('neutral')
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'resolved')['sentiment'])->toBe('higher_is_better');
});
