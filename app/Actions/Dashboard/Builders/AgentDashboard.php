<?php

namespace App\Actions\Dashboard\Builders;

use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Actions\Dashboard\Support\Delta;
use App\Actions\Dashboard\Support\TicketMetricQueries;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class AgentDashboard
{
    public function __construct(
        private readonly TicketMetricQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(User $user, DashboardPeriod $period): array
    {
        $assignedScope = fn (): Builder => Ticket::query()->where('assigned_to', $user->id);

        $assigned = $assignedScope()->whereIn('status', TicketStatus::activeCases())->count();
        $unassigned = Ticket::query()->whereNull('assigned_to')->whereIn('status', TicketStatus::activeCases())->count();
        $pendingApproval = Ticket::query()->where('status', TicketStatus::PendingApproval)->count();
        $slaBreached = Ticket::query()
            ->whereIn('status', TicketStatus::activeCases())
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '<', Date::now())
            ->count();

        $resolved = $this->queries->countResolved($assignedScope(), $period->start(), $period->end());
        $resolvedPrev = $this->queries->countResolved($assignedScope(), $period->previousStart(), $period->previousEnd());
        $resolvedDelta = Delta::compute($resolved, $resolvedPrev);

        $compliance = $this->queries->compliance($assignedScope(), $period->start(), $period->end());
        $compliancePrev = $this->queries->compliance($assignedScope(), $period->previousStart(), $period->previousEnd());
        $complianceDelta = Delta::compute($compliance['rate'], $compliancePrev['rate']);

        return [
            'live' => [
                ['key' => 'assigned', 'value' => $assigned],
                ['key' => 'unassigned', 'value' => $unassigned],
                ['key' => 'pending_approval', 'value' => $pendingApproval],
                ['key' => 'sla_breached', 'value' => $slaBreached],
            ],
            'periodMetrics' => [
                [
                    'key' => 'resolved',
                    'value' => $resolved,
                    'previous' => $resolvedPrev,
                    'deltaPercent' => $resolvedDelta['deltaPercent'],
                    'direction' => $resolvedDelta['direction'],
                    'sentiment' => 'higher_is_better',
                ],
            ],
            'compliance' => [
                'rate' => $compliance['rate'],
                'resolvedWithinDue' => $compliance['resolvedWithinDue'],
                'totalResolved' => $compliance['totalResolved'],
                'previousRate' => $compliancePrev['rate'],
                'deltaPercent' => $complianceDelta['deltaPercent'],
                'direction' => $complianceDelta['direction'],
            ],
            'trend' => [
                'granularity' => $period->granularity(),
                'points' => $this->queries->trend($assignedScope(), $assignedScope(), $period),
            ],
            'breakdown' => $this->statusBreakdown(),
        ];
    }

    /**
     * @return array{type: string, segments: list<array{key: string, value: int}>}
     */
    private function statusBreakdown(): array
    {
        $counts = Ticket::query()
            ->whereIn('status', TicketStatus::activeCases())
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $segments = [];
        foreach (TicketStatus::activeCases() as $status) {
            $segments[] = ['key' => $status->value, 'value' => (int) ($counts[$status->value] ?? 0)];
        }

        return ['type' => 'status', 'segments' => $segments];
    }
}
