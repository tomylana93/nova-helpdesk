<?php

use App\Actions\Dashboard\GetDashboardData;
use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->action = app(GetDashboardData::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('routes requester to requester payload shape', function (): void {
    $user = createRequesterUser();

    $payload = $this->action->handle($user, $this->period);

    expect($payload['role'])->toBe('requester')
        ->and($payload['period'])->toBe(['mode' => 'monthly', 'month' => 6, 'year' => 2026])
        ->and($payload['compliance'])->toBeNull()
        ->and($payload)->toHaveKeys(['role', 'period', 'live', 'periodMetrics', 'compliance', 'trend', 'breakdown']);
});

test('routes agent to agent payload', function (): void {
    $payload = $this->action->handle(createAgentUser(), $this->period);

    expect($payload['role'])->toBe('it_agent')
        ->and($payload['compliance'])->not->toBeNull();
});

test('routes super admin to admin payload', function (): void {
    $payload = $this->action->handle(grantSuperAdmin(User::factory()->create()), $this->period);

    expect($payload['role'])->toBe('super_admin')
        ->and($payload['breakdown']['type'])->toBe('priority');
});
