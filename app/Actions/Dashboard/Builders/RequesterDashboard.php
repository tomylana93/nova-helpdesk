<?php

namespace App\Actions\Dashboard\Builders;

use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Actions\Dashboard\Support\Delta;
use App\Actions\Dashboard\Support\TicketMetricQueries;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RequesterDashboard
{
    public function __construct(
        private readonly TicketMetricQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(User $user, DashboardPeriod $period): array
    {
        $scope = fn (): Builder => Ticket::query()->where('requester_id', $user->id);

        $activeCount = $scope()->whereIn('status', TicketStatus::activeCases())->count();

        $created = $this->queries->countCreated($scope(), $period->start(), $period->end());
        $createdPrev = $this->queries->countCreated($scope(), $period->previousStart(), $period->previousEnd());
        $resolved = $this->queries->countResolved($scope(), $period->start(), $period->end());
        $resolvedPrev = $this->queries->countResolved($scope(), $period->previousStart(), $period->previousEnd());

        $myAssets = AssetResource::collection(
            Asset::query()
                ->with(['branch'])
                ->where('user_id', $user->id)
                ->get()
        )->resolve();

        return [
            'live' => [
                ['key' => 'active', 'value' => $activeCount],
            ],
            'periodMetrics' => [
                $this->metric('created', $created, $createdPrev, 'neutral'),
                $this->metric('resolved', $resolved, $resolvedPrev, 'higher_is_better'),
            ],
            'compliance' => null,
            'trend' => [
                'granularity' => $period->granularity(),
                'points' => $this->queries->trend($scope(), $scope(), $period),
            ],
            'breakdown' => $this->priorityBreakdown($scope()),
            'myAssets' => $myAssets,
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
     * @param  Builder<Ticket>  $scope
     * @return array{type: string, segments: list<array{key: string, value: int}>}
     */
    private function priorityBreakdown(Builder $scope): array
    {
        $counts = $scope->selectRaw('priority, count(*) as aggregate')
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
