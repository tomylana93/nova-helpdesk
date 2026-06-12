<?php

declare(strict_types=1);

use App\Support\IndonesiaCalendar;
use App\Support\SlaCalculator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    // Clear cache to ensure HTTP calls are made during tests
    Cache::forget('id_holidays_2026');
});

test('it calculates SLA correctly within normal working hours', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // Monday 09:30 -> target 60 minutes -> Monday 10:30
    $start = Date::parse('2026-06-15 09:30:00'); // June 15, 2026 is Monday
    $due = $calculator->addWorkingMinutes($start, 60);

    expect($due->toDateTimeString())->toBe('2026-06-15 10:30:00');
});

test('it excludes lunch break 12:00 - 13:00', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // Monday 11:30 -> target 60 minutes -> Monday 13:30 (skipping 12:00 - 13:00)
    $start = Date::parse('2026-06-15 11:30:00');
    $due = $calculator->addWorkingMinutes($start, 60);

    expect($due->toDateTimeString())->toBe('2026-06-15 13:30:00');
});

test('it shifts submission during lunch break to 13:00', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // Monday 12:30 -> target 30 minutes -> Monday 13:30
    $start = Date::parse('2026-06-15 12:30:00');
    $due = $calculator->addWorkingMinutes($start, 30);

    expect($due->toDateTimeString())->toBe('2026-06-15 13:30:00');
});

test('it excludes weekends', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // Friday 17:30 -> target 60 minutes -> Friday 17:30-18:00 (30 mins) + Monday 09:00-09:30 (30 mins) -> Monday 09:30
    $start = Date::parse('2026-06-12 17:30:00'); // June 12, 2026 is Friday
    $due = $calculator->addWorkingMinutes($start, 60);

    expect($due->toDateTimeString())->toBe('2026-06-15 09:30:00');
});

test('it shifts weekend submission to Monday 09:00', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // Saturday 14:00 -> target 60 minutes -> Monday 10:00
    $start = Date::parse('2026-06-13 14:00:00'); // Saturday
    $due = $calculator->addWorkingMinutes($start, 60);

    expect($due->toDateTimeString())->toBe('2026-06-15 10:00:00');
});

test('it excludes Indonesian national holidays fetched from Nager.Date API', function (): void {
    // Fake the public holiday response
    Http::fake([
        'date.nager.at/*' => Http::response([
            ['date' => '2026-06-16', 'localName' => 'Test Holiday', 'name' => 'Test Holiday'],
        ], 200),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // Monday 17:30 -> target 60 minutes
    // Monday 17:30-18:00 (30 mins)
    // Tuesday 2026-06-16 is a holiday (skipped)
    // Wednesday 09:00-09:30 (30 mins) -> Wednesday 09:30
    $start = Date::parse('2026-06-15 17:30:00');
    $due = $calculator->addWorkingMinutes($start, 60);

    expect($due->toDateTimeString())->toBe('2026-06-17 09:30:00');
});

test('it falls back to hardcoded 2026 holidays if API fails', function (): void {
    // Force API failure
    Http::fake([
        'date.nager.at/*' => Http::response(null, 500),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // August 17 is Independence Day (listed in FALLBACK_2026)
    // August 17, 2026 is Monday
    // Friday 2026-08-14 17:30 -> target 60 minutes
    // Friday 17:30-18:00 (30 mins)
    // Sat/Sun skipped
    // Monday Aug 17 is Independence Day (skipped)
    // Tuesday Aug 18 09:00-09:30 (30 mins) -> Tuesday 09:30
    $start = Date::parse('2026-08-14 17:30:00');
    $due = $calculator->addWorkingMinutes($start, 60);

    expect($due->toDateTimeString())->toBe('2026-08-18 09:30:00');
});

test('it shifts after-hours submission to next working day 09:00', function (): void {
    Http::fake([
        'date.nager.at/*' => Http::response([], 200),
    ]);

    $calendar = new IndonesiaCalendar;
    $calculator = new SlaCalculator($calendar);

    // Monday 20:00 -> target 60 minutes -> Tuesday 10:00
    $start = Date::parse('2026-06-15 20:00:00');
    $due = $calculator->addWorkingMinutes($start, 60);

    expect($due->toDateTimeString())->toBe('2026-06-16 10:00:00');
});
