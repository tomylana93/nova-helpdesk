<?php

namespace App\Actions\Dashboard\Support;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TicketMetricQueries
{
    /**
     * @param  Builder<Ticket>  $base
     */
    public function countCreated(Builder $base, CarbonInterface $start, CarbonInterface $end): int
    {
        return $base->clone()->whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * @param  Builder<Ticket>  $base
     */
    public function countResolved(Builder $base, CarbonInterface $start, CarbonInterface $end): int
    {
        return $base->clone()
            ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
            ->whereBetween('resolved_at', [$start, $end])
            ->count();
    }

    /**
     * @param  Builder<Ticket>  $base
     * @return array{resolvedWithinDue: int, totalResolved: int, rate: int}
     */
    public function compliance(Builder $base, CarbonInterface $start, CarbonInterface $end): array
    {
        $resolved = $base->clone()
            ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
            ->whereBetween('resolved_at', [$start, $end]);

        $totalResolved = $resolved->clone()->count();
        $withinDue = $resolved->clone()
            ->whereNotNull('resolution_due_at')
            ->whereColumn('resolved_at', '<=', 'resolution_due_at')
            ->count();

        return [
            'resolvedWithinDue' => $withinDue,
            'totalResolved' => $totalResolved,
            'rate' => $totalResolved > 0 ? (int) round(($withinDue / $totalResolved) * 100) : 100,
        ];
    }

    /**
     * Created-vs-resolved series bucketed by day (monthly) or month (yearly).
     * Buckets are dense: every day/month in the period is present, zero-filled.
     *
     * @param  Builder<Ticket>  $createdBase
     * @param  Builder<Ticket>  $resolvedBase
     * @return list<array{label: string, created: int, resolved: int}>
     */
    public function trend(Builder $createdBase, Builder $resolvedBase, DashboardPeriod $period): array
    {
        $granularity = $period->granularity();
        $start = $period->start();
        $end = $period->end();

        $created = $this->bucketCounts(
            $createdBase->clone()->whereBetween('created_at', [$start, $end]),
            'created_at',
            $granularity,
        );

        $resolved = $this->bucketCounts(
            $resolvedBase->clone()
                ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->whereBetween('resolved_at', [$start, $end]),
            'resolved_at',
            $granularity,
        );

        return $this->zeroFilled($period, $created, $resolved);
    }

    /**
     * @param  Builder<Ticket>  $query
     * @return array<string, int> keyed by bucket label (day-of-month "01".."31" or month "01".."12")
     */
    private function bucketCounts(Builder $query, string $column, string $granularity): array
    {
        $expr = $this->dateGroupExpression($column, $granularity);

        return $query
            ->selectRaw("{$expr} as bucket, count(*) as aggregate")
            ->groupBy('bucket')
            ->pluck('aggregate', 'bucket')
            ->mapWithKeys(fn ($count, $bucket): array => [(string) $bucket => (int) $count])
            ->all();
    }

    /**
     * Driver-portable expression that extracts a zero-padded day-of-month
     * (granularity=day) or month-of-year (granularity=month) from a column.
     */
    private function dateGroupExpression(string $column, string $granularity): string
    {
        $driver = DB::connection()->getDriverName();
        $isMonth = $granularity === 'month';

        return match ($driver) {
            'sqlite' => "strftime('%".($isMonth ? 'm' : 'd')."', {$column})",
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%".($isMonth ? 'm' : 'd')."')",
            'pgsql' => "to_char({$column}, '".($isMonth ? 'MM' : 'DD')."')",
            default => "strftime('%".($isMonth ? 'm' : 'd')."', {$column})",
        };
    }

    /**
     * @param  array<string, int>  $created
     * @param  array<string, int>  $resolved
     * @return list<array{label: string, created: int, resolved: int}>
     */
    private function zeroFilled(DashboardPeriod $period, array $created, array $resolved): array
    {
        $points = [];

        if ($period->granularity() === 'month') {
            for ($m = 1; $m <= 12; $m++) {
                $label = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                $points[] = ['label' => $label, 'created' => $created[$label] ?? 0, 'resolved' => $resolved[$label] ?? 0];
            }

            return $points;
        }

        $days = (int) $period->end()->day;
        for ($d = 1; $d <= $days; $d++) {
            $label = str_pad((string) $d, 2, '0', STR_PAD_LEFT);
            $points[] = ['label' => $label, 'created' => $created[$label] ?? 0, 'resolved' => $resolved[$label] ?? 0];
        }

        return $points;
    }
}
