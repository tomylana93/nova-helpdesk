<?php

use App\Actions\Dashboard\Support\DashboardPeriod;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-06-11 10:00:00'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('monthly period spans the calendar month and previous month', function (): void {
    $period = DashboardPeriod::fromRequest('monthly', 6, 2026);

    expect($period->mode)->toBe('monthly')
        ->and($period->granularity())->toBe('day')
        ->and($period->start()->toDateTimeString())->toBe('2026-06-01 00:00:00')
        ->and($period->end()->toDateTimeString())->toBe('2026-06-30 23:59:59')
        ->and($period->previousStart()->toDateTimeString())->toBe('2026-05-01 00:00:00')
        ->and($period->previousEnd()->toDateTimeString())->toBe('2026-05-31 23:59:59');
});

test('monthly january rolls previous period into december of last year', function (): void {
    $period = DashboardPeriod::fromRequest('monthly', 1, 2026);

    expect($period->previousStart()->toDateTimeString())->toBe('2025-12-01 00:00:00')
        ->and($period->previousEnd()->toDateTimeString())->toBe('2025-12-31 23:59:59');
});

test('yearly period spans the year and previous year', function (): void {
    $period = DashboardPeriod::fromRequest('yearly', null, 2026);

    expect($period->mode)->toBe('yearly')
        ->and($period->granularity())->toBe('month')
        ->and($period->start()->toDateTimeString())->toBe('2026-01-01 00:00:00')
        ->and($period->end()->toDateTimeString())->toBe('2026-12-31 23:59:59')
        ->and($period->previousStart()->toDateTimeString())->toBe('2025-01-01 00:00:00')
        ->and($period->previousEnd()->toDateTimeString())->toBe('2025-12-31 23:59:59');
});

test('invalid month falls back to current month', function (): void {
    $period = DashboardPeriod::fromRequest('monthly', 99, 2026);

    expect($period->month)->toBe(6); // current month from travelTo
});

test('toArray exposes mode month and year', function (): void {
    expect(DashboardPeriod::fromRequest('monthly', 6, 2026)->toArray())
        ->toBe(['mode' => 'monthly', 'month' => 6, 'year' => 2026]);

    expect(DashboardPeriod::fromRequest('yearly', null, 2026)->toArray())
        ->toBe(['mode' => 'yearly', 'month' => null, 'year' => 2026]);
});
