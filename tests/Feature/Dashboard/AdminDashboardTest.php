<?php

use App\Actions\Dashboard\Builders\AdminDashboard;
use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->builder = app(AdminDashboard::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('builds system-wide admin payload', function (): void {
    Ticket::factory()->count(2)->create(['status' => TicketStatus::Open]); // active
    Ticket::factory()->create(['status' => TicketStatus::PendingApproval]);
    Ticket::factory()->create(['assigned_to' => null, 'status' => TicketStatus::InProgress]);
    Ticket::factory()->create([
        'created_at' => Date::parse('2026-06-05 09:00:00'),
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-13 09:00:00'),
    ]);

    $payload = $this->builder->handle($this->period);

    $live = collect($payload['live'])->keyBy('key');
    expect($live)->toHaveKeys(['active', 'unassigned', 'pending_approval', 'sla_breached'])
        ->and($payload['compliance']['rate'])->toBe(100)
        ->and($payload['breakdown']['type'])->toBe('priority')
        ->and(collect($payload['periodMetrics'])->pluck('key')->all())->toBe(['created', 'resolved']);
});
