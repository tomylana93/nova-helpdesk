<?php

namespace App\Actions\Dashboard;

use App\Actions\Dashboard\Builders\AdminDashboard;
use App\Actions\Dashboard\Builders\AgentDashboard;
use App\Actions\Dashboard\Builders\RequesterDashboard;
use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Enums\UserRole;
use App\Models\User;

class GetDashboardData
{
    public function __construct(
        private readonly RequesterDashboard $requester,
        private readonly AgentDashboard $agent,
        private readonly AdminDashboard $admin,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(User $user, DashboardPeriod $period): array
    {
        if ($user->hasRole(UserRole::ItAgent->value)) {
            return ['role' => 'it_agent', 'period' => $period->toArray(), ...$this->agent->handle($user, $period)];
        }

        if ($user->hasRole(UserRole::SuperAdmin->value)) {
            return ['role' => UserRole::SuperAdmin->value, 'period' => $period->toArray(), ...$this->admin->handle($period)];
        }

        if ($user->hasRole(UserRole::Auditor->value)) {
            return ['role' => UserRole::Auditor->value, 'period' => $period->toArray(), ...$this->admin->handle($period)];
        }

        return ['role' => 'requester', 'period' => $period->toArray(), ...$this->requester->handle($user, $period)];
    }
}
