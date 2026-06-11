<?php

namespace App\Actions\Dashboard\Builders;

use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Actions\Dashboard\Support\Delta;
use App\Actions\Dashboard\Support\TicketMetricQueries;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class AdminDashboard
{
    public function __construct(
        private readonly TicketMetricQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(DashboardPeriod $period): array
    {
        $all = fn (): Builder => Ticket::query();

        $active = $all()->whereIn('status', TicketStatus::activeCases())->count();
        $unassigned = $all()->whereNull('assigned_to')->whereIn('status', TicketStatus::activeCases())->count();
        $pendingApproval = $all()->where('status', TicketStatus::PendingApproval)->count();
        $slaBreached = $all()
            ->whereIn('status', TicketStatus::activeCases())
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '<', Date::now())
            ->count();

        $created = $this->queries->countCreated($all(), $period->start(), $period->end());
        $createdPrev = $this->queries->countCreated($all(), $period->previousStart(), $period->previousEnd());
        $resolved = $this->queries->countResolved($all(), $period->start(), $period->end());
        $resolvedPrev = $this->queries->countResolved($all(), $period->previousStart(), $period->previousEnd());

        $compliance = $this->queries->compliance($all(), $period->start(), $period->end());
        $compliancePrev = $this->queries->compliance($all(), $period->previousStart(), $period->previousEnd());
        $complianceDelta = Delta::compute($compliance['rate'], $compliancePrev['rate']);

        return [
            'live' => [
                ['key' => 'active', 'value' => $active],
                ['key' => 'unassigned', 'value' => $unassigned],
                ['key' => 'pending_approval', 'value' => $pendingApproval],
                ['key' => 'sla_breached', 'value' => $slaBreached],
            ],
            'periodMetrics' => [
                $this->metric('created', $created, $createdPrev, 'neutral'),
                $this->metric('resolved', $resolved, $resolvedPrev, 'higher_is_better'),
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
                'points' => $this->queries->trend($all(), $all(), $period),
            ],
            'breakdown' => $this->priorityBreakdown(),
        ];
    }

    /**
     * @return array{key: string, value: int, previous: int, deltaPercent: int|null, direction: string, sentiment: string}
     */
    private function metric(string $key, int $value, int $previous, string $sentiment): array
    {
        $delta = Delta::compute($value, $previous);

        return [
            'key' => $key,
            'value' => $value,
            'previous' => $previous,
            'deltaPercent' => $delta['deltaPercent'],
            'direction' => $delta['direction'],
            'sentiment' => $sentiment,
        ];
    }

    /**
     * @return array{type: string, segments: list<array{key: string, value: int}>}
     */
    private function priorityBreakdown(): array
    {
        $counts = Ticket::query()
            ->selectRaw('priority, count(*) as aggregate')
            ->groupBy('priority')
            ->pluck('aggregate', 'priority')
            ->all();

        $segments = [];
        foreach (TicketPriority::cases() as $priority) {
            $segments[] = ['key' => $priority->value, 'value' => (int) ($counts[$priority->value] ?? 0)];
        }

        return ['type' => 'priority', 'segments' => $segments];
    }
}
