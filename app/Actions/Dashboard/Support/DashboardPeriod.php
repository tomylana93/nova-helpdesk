<?php

namespace App\Actions\Dashboard\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class DashboardPeriod
{
    /**
     * @param  'monthly'|'yearly'  $mode
     */
    private function __construct(
        public readonly string $mode,
        public readonly ?int $month,
        public readonly int $year,
    ) {}

    public static function fromRequest(?string $mode, ?int $month, ?int $year): self
    {
        $now = Date::now();

        $mode = $mode === 'yearly' ? 'yearly' : 'monthly';

        $year = is_int($year) && $year >= 2000 && $year <= $now->year + 1
            ? $year
            : $now->year;

        if ($mode === 'yearly') {
            return new self('yearly', null, $year);
        }

        $month = is_int($month) && $month >= 1 && $month <= 12
            ? $month
            : (int) $now->month;

        return new self('monthly', $month, $year);
    }

    public function start(): Carbon
    {
        return $this->mode === 'yearly'
            ? Date::create($this->year, 1, 1)->startOfDay()
            : Date::create($this->year, $this->month, 1)->startOfMonth();
    }

    public function end(): Carbon
    {
        return $this->mode === 'yearly'
            ? Date::create($this->year, 12, 1)->endOfYear()
            : Date::create($this->year, $this->month, 1)->endOfMonth();
    }

    public function previousStart(): Carbon
    {
        return $this->mode === 'yearly'
            ? $this->start()->copy()->subYear()->startOfYear()
            : $this->start()->copy()->subMonthNoOverflow()->startOfMonth();
    }

    public function previousEnd(): Carbon
    {
        return $this->mode === 'yearly'
            ? $this->end()->copy()->subYear()->endOfYear()
            : $this->start()->copy()->subMonthNoOverflow()->endOfMonth();
    }

    /**
     * @return 'day'|'month'
     */
    public function granularity(): string
    {
        return $this->mode === 'yearly' ? 'month' : 'day';
    }

    /**
     * @return array{mode: 'monthly'|'yearly', month: int|null, year: int}
     */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'month' => $this->month,
            'year' => $this->year,
        ];
    }
}
