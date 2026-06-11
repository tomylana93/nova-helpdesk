# Dashboard Refactor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the helpdesk dashboard around a time-period filter (monthly/yearly) with a "Live snapshot" zone plus a filtered "Period" zone (delta vs previous period, created-vs-resolved trend chart, period donut, SLA gauge), aligned to the app design system and fully internationalised, backed by a decomposed, driver-portable backend.

**Architecture:** Backend splits the single 281-line `GetDashboardMetrics` action into a thin orchestrator (`GetDashboardData`), three per-role builders, and a `Support/` layer (`DashboardPeriod` value object, driver-aware `TicketMetricQueries`, `Delta` calculator). The controller validates a `mode/month/year` query, builds a `DashboardPeriod`, and renders Inertia. Frontend replaces the monolithic `Dashboard.vue` with a thin page composing focused components (period control, metric cards, trend chart, breakdown donut, SLA gauge), using theme tokens and `useTrans`.

**Tech Stack:** Laravel 13 / PHP 8.5, Pest 4, Inertia v3 + Vue 3, `@unovis/vue` 1.6 charts, Tailwind v4 theme tokens, Wayfinder generated routes, `php artisan lang:export` JSON i18n.

---

## Data Contract (single source of truth — referenced by all tasks)

The orchestrator returns this exact shape to `Inertia::render('Dashboard', ...)`:

```php
[
    'role' => 'requester'|'it_agent'|'super_admin',
    'period' => [
        'mode' => 'monthly'|'yearly',
        'month' => int|null,   // 1-12 when monthly, null when yearly
        'year' => int,
    ],
    'live' => [                                   // snapshot cards, NOT affected by period
        ['key' => 'active', 'value' => 12],       // key ∈ active|assigned|unassigned|pending_approval|sla_breached
        // ...
    ],
    'periodMetrics' => [                          // delta cards, affected by period
        [
            'key' => 'created',                   // key ∈ created|resolved
            'value' => 40,
            'previous' => 33,
            'deltaPercent' => 21,                 // int, or null when previous=0 && value>0
            'direction' => 'up',                  // up|down|flat
            'sentiment' => 'neutral',             // higher_is_better|lower_is_better|neutral
        ],
        // ...
    ],
    'compliance' => [                             // SLA gauge; null for requester
        'rate' => 92,
        'resolvedWithinDue' => 46,
        'totalResolved' => 50,
        'previousRate' => 88,
        'deltaPercent' => 4,                      // int, or null
        'direction' => 'up',                      // up|down|flat
    ],                                            // or null
    'trend' => [
        'granularity' => 'day'|'month',
        'points' => [
            ['label' => '1', 'created' => 3, 'resolved' => 1],
            // ...
        ],
    ],
    'breakdown' => [
        'type' => 'priority'|'status',
        'segments' => [
            ['key' => 'low', 'value' => 5],       // enum value; frontend translates label
            // ...
        ],
    ],
]
```

**Per-role composition:**

| Role | `live` keys | `periodMetrics` | `compliance` | `breakdown.type` |
|------|-------------|-----------------|--------------|------------------|
| requester | `active` (own) | `created`, `resolved` | `null` | `priority` |
| it_agent | `assigned`, `unassigned`, `pending_approval`, `sla_breached` | `resolved` (by me) | gauge | `status` |
| super_admin | `active`, `unassigned`, `pending_approval`, `sla_breached` | `created`, `resolved` | gauge | `priority` |

**Sentiment rules:** `created` → `neutral`; `resolved` → `higher_is_better`.

---

## File Structure

**Backend — create:**
- `app/Actions/Dashboard/GetDashboardData.php` — orchestrator; picks builder, assembles payload.
- `app/Actions/Dashboard/Builders/RequesterDashboard.php`
- `app/Actions/Dashboard/Builders/AgentDashboard.php`
- `app/Actions/Dashboard/Builders/AdminDashboard.php`
- `app/Actions/Dashboard/Support/DashboardPeriod.php` — value object (range + previous range + granularity).
- `app/Actions/Dashboard/Support/TicketMetricQueries.php` — reusable, driver-aware queries.
- `app/Actions/Dashboard/Support/Delta.php` — delta percent + direction.
- `app/Http/Requests/DashboardRequest.php` — validates `mode/month/year`.

**Backend — modify:**
- `app/Http/Controllers/DashboardController.php` — inject `DashboardRequest` + `GetDashboardData`.
- `app/Enums/TicketStatus.php` — add `activeCases()` helper (DRY for "not resolved/closed").
- `lang/en/dashboard.php`, `lang/id/dashboard.php` — new namespace (create).

**Backend — delete (after frontend no longer needs it):**
- `app/Actions/Dashboard/GetDashboardMetrics.php` (replaced by `GetDashboardData`).

**Frontend — create:**
- `resources/js/pages/Dashboard.vue` — rewritten thin page (replace existing).
- `resources/js/components/dashboard/PeriodControl.vue`
- `resources/js/components/dashboard/MetricCard.vue`
- `resources/js/components/dashboard/DeltaBadge.vue`
- `resources/js/components/dashboard/TrendChart.vue`
- `resources/js/components/dashboard/BreakdownDonut.vue`
- `resources/js/components/dashboard/SlaGauge.vue`
- `resources/js/composables/useDashboard.ts` — shapes props into typed view models + label maps.

**Frontend — modify:**
- `resources/js/types/index.d.ts` (or wherever dashboard types live) — add `DashboardProps` types.

---

## PHASE 1 — Backend foundation (no output change yet)

### Task 1: `TicketStatus::activeCases()` helper

**Files:**
- Modify: `app/Enums/TicketStatus.php`
- Test: `tests/Unit/TicketStatusTest.php`

- [ ] **Step 1: Write the failing test**

Append to `tests/Unit/TicketStatusTest.php`:

```php
test('activeCases excludes resolved and closed', function (): void {
    $active = TicketStatus::activeCases();

    expect($active)->not->toContain(TicketStatus::Resolved)
        ->and($active)->not->toContain(TicketStatus::Closed)
        ->and($active)->toContain(TicketStatus::Open)
        ->and($active)->toContain(TicketStatus::InProgress);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter='activeCases excludes resolved'`
Expected: FAIL — `Call to undefined method App\Enums\TicketStatus::activeCases()`.

- [ ] **Step 3: Implement the helper**

Add to `app/Enums/TicketStatus.php` (after `allowedTransitions()`):

```php
    /**
     * Statuses considered "active" (not resolved or closed).
     *
     * @return list<self>
     */
    public static function activeCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $status): bool => ! in_array($status, [self::Resolved, self::Closed], true),
        ));
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter='activeCases excludes resolved'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/TicketStatus.php tests/Unit/TicketStatusTest.php
git commit -m "feat(dashboard): add TicketStatus::activeCases helper"
```

---

### Task 2: `DashboardPeriod` value object

**Files:**
- Create: `app/Actions/Dashboard/Support/DashboardPeriod.php`
- Test: `tests/Unit/Dashboard/DashboardPeriodTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Dashboard/DashboardPeriodTest.php`:

```php
<?php

use App\Actions\Dashboard\Support\DashboardPeriod;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-11 10:00:00'));
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=DashboardPeriod`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the value object**

Create `app/Actions/Dashboard/Support/DashboardPeriod.php`:

```php
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=DashboardPeriod`
Expected: PASS (5 tests).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Dashboard/Support/DashboardPeriod.php tests/Unit/Dashboard/DashboardPeriodTest.php
git commit -m "feat(dashboard): add DashboardPeriod value object"
```

---

### Task 3: `Delta` calculator

**Files:**
- Create: `app/Actions/Dashboard/Support/Delta.php`
- Test: `tests/Unit/Dashboard/DeltaTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Dashboard/DeltaTest.php`:

```php
<?php

use App\Actions\Dashboard\Support\Delta;

test('computes positive delta', function (): void {
    expect(Delta::compute(40, 33))->toBe([
        'deltaPercent' => 21,
        'direction' => 'up',
    ]);
});

test('computes negative delta', function (): void {
    expect(Delta::compute(8, 10))->toBe([
        'deltaPercent' => -20,
        'direction' => 'down',
    ]);
});

test('flat when equal', function (): void {
    expect(Delta::compute(10, 10))->toBe([
        'deltaPercent' => 0,
        'direction' => 'flat',
    ]);
});

test('both zero is flat zero', function (): void {
    expect(Delta::compute(0, 0))->toBe([
        'deltaPercent' => 0,
        'direction' => 'flat',
    ]);
});

test('growth from zero baseline yields null percent and up direction', function (): void {
    expect(Delta::compute(5, 0))->toBe([
        'deltaPercent' => null,
        'direction' => 'up',
    ]);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=Delta`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the calculator**

Create `app/Actions/Dashboard/Support/Delta.php`:

```php
<?php

namespace App\Actions\Dashboard\Support;

class Delta
{
    /**
     * @return array{deltaPercent: int|null, direction: 'up'|'down'|'flat'}
     */
    public static function compute(int $current, int $previous): array
    {
        if ($current === $previous) {
            return ['deltaPercent' => 0, 'direction' => 'flat'];
        }

        $direction = $current > $previous ? 'up' : 'down';

        if ($previous === 0) {
            return ['deltaPercent' => null, 'direction' => $direction];
        }

        $percent = (int) round((($current - $previous) / $previous) * 100);

        return ['deltaPercent' => $percent, 'direction' => $direction];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=Delta`
Expected: PASS (5 tests).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Dashboard/Support/Delta.php tests/Unit/Dashboard/DeltaTest.php
git commit -m "feat(dashboard): add Delta calculator"
```

---

### Task 4: `TicketMetricQueries` (driver-aware aggregation)

**Files:**
- Create: `app/Actions/Dashboard/Support/TicketMetricQueries.php`
- Test: `tests/Feature/Dashboard/TicketMetricQueriesTest.php`

> This is a feature test (touches the DB) so it runs on SQLite locally and proves the date-grouping path. The `dateGroupExpression()` switch covers sqlite/mysql/pgsql so production (MySQL/Postgres) stays correct.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/TicketMetricQueriesTest.php`:

```php
<?php

use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Actions\Dashboard\Support\TicketMetricQueries;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->queries = app(TicketMetricQueries::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('counts tickets created within the period', function (): void {
    Ticket::factory()->count(3)->create(['created_at' => Date::parse('2026-06-10 09:00:00')]);
    Ticket::factory()->create(['created_at' => Date::parse('2026-05-10 09:00:00')]); // out of period

    $count = $this->queries->countCreated(Ticket::query(), $this->period->start(), $this->period->end());

    expect($count)->toBe(3);
});

test('counts tickets resolved within the period', function (): void {
    Ticket::factory()->count(2)->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
    ]);
    Ticket::factory()->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-05-12 09:00:00'),
    ]);

    $count = $this->queries->countResolved(Ticket::query(), $this->period->start(), $this->period->end());

    expect($count)->toBe(2);
});

test('computes compliance breakdown within the period', function (): void {
    // 3 resolved in period: 2 within due, 1 breached
    Ticket::factory()->count(2)->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-13 09:00:00'),
    ]);
    Ticket::factory()->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-11 09:00:00'),
    ]);

    $result = $this->queries->compliance(Ticket::query(), $this->period->start(), $this->period->end());

    expect($result)->toBe([
        'resolvedWithinDue' => 2,
        'totalResolved' => 3,
        'rate' => 67,
    ]);
});

test('daily trend buckets created and resolved by day', function (): void {
    Ticket::factory()->create(['created_at' => Date::parse('2026-06-01 09:00:00')]);
    Ticket::factory()->create(['created_at' => Date::parse('2026-06-01 14:00:00')]);
    Ticket::factory()->create([
        'created_at' => Date::parse('2026-05-20 09:00:00'),
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-02 09:00:00'),
    ]);

    $points = $this->queries->trend(Ticket::query(), Ticket::query(), $this->period);

    expect($points)->toHaveCount(30) // June has 30 days
        ->and($points[0])->toBe(['label' => '01', 'created' => 2, 'resolved' => 0])
        ->and($points[1])->toBe(['label' => '02', 'created' => 0, 'resolved' => 1]);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=TicketMetricQueries`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the query helper**

Create `app/Actions/Dashboard/Support/TicketMetricQueries.php`:

```php
<?php

namespace App\Actions\Dashboard\Support;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TicketMetricQueries
{
    /**
     * @param  Builder<\App\Models\Ticket>  $base
     */
    public function countCreated(Builder $base, Carbon $start, Carbon $end): int
    {
        return $base->clone()->whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * @param  Builder<\App\Models\Ticket>  $base
     */
    public function countResolved(Builder $base, Carbon $start, Carbon $end): int
    {
        return $base->clone()
            ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
            ->whereBetween('resolved_at', [$start, $end])
            ->count();
    }

    /**
     * @param  Builder<\App\Models\Ticket>  $base
     * @return array{resolvedWithinDue: int, totalResolved: int, rate: int}
     */
    public function compliance(Builder $base, Carbon $start, Carbon $end): array
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
     * @param  Builder<\App\Models\Ticket>  $createdBase
     * @param  Builder<\App\Models\Ticket>  $resolvedBase
     * @return list<array{label: string, created: int, resolved: int}>
     */
    public function trend(Builder $createdBase, Builder $resolvedBase, DashboardPeriod $period): array
    {
        $granularity = $period->granularity();
        $start = $period->start();
        $end = $period->end();

        $created = $this->bucketCounts($createdBase->clone()->whereBetween('created_at', [$start, $end]), 'created_at', $granularity);

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
     * @param  Builder<\App\Models\Ticket>  $query
     * @return array<string, int>  keyed by bucket label (day-of-month "01".."31" or month "01".."12")
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
        $unit = $granularity === 'month' ? 'm' : 'd';

        return match ($driver) {
            'sqlite' => "strftime('%{$unit}', {$column})",
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%".($unit === 'm' ? 'm' : 'd')."')",
            'pgsql' => "to_char({$column}, '".($unit === 'm' ? 'MM' : 'DD')."')",
            default => "strftime('%{$unit}', {$column})",
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=TicketMetricQueries`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Dashboard/Support/TicketMetricQueries.php tests/Feature/Dashboard/TicketMetricQueriesTest.php
git commit -m "feat(dashboard): add driver-aware TicketMetricQueries"
```

---

## PHASE 2 — Builders, orchestrator, controller (payload changes)

### Task 5: `RequesterDashboard` builder

**Files:**
- Create: `app/Actions/Dashboard/Builders/RequesterDashboard.php`
- Test: `tests/Feature/Dashboard/RequesterDashboardTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/RequesterDashboardTest.php`:

```php
<?php

use App\Actions\Dashboard\Builders\RequesterDashboard;
use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->builder = app(RequesterDashboard::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('builds requester payload scoped to own tickets', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    // own: 2 active, 1 resolved this month
    Ticket::factory()->create(['requester_id' => $user->id, 'status' => TicketStatus::Open]);
    Ticket::factory()->create(['requester_id' => $user->id, 'status' => TicketStatus::InProgress]);
    Ticket::factory()->create([
        'requester_id' => $user->id,
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'priority' => \App\Enums\TicketPriority::High,
    ]);
    // someone else's ticket must be excluded
    Ticket::factory()->create(['requester_id' => $other->id, 'status' => TicketStatus::Open]);

    $payload = $this->builder->handle($user, $this->period);

    expect($payload['live'])->toBe([['key' => 'active', 'value' => 2]])
        ->and($payload['compliance'])->toBeNull()
        ->and($payload['breakdown']['type'])->toBe('priority')
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'created')['value'])->toBe(3)
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'resolved')['value'])->toBe(1)
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'created')['sentiment'])->toBe('neutral')
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'resolved')['sentiment'])->toBe('higher_is_better');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=RequesterDashboard`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the builder**

Create `app/Actions/Dashboard/Builders/RequesterDashboard.php`:

```php
<?php

namespace App\Actions\Dashboard\Builders;

use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Actions\Dashboard\Support\Delta;
use App\Actions\Dashboard\Support\TicketMetricQueries;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

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
        $scope = fn (): \Illuminate\Database\Eloquent\Builder => Ticket::query()->where('requester_id', $user->id);

        $activeCount = $scope()->whereIn('status', TicketStatus::activeCases())->count();

        $created = $this->queries->countCreated($scope(), $period->start(), $period->end());
        $createdPrev = $this->queries->countCreated($scope(), $period->previousStart(), $period->previousEnd());
        $resolved = $this->queries->countResolved($scope(), $period->start(), $period->end());
        $resolvedPrev = $this->queries->countResolved($scope(), $period->previousStart(), $period->previousEnd());

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
     * @param  \Illuminate\Database\Eloquent\Builder<Ticket>  $scope
     * @return array{type: string, segments: list<array{key: string, value: int}>}
     */
    private function priorityBreakdown(\Illuminate\Database\Eloquent\Builder $scope): array
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=RequesterDashboard`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Dashboard/Builders/RequesterDashboard.php tests/Feature/Dashboard/RequesterDashboardTest.php
git commit -m "feat(dashboard): add RequesterDashboard builder"
```

---

### Task 6: `AgentDashboard` builder

**Files:**
- Create: `app/Actions/Dashboard/Builders/AgentDashboard.php`
- Test: `tests/Feature/Dashboard/AgentDashboardTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/AgentDashboardTest.php`:

```php
<?php

use App\Actions\Dashboard\Builders\AgentDashboard;
use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));
    $this->builder = app(AgentDashboard::class);
    $this->period = DashboardPeriod::fromRequest('monthly', 6, 2026);
});

test('builds agent payload with live snapshot and compliance gauge', function (): void {
    $agent = User::factory()->create();

    Ticket::factory()->create(['assigned_to' => $agent->id, 'status' => TicketStatus::InProgress]); // assigned active
    Ticket::factory()->create(['assigned_to' => null, 'status' => TicketStatus::Open]); // unassigned active
    Ticket::factory()->create(['status' => TicketStatus::PendingApproval]); // pending approval
    Ticket::factory()->create([
        'status' => TicketStatus::Open,
        'resolution_due_at' => Date::parse('2026-06-10 09:00:00'), // overdue -> breached
    ]);

    // resolved-by-me in period: 1 within due
    Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'status' => TicketStatus::Resolved,
        'resolved_at' => Date::parse('2026-06-12 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-13 09:00:00'),
    ]);

    $payload = $this->builder->handle($agent, $this->period);

    $live = collect($payload['live'])->keyBy('key');
    expect($live['assigned']['value'])->toBe(1)
        ->and($live['unassigned']['value'])->toBe(1)
        ->and($live['pending_approval']['value'])->toBe(1)
        ->and($live['sla_breached']['value'])->toBe(1)
        ->and($payload['compliance']['rate'])->toBe(100)
        ->and($payload['compliance']['totalResolved'])->toBe(1)
        ->and($payload['breakdown']['type'])->toBe('status')
        ->and(collect($payload['periodMetrics'])->firstWhere('key', 'resolved')['value'])->toBe(1);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=AgentDashboard`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the builder**

Create `app/Actions/Dashboard/Builders/AgentDashboard.php`:

```php
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=AgentDashboard`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Dashboard/Builders/AgentDashboard.php tests/Feature/Dashboard/AgentDashboardTest.php
git commit -m "feat(dashboard): add AgentDashboard builder"
```

---

### Task 7: `AdminDashboard` builder

**Files:**
- Create: `app/Actions/Dashboard/Builders/AdminDashboard.php`
- Test: `tests/Feature/Dashboard/AdminDashboardTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/AdminDashboardTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=AdminDashboard`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the builder**

Create `app/Actions/Dashboard/Builders/AdminDashboard.php`:

```php
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=AdminDashboard`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Dashboard/Builders/AdminDashboard.php tests/Feature/Dashboard/AdminDashboardTest.php
git commit -m "feat(dashboard): add AdminDashboard builder"
```

---

### Task 8: `GetDashboardData` orchestrator

**Files:**
- Create: `app/Actions/Dashboard/GetDashboardData.php`
- Test: `tests/Feature/Dashboard/GetDashboardDataTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/GetDashboardDataTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=GetDashboardData`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the orchestrator**

Create `app/Actions/Dashboard/GetDashboardData.php`:

```php
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
            return ['role' => 'super_admin', 'period' => $period->toArray(), ...$this->admin->handle($period)];
        }

        return ['role' => 'requester', 'period' => $period->toArray(), ...$this->requester->handle($user, $period)];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=GetDashboardData`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Dashboard/GetDashboardData.php tests/Feature/Dashboard/GetDashboardDataTest.php
git commit -m "feat(dashboard): add GetDashboardData orchestrator"
```

---

### Task 9: `DashboardRequest` + wire controller

**Files:**
- Create: `app/Http/Requests/DashboardRequest.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Test: `tests/Feature/DashboardTest.php` (replace stale role/recentTickets assertions)

- [ ] **Step 1: Write the failing test**

Replace the `dashboard returns correct inertia props based on user role` and `dashboard recent tickets include sla payload` tests in `tests/Feature/DashboardTest.php` with:

```php
test('dashboard returns role-shaped props with period defaults', function (): void {
    $this->travelTo(\Illuminate\Support\Facades\Date::parse('2026-06-15 10:00:00'));

    $this->actingAs(createRequesterUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('role', 'requester')
            ->where('period.mode', 'monthly')
            ->where('period.month', 6)
            ->where('period.year', 2026)
            ->has('live')
            ->has('periodMetrics')
            ->has('trend.points')
            ->has('breakdown.segments')
            ->where('compliance', null)
        );
});

test('dashboard accepts yearly period from query', function (): void {
    $this->actingAs(createAgentUser())
        ->get(route('dashboard', ['mode' => 'yearly', 'year' => 2025]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('period.mode', 'yearly')
            ->where('period.month', null)
            ->where('period.year', 2025)
            ->where('trend.granularity', 'month')
            ->has('trend.points', 12)
        );
});

test('dashboard clamps invalid period query to safe defaults', function (): void {
    $this->travelTo(\Illuminate\Support\Facades\Date::parse('2026-06-15 10:00:00'));

    $this->actingAs(createRequesterUser())
        ->get(route('dashboard', ['mode' => 'weekly', 'month' => 99, 'year' => 1800]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('period.mode', 'monthly')
            ->where('period.month', 6)
            ->where('period.year', 2026)
        );
});
```

> Note: also update the `dashboard shares auth abilities...` and locale tests if they referenced `recentTickets` — they do not, so leave them.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=DashboardTest`
Expected: FAIL — props `period`, `live` missing (old controller still renders `metrics/recentTickets/charts`).

- [ ] **Step 3: Create the FormRequest**

Create `app/Http/Requests/DashboardRequest.php`:

```php
<?php

namespace App\Http\Requests;

use App\Actions\Dashboard\Support\DashboardPeriod;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * No strict rules: the dashboard must never 400 on a bad period query.
     * DashboardPeriod clamps invalid values to safe defaults instead.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function toPeriod(): DashboardPeriod
    {
        return DashboardPeriod::fromRequest(
            $this->query('mode'),
            $this->integerOrNull($this->query('month')),
            $this->integerOrNull($this->query('year')),
        );
    }

    private function integerOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
```

- [ ] **Step 4: Rewrite the controller**

Replace the body of `app/Http/Controllers/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardData;
use App\Http\Requests\DashboardRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(DashboardRequest $request, GetDashboardData $getDashboardData): Response
    {
        $user = $request->user();
        abort_if($user === null, 401);

        return Inertia::render('Dashboard', $getDashboardData->handle($user, $request->toPeriod()));
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --compact --filter=DashboardTest`
Expected: PASS (all dashboard feature tests).

- [ ] **Step 6: Delete the obsolete action**

```bash
git rm app/Actions/Dashboard/GetDashboardMetrics.php
```

Run: `rg -n "GetDashboardMetrics" app tests` → Expected: no matches.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat(dashboard): validate period query and render new payload"
```

---

### Task 10: Dashboard i18n lang keys

**Files:**
- Create: `lang/en/dashboard.php`, `lang/id/dashboard.php`
- Run: `php artisan lang:export`
- Test: `tests/Feature/Dashboard/DashboardLangTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/DashboardLangTest.php`:

```php
<?php

test('dashboard translations exist for both locales', function (): void {
    expect(__('dashboard.metric.created', [], 'en'))->toBe('Created')
        ->and(__('dashboard.metric.created', [], 'id'))->toBe('Dibuat')
        ->and(__('dashboard.live.unassigned', [], 'en'))->toBe('Unassigned')
        ->and(__('dashboard.compliance.tooltip', ['within' => 46, 'total' => 50], 'en'))
        ->toContain('46')
        ->toContain('50');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=DashboardLangTest`
Expected: FAIL — keys return the key string.

- [ ] **Step 3: Create `lang/en/dashboard.php`**

```php
<?php

return [
    'greeting' => 'Hello, :name',
    'subtitle' => 'Your helpdesk overview for :period.',
    'period' => [
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'month' => 'Month',
        'year' => 'Year',
        'vs_previous' => 'vs :period',
    ],
    'live' => [
        'heading' => 'Right now',
        'active' => 'Active Tickets',
        'assigned' => 'Assigned to you',
        'unassigned' => 'Unassigned',
        'pending_approval' => 'Pending Approvals',
        'sla_breached' => 'SLA Breached',
    ],
    'metric' => [
        'heading' => 'In :period',
        'created' => 'Created',
        'resolved' => 'Resolved',
        'new' => 'New',
    ],
    'compliance' => [
        'title' => 'SLA Compliance Rate',
        'caption' => 'Resolved in SLA',
        'tooltip' => ':within of :total tickets resolved on time (before their resolution due date).',
    ],
    'trend' => [
        'title' => 'Created vs Resolved',
        'created' => 'Created',
        'resolved' => 'Resolved',
        'empty' => 'No activity in this period.',
    ],
    'breakdown' => [
        'priority_title' => 'Ticket Priority Distribution',
        'status_title' => 'Active Status Distribution',
        'tickets' => 'Tickets',
        'empty' => 'No distribution data available.',
    ],
    'priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ],
    'status' => [
        'open' => 'Open',
        'pending_approval' => 'Pending Approval',
        'in_progress' => 'In Progress',
        'waiting_for_requester' => 'Waiting for Requester',
        'reopened' => 'Reopened',
    ],
];
```

- [ ] **Step 4: Create `lang/id/dashboard.php`**

```php
<?php

return [
    'greeting' => 'Halo, :name',
    'subtitle' => 'Ringkasan helpdesk Anda untuk :period.',
    'period' => [
        'monthly' => 'Bulanan',
        'yearly' => 'Tahunan',
        'month' => 'Bulan',
        'year' => 'Tahun',
        'vs_previous' => 'vs :period',
    ],
    'live' => [
        'heading' => 'Saat ini',
        'active' => 'Tiket Aktif',
        'assigned' => 'Ditugaskan ke Anda',
        'unassigned' => 'Belum Ditugaskan',
        'pending_approval' => 'Menunggu Persetujuan',
        'sla_breached' => 'SLA Terlewati',
    ],
    'metric' => [
        'heading' => 'Pada :period',
        'created' => 'Dibuat',
        'resolved' => 'Selesai',
        'new' => 'Baru',
    ],
    'compliance' => [
        'title' => 'Tingkat Kepatuhan SLA',
        'caption' => 'Selesai sesuai SLA',
        'tooltip' => ':within dari :total tiket selesai tepat waktu (sebelum batas waktu penyelesaian).',
    ],
    'trend' => [
        'title' => 'Dibuat vs Selesai',
        'created' => 'Dibuat',
        'resolved' => 'Selesai',
        'empty' => 'Tidak ada aktivitas pada periode ini.',
    ],
    'breakdown' => [
        'priority_title' => 'Distribusi Prioritas Tiket',
        'status_title' => 'Distribusi Status Aktif',
        'tickets' => 'Tiket',
        'empty' => 'Tidak ada data distribusi.',
    ],
    'priority' => [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'critical' => 'Kritis',
    ],
    'status' => [
        'open' => 'Terbuka',
        'pending_approval' => 'Menunggu Persetujuan',
        'in_progress' => 'Sedang Diproses',
        'waiting_for_requester' => 'Menunggu Pelapor',
        'reopened' => 'Dibuka Kembali',
    ],
];
```

- [ ] **Step 5: Export to JSON + run test**

```bash
php artisan lang:export
php artisan test --compact --filter=DashboardLangTest
```
Expected: lang JSON regenerated; test PASS.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add lang/
git commit -m "feat(dashboard): add dashboard i18n keys (en/id)"
```

---

## PHASE 3 — Frontend rebuild

> After Phase 2, `Dashboard.vue` still references the old `metrics/recentTickets/charts` props and will be broken at runtime/typecheck. Phase 3 replaces it. Work top-down: types → composable → leaf components → page.

### Task 11: Dashboard TypeScript types

**Files:**
- Modify: `resources/js/types/index.d.ts`
- Verify: `pnpm run types:check`

- [ ] **Step 1: Add the types**

Append to `resources/js/types/index.d.ts`:

```typescript
export type DashboardRole = 'requester' | 'it_agent' | 'super_admin';

export interface DashboardPeriodProp {
    mode: 'monthly' | 'yearly';
    month: number | null;
    year: number;
}

export interface DashboardLiveMetric {
    key: 'active' | 'assigned' | 'unassigned' | 'pending_approval' | 'sla_breached';
    value: number;
}

export interface DashboardPeriodMetric {
    key: 'created' | 'resolved';
    value: number;
    previous: number;
    deltaPercent: number | null;
    direction: 'up' | 'down' | 'flat';
    sentiment: 'higher_is_better' | 'lower_is_better' | 'neutral';
}

export interface DashboardCompliance {
    rate: number;
    resolvedWithinDue: number;
    totalResolved: number;
    previousRate: number;
    deltaPercent: number | null;
    direction: 'up' | 'down' | 'flat';
}

export interface DashboardTrendPoint {
    label: string;
    created: number;
    resolved: number;
}

export interface DashboardBreakdownSegment {
    key: string;
    value: number;
}

export interface DashboardProps {
    role: DashboardRole;
    period: DashboardPeriodProp;
    live: DashboardLiveMetric[];
    periodMetrics: DashboardPeriodMetric[];
    compliance: DashboardCompliance | null;
    trend: {
        granularity: 'day' | 'month';
        points: DashboardTrendPoint[];
    };
    breakdown: {
        type: 'priority' | 'status';
        segments: DashboardBreakdownSegment[];
    };
}
```

- [ ] **Step 2: Verify typecheck still passes**

Run: `pnpm run types:check`
Expected: PASS (no new errors from the type additions; `Dashboard.vue` errors are expected and fixed in Task 17).

- [ ] **Step 3: Commit**

```bash
git add resources/js/types/index.d.ts
git commit -m "feat(dashboard): add dashboard prop types"
```

---

### Task 12: `useDashboard` composable (label maps + period helpers)

**Files:**
- Create: `resources/js/composables/useDashboard.ts`

- [ ] **Step 1: Create the composable**

Create `resources/js/composables/useDashboard.ts`:

```typescript
import { computed } from 'vue';

import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import type { DashboardPeriodProp } from '@/types';

export function useDashboard() {
    const { trans, locale } = useTrans();

    /** Human label for the active period, e.g. "June 2026" or "2026". */
    function periodLabel(period: DashboardPeriodProp): string {
        if (period.mode === 'yearly') {
            return String(period.year);
        }

        const date = new Date(period.year, (period.month ?? 1) - 1, 1);

        return date.toLocaleDateString(locale.value, {
            month: 'long',
            year: 'numeric',
        });
    }

    function previousPeriodLabel(period: DashboardPeriodProp): string {
        if (period.mode === 'yearly') {
            return String(period.year - 1);
        }

        const prev = new Date(period.year, (period.month ?? 1) - 2, 1);

        return prev.toLocaleDateString(locale.value, {
            month: 'long',
            year: 'numeric',
        });
    }

    /** Wayfinder URL preserving the period query for instant Inertia visits. */
    function periodUrl(period: DashboardPeriodProp): string {
        const query: Record<string, string> =
            period.mode === 'yearly'
                ? { mode: 'yearly', year: String(period.year) }
                : {
                      mode: 'monthly',
                      month: String(period.month ?? 1),
                      year: String(period.year),
                  };

        return dashboard({ query }).url;
    }

    /** Trend x-axis tick formatter: short month name (yearly) or day number (monthly). */
    function trendTick(granularity: 'day' | 'month', label: string): string {
        if (granularity === 'month') {
            const date = new Date(2000, Number(label) - 1, 1);

            return date.toLocaleDateString(locale.value, { month: 'short' });
        }

        return String(Number(label));
    }

    return { trans, locale, periodLabel, previousPeriodLabel, periodUrl, trendTick };
}

/** Years available in the year selector: current year back to 2023. */
export function availableYears(currentYear: number): number[] {
    const years: number[] = [];
    for (let y = currentYear; y >= 2023; y--) {
        years.push(y);
    }

    return years;
}
```

- [ ] **Step 2: Verify typecheck**

Run: `pnpm run types:check`
Expected: no new errors from this file (Dashboard.vue errors still expected until Task 17).

- [ ] **Step 3: Commit**

```bash
git add resources/js/composables/useDashboard.ts
git commit -m "feat(dashboard): add useDashboard composable"
```

---

### Task 13: `DeltaBadge` component

**Files:**
- Create: `resources/js/components/dashboard/DeltaBadge.vue`

- [ ] **Step 1: Create the component**

Create `resources/js/components/dashboard/DeltaBadge.vue`:

```vue
<script setup lang="ts">
import { ArrowDown, ArrowUp, Minus } from 'lucide-vue-next';
import { computed } from 'vue';

import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';

const props = defineProps<{
    deltaPercent: number | null;
    direction: 'up' | 'down' | 'flat';
    sentiment: 'higher_is_better' | 'lower_is_better' | 'neutral';
}>();

const { trans } = useTrans();

const icon = computed(() => {
    if (props.direction === 'up') {
        return ArrowUp;
    }
    if (props.direction === 'down') {
        return ArrowDown;
    }

    return Minus;
});

// Colour only when the metric has a sentiment; neutral stays muted.
const toneClass = computed(() => {
    if (props.sentiment === 'neutral' || props.direction === 'flat') {
        return 'text-muted-foreground';
    }

    const isGood =
        props.sentiment === 'higher_is_better'
            ? props.direction === 'up'
            : props.direction === 'down';

    return isGood
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-destructive';
});

const label = computed(() => {
    if (props.deltaPercent === null) {
        return trans('dashboard.metric.new');
    }

    return `${Math.abs(props.deltaPercent)}%`;
});
</script>

<template>
    <span
        :class="cn('inline-flex items-center gap-0.5 text-xs font-medium', toneClass)"
    >
        <component :is="icon" class="h-3 w-3" />
        {{ label }}
    </span>
</template>
```

> If `@/lib/utils` is not the `cn` location, use the project's existing import (check an existing component such as `Badge`). The brainstorm confirmed the Prettier config registers `cn`.

- [ ] **Step 2: Verify typecheck**

Run: `pnpm run types:check`
Expected: no new errors from this file.

- [ ] **Step 3: Commit**

```bash
git add resources/js/components/dashboard/DeltaBadge.vue
git commit -m "feat(dashboard): add DeltaBadge component"
```

---

### Task 14: `MetricCard` + `SlaGauge` components

**Files:**
- Create: `resources/js/components/dashboard/MetricCard.vue`
- Create: `resources/js/components/dashboard/SlaGauge.vue`

- [ ] **Step 1: Create `MetricCard.vue`**

```vue
<script setup lang="ts">
import type { Component } from 'vue';

import DeltaBadge from '@/components/dashboard/DeltaBadge.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    label: string;
    value: number | string;
    icon: Component;
    // Optional delta block (period cards only)
    deltaPercent?: number | null;
    direction?: 'up' | 'down' | 'flat';
    sentiment?: 'higher_is_better' | 'lower_is_better' | 'neutral';
    caption?: string;
}>();
</script>

<template>
    <Card class="border-border/60">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
                {{ label }}
            </CardTitle>
            <div class="rounded-lg bg-muted p-1.5">
                <component :is="icon" class="h-4 w-4 text-muted-foreground" />
            </div>
        </CardHeader>
        <CardContent>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight">{{ value }}</span>
                <DeltaBadge
                    v-if="direction"
                    :delta-percent="deltaPercent ?? null"
                    :direction="direction"
                    :sentiment="sentiment ?? 'neutral'"
                />
            </div>
            <p v-if="caption" class="mt-1 text-xs text-muted-foreground">
                {{ caption }}
            </p>
        </CardContent>
    </Card>
</template>
```

- [ ] **Step 2: Create `SlaGauge.vue`** (token-aware `@unovis` donut gauge + tooltip)

```vue
<script setup lang="ts">
import { VisSingleContainer, VisDonut } from '@unovis/vue';
import { ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';

import DeltaBadge from '@/components/dashboard/DeltaBadge.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useTrans } from '@/composables/useTrans';
import type { DashboardCompliance } from '@/types';

const props = defineProps<{
    compliance: DashboardCompliance;
    previousLabel: string;
}>();

const { trans } = useTrans();

// Two-segment donut: compliant (chart-2 token) vs remainder (muted).
const data = computed(() => [props.compliance.rate, 100 - props.compliance.rate]);

const tooltipText = computed(() =>
    trans('dashboard.compliance.tooltip', {
        within: props.compliance.resolvedWithinDue,
        total: props.compliance.totalResolved,
    }),
);
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <Card class="flex flex-col items-center border-border/60 p-6 text-center">
                    <CardHeader class="pb-2">
                        <CardTitle
                            class="flex items-center gap-1.5 text-sm font-medium text-muted-foreground"
                        >
                            <ShieldCheck class="h-4 w-4 text-[var(--chart-2)]" />
                            {{ trans('dashboard.compliance.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="relative flex items-center justify-center py-2">
                        <div class="h-32 w-32">
                            <VisSingleContainer :data="data">
                                <VisDonut
                                    :value="(d: number) => d"
                                    :arc-width="12"
                                    :pad-angle="0.02"
                                    :color="(_: number, i: number) => (i === 0 ? 'var(--chart-2)' : 'var(--muted)')"
                                />
                            </VisSingleContainer>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-extrabold tracking-tight">
                                    {{ compliance.rate }}%
                                </span>
                                <DeltaBadge
                                    :delta-percent="compliance.deltaPercent"
                                    :direction="compliance.direction"
                                    sentiment="higher_is_better"
                                />
                            </div>
                        </div>
                    </CardContent>
                    <CardDescription class="text-xs">
                        {{ trans('dashboard.compliance.caption') }}
                    </CardDescription>
                </Card>
            </TooltipTrigger>
            <TooltipContent side="top" class="max-w-xs">
                <p class="text-xs">{{ tooltipText }}</p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ trans('dashboard.period.vs_previous', { period: previousLabel }) }}
                </p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
```

> Verify the `VisDonut` color signature against `Dashboard.vue`'s existing donut usage (Task removed it but the import pattern is known to work). If `@unovis/vue` requires a different color callback shape, mirror the working pattern from the old file's git history.

- [ ] **Step 3: Verify typecheck**

Run: `pnpm run types:check`
Expected: no new errors from these files.

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/dashboard/MetricCard.vue resources/js/components/dashboard/SlaGauge.vue
git commit -m "feat(dashboard): add MetricCard and SlaGauge components"
```

---

### Task 15: `BreakdownDonut` component

**Files:**
- Create: `resources/js/components/dashboard/BreakdownDonut.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup lang="ts">
import { Donut } from '@unovis/ts';
import { VisSingleContainer, VisDonut } from '@unovis/vue';
import { CircleHelp } from 'lucide-vue-next';
import { computed } from 'vue';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTrans } from '@/composables/useTrans';
import type { DashboardBreakdownSegment } from '@/types';

const props = defineProps<{
    type: 'priority' | 'status';
    segments: DashboardBreakdownSegment[];
}>();

const { trans } = useTrans();

const title = computed(() =>
    props.type === 'status'
        ? trans('dashboard.breakdown.status_title')
        : trans('dashboard.breakdown.priority_title'),
);

function segmentLabel(key: string): string {
    return trans(`dashboard.${props.type}.${key}`);
}

const total = computed(() => props.segments.reduce((sum, s) => sum + s.value, 0));

const data = computed(() => props.segments.map((s) => s.value));

function colorAt(index: number): string {
    return `var(--chart-${(index % 5) + 1})`;
}
</script>

<template>
    <Card class="flex flex-col border-border/60">
        <CardHeader class="pb-2">
            <CardTitle class="text-sm font-semibold text-muted-foreground">
                {{ title }}
            </CardTitle>
            <CardDescription>
                {{ trans('dashboard.breakdown.tickets') }}: {{ total }}
            </CardDescription>
        </CardHeader>
        <CardContent class="flex min-h-[220px] flex-1 items-center justify-center">
            <div v-if="total > 0" class="flex w-full flex-col items-center gap-4">
                <div class="aspect-square w-full max-w-[180px]">
                    <VisSingleContainer :data="data">
                        <VisDonut
                            :value="(d: number) => d"
                            :arc-width="20"
                            :color="(_: number, i: number) => colorAt(i)"
                            :central-label="String(total)"
                            :central-sub-label="trans('dashboard.breakdown.tickets')"
                        />
                    </VisSingleContainer>
                </div>
                <ul class="grid w-full grid-cols-2 gap-1 text-xs">
                    <li
                        v-for="(segment, index) in segments"
                        :key="segment.key"
                        class="flex items-center gap-1.5"
                    >
                        <span
                            class="h-2 w-2 shrink-0 rounded-full"
                            :style="{ backgroundColor: colorAt(index) }"
                        />
                        <span class="truncate text-muted-foreground">
                            {{ segmentLabel(segment.key) }}
                        </span>
                        <span class="ml-auto font-medium">{{ segment.value }}</span>
                    </li>
                </ul>
            </div>
            <div
                v-else
                class="flex flex-col items-center gap-2 py-10 text-center text-sm text-muted-foreground"
            >
                <CircleHelp class="h-8 w-8 text-muted-foreground/50" />
                <span>{{ trans('dashboard.breakdown.empty') }}</span>
            </div>
        </CardContent>
    </Card>
</template>
```

- [ ] **Step 2: Verify typecheck**

Run: `pnpm run types:check`
Expected: no new errors from this file.

- [ ] **Step 3: Commit**

```bash
git add resources/js/components/dashboard/BreakdownDonut.vue
git commit -m "feat(dashboard): add BreakdownDonut component"
```

---

### Task 16: `TrendChart` + `PeriodControl` components

**Files:**
- Create: `resources/js/components/dashboard/TrendChart.vue`
- Create: `resources/js/components/dashboard/PeriodControl.vue`

- [ ] **Step 1: Create `TrendChart.vue`** (created-vs-resolved lines, token colours)

```vue
<script setup lang="ts">
import { VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useDashboard } from '@/composables/useDashboard';
import { useTrans } from '@/composables/useTrans';
import type { DashboardTrendPoint } from '@/types';

const props = defineProps<{
    granularity: 'day' | 'month';
    points: DashboardTrendPoint[];
}>();

const { trans } = useTrans();
const { trendTick } = useDashboard();

const hasData = computed(() =>
    props.points.some((p) => p.created > 0 || p.resolved > 0),
);

// x = index into the dense series; y accessors read each metric.
const x = (_: DashboardTrendPoint, i: number) => i;
const createdY = (d: DashboardTrendPoint) => d.created;
const resolvedY = (d: DashboardTrendPoint) => d.resolved;

function tickFormat(index: number): string {
    const point = props.points[index];

    return point ? trendTick(props.granularity, point.label) : '';
}
</script>

<template>
    <Card class="flex flex-col border-border/60">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-semibold text-muted-foreground">
                {{ trans('dashboard.trend.title') }}
            </CardTitle>
            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[var(--chart-1)]" />
                    {{ trans('dashboard.trend.created') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[var(--chart-2)]" />
                    {{ trans('dashboard.trend.resolved') }}
                </span>
            </div>
        </CardHeader>
        <CardContent class="min-h-[220px] flex-1">
            <VisXYContainer v-if="hasData" :data="points" :height="220">
                <VisLine :x="x" :y="createdY" color="var(--chart-1)" />
                <VisLine :x="x" :y="resolvedY" color="var(--chart-2)" />
                <VisAxis type="x" :tick-format="tickFormat" :grid-line="false" />
                <VisAxis type="y" :grid-line="true" />
            </VisXYContainer>
            <div
                v-else
                class="flex h-[220px] items-center justify-center text-sm text-muted-foreground"
            >
                {{ trans('dashboard.trend.empty') }}
            </div>
        </CardContent>
    </Card>
</template>
```

- [ ] **Step 2: Create `PeriodControl.vue`** (mode toggle + month/year selects, Inertia visit)

```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { availableYears, useDashboard } from '@/composables/useDashboard';
import { useTrans } from '@/composables/useTrans';
import type { DashboardPeriodProp } from '@/types';

const props = defineProps<{ period: DashboardPeriodProp }>();

const { trans, locale } = useTrans();
const { periodUrl } = useDashboard();

const currentYear = new Date().getFullYear();
const years = availableYears(currentYear);

const months = computed(() =>
    Array.from({ length: 12 }, (_, i) => ({
        value: i + 1,
        label: new Date(2000, i, 1).toLocaleDateString(locale.value, {
            month: 'long',
        }),
    })),
);

function visit(next: DashboardPeriodProp): void {
    router.visit(periodUrl(next), {
        preserveScroll: true,
        preserveState: true,
        only: ['live', 'periodMetrics', 'compliance', 'trend', 'breakdown', 'period'],
    });
}

function setMode(mode: 'monthly' | 'yearly'): void {
    visit({
        mode,
        year: props.period.year,
        month: mode === 'monthly' ? (props.period.month ?? new Date().getMonth() + 1) : null,
    });
}

function setMonth(month: number): void {
    visit({ ...props.period, mode: 'monthly', month });
}

function setYear(year: number): void {
    visit({ ...props.period, year });
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <div class="inline-flex rounded-lg border border-border/60 p-0.5">
            <Button
                v-for="mode in (['monthly', 'yearly'] as const)"
                :key="mode"
                size="sm"
                :variant="period.mode === mode ? 'default' : 'ghost'"
                @click="setMode(mode)"
            >
                {{ trans(`dashboard.period.${mode}`) }}
            </Button>
        </div>

        <Select
            v-if="period.mode === 'monthly'"
            :model-value="String(period.month)"
            @update:model-value="(v) => setMonth(Number(v))"
        >
            <SelectTrigger class="w-[140px]">
                <SelectValue :placeholder="trans('dashboard.period.month')" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="m in months"
                    :key="m.value"
                    :value="String(m.value)"
                >
                    {{ m.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            :model-value="String(period.year)"
            @update:model-value="(v) => setYear(Number(v))"
        >
            <SelectTrigger class="w-[100px]">
                <SelectValue :placeholder="trans('dashboard.period.year')" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-for="y in years" :key="y" :value="String(y)">
                    {{ y }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
```

> Verify `@/components/ui/select` exists (`ls resources/js/components/ui/select`). If the project lacks a Select primitive, add it via `npx shadcn-vue add select` or fall back to native `<select>` styled with theme tokens. The `Select` value model uses strings.

- [ ] **Step 3: Verify typecheck**

Run: `pnpm run types:check`
Expected: no new errors from these files.

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/dashboard/TrendChart.vue resources/js/components/dashboard/PeriodControl.vue
git commit -m "feat(dashboard): add TrendChart and PeriodControl components"
```

---

### Task 17: Rewrite `Dashboard.vue` page

**Files:**
- Replace: `resources/js/pages/Dashboard.vue`
- Verify: `pnpm run types:check`, `pnpm run lint:check`, `pnpm run format:check`, `pnpm run build`

- [ ] **Step 1: Replace the page**

Overwrite `resources/js/pages/Dashboard.vue`:

```vue
<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    Inbox,
    Ticket,
    UserCheck,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';

import BreakdownDonut from '@/components/dashboard/BreakdownDonut.vue';
import MetricCard from '@/components/dashboard/MetricCard.vue';
import PeriodControl from '@/components/dashboard/PeriodControl.vue';
import SlaGauge from '@/components/dashboard/SlaGauge.vue';
import TrendChart from '@/components/dashboard/TrendChart.vue';
import { useDashboard } from '@/composables/useDashboard';
import { dashboard } from '@/routes';
import type {
    AuthenticatedSharedPageProps,
    DashboardProps,
} from '@/types';

const props = defineProps<DashboardProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page = usePage<AuthenticatedSharedPageProps>();
const { trans, periodLabel, previousPeriodLabel } = useDashboard();

const userName = computed(() => page.props.auth.user?.name ?? '');
const periodText = computed(() => periodLabel(props.period));
const previousText = computed(() => previousPeriodLabel(props.period));

const liveIcons: Record<string, Component> = {
    active: Ticket,
    assigned: UserCheck,
    unassigned: Inbox,
    pending_approval: Clock,
    sla_breached: AlertTriangle,
};

const metricIcons: Record<string, Component> = {
    created: Ticket,
    resolved: CheckCircle2,
};
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-6">
        <Head title="Dashboard" />

        <!-- Header: greeting + period control (design-system aligned, no gradient) -->
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ trans('dashboard.greeting', { name: userName }) }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ trans('dashboard.subtitle', { period: periodText }) }}
                </p>
            </div>
            <PeriodControl :period="period" />
        </div>

        <!-- Zone: Live snapshot -->
        <section class="space-y-3">
            <h2 class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                {{ trans('dashboard.live.heading') }}
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <MetricCard
                    v-for="metric in live"
                    :key="metric.key"
                    :label="trans(`dashboard.live.${metric.key}`)"
                    :value="metric.value"
                    :icon="liveIcons[metric.key] ?? Ticket"
                />
            </div>
        </section>

        <!-- Zone: Period -->
        <section class="space-y-3">
            <h2 class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                {{ trans('dashboard.metric.heading', { period: periodText }) }}
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <MetricCard
                    v-for="metric in periodMetrics"
                    :key="metric.key"
                    :label="trans(`dashboard.metric.${metric.key}`)"
                    :value="metric.value"
                    :icon="metricIcons[metric.key] ?? Ticket"
                    :delta-percent="metric.deltaPercent"
                    :direction="metric.direction"
                    :sentiment="metric.sentiment"
                    :caption="trans('dashboard.period.vs_previous', { period: previousText })"
                />
                <SlaGauge
                    v-if="compliance"
                    :compliance="compliance"
                    :previous-label="previousText"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <TrendChart
                        :granularity="trend.granularity"
                        :points="trend.points"
                    />
                </div>
                <BreakdownDonut
                    :type="breakdown.type"
                    :segments="breakdown.segments"
                />
            </div>
        </section>
    </div>
</template>
```

- [ ] **Step 2: Run the full frontend gate**

```bash
pnpm run types:check
pnpm run lint:check
pnpm run format:check
pnpm run build
```
Expected: all PASS. If `format:check` fails, run `pnpm run format` and re-commit.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/Dashboard.vue
git commit -m "feat(dashboard): rebuild dashboard page with period zones"
```

---

## PHASE 4 — Verification & handoff

### Task 18: Full backend suite + CI gate

**Files:** none (verification only)

- [ ] **Step 1: Run the dashboard test group**

Run: `php artisan test --compact --filter=Dashboard`
Expected: PASS — all unit + feature dashboard tests green.

- [ ] **Step 2: Run the full suite**

Run: `php artisan test --compact`
Expected: PASS. If any non-dashboard test referenced the removed `recentTickets`/`metrics`/`charts` props or `GetDashboardMetrics`, fix it now (search `rg -n "recentTickets|GetDashboardMetrics|->where\('charts'" tests`).

- [ ] **Step 3: Run the project CI check**

Run: `composer run ci:check`
Expected: PASS — `lint:check`, `format:check`, `types:check`, phpstan, rector check, and tests all green.

- [ ] **Step 4: Fix any phpstan/rector findings**

If larastan flags array-shape or generic-Builder issues in the new `Support/` or `Builders/` classes, add precise PHPDoc array shapes (already provided above) or `@param Builder<Ticket>` annotations until clean. Re-run `composer run ci:check`.

- [ ] **Step 5: Commit any fixups**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "chore(dashboard): satisfy ci:check"
```

---

### Task 19: Manual smoke (optional but recommended)

**Files:** none

- [ ] **Step 1: Boot the app**

Run: `composer run dev` (or ensure `pnpm run dev` is running).

- [ ] **Step 2: Verify each role + period**

Log in as a requester, an IT agent, and a super admin. For each:
- Confirm Live zone shows the role's snapshot cards; Period zone shows delta cards + trend + donut (+ gauge for agent/admin).
- Toggle Monthly ↔ Yearly: trend x-axis switches day ↔ month; cards/donut recompute; URL gains `?mode=...`.
- Switch locale to `id` (General Settings) and confirm all dashboard strings translate.
- Hover the SLA gauge: tooltip shows "X of Y tickets resolved on time" + vs-previous line.

- [ ] **Step 3: Confirm no console errors**

Use Boost `browser-logs` (or the browser console) to confirm no Vue/runtime errors on the dashboard route.

---

### Task 20: Finish the branch

**Files:** none

- [ ] **Step 1: Confirm clean status**

Run: `git status --short` → Expected: clean (all work committed).

- [ ] **Step 2: Hand off**

Use the `superpowers:finishing-a-development-branch` skill to choose merge / PR / cleanup. Per project convention (`mem:handoff/git_publish`), open a PR from `dev` and follow the versioning workflow before merge.

---

## Self-Review Notes

- **Spec coverage:** filter (monthly/yearly toggle) → Task 2/9/16; two zones → Task 17 sections; per-role inventory → Tasks 5–7 + contract table; delta sentiment colouring → Task 13; trend chart → Task 16; period donut → Task 15; SLA gauge `@unovis` + tooltip → Task 14; design-system alignment (drop gradient/sparkles, theme tokens) → Task 17; i18n en+id → Task 10/12; recent-tickets table removed → Task 9 (props dropped) + Task 17 (absent); driver-aware DB → Task 4; default current month + validation/fallback → Task 2/9; tests (feature per-role + unit Period/Delta + frontend checks) → Tasks 2,3,5–9,18; staged 4-phase delivery → phase structure.
- **Type consistency:** payload keys (`live`, `periodMetrics`, `compliance`, `trend`, `breakdown`, `direction`, `sentiment`, `deltaPercent`) are identical across PHP builders, the `DashboardProps` TS types (Task 11), and component props (Tasks 13–17).
- **Open verification points flagged inline** (do not skip): `@unovis` `VisDonut`/`VisLine` color callback signature; existence of `@/components/ui/select`; `cn` import path. Each has a stated fallback.
