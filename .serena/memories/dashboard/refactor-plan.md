# Dashboard Refactor — Agreed Design

Full task-by-task plan: `docs/superpowers/plans/2026-06-11-dashboard-refactor.md`.
Decided via grill-me on 2026-06-11. Scope = structural refactor + UX/metric/visual change (not behavior-preserving).

## Backend layout (replaces single 281-line `GetDashboardMetrics`, now deleted)
- `app/Actions/Dashboard/GetDashboardData.php` — thin orchestrator, role → builder, prepends `role`+`period`.
- `Builders/{Requester,Agent,Admin}Dashboard.php` — per-role payload (role variants preserved, NOT unified).
- `Support/DashboardPeriod.php` — VO from `mode/month/year`; `start/end/previousStart/previousEnd/granularity/toArray`; clamps invalid → safe defaults (no 400/500).
- `Support/TicketMetricQueries.php` — reusable counts/compliance/trend; date-group expr is **driver-aware** (`DB::connection()->getDriverName()`: sqlite strftime / mysql DATE_FORMAT / pgsql to_char). Prod = MySQL/Postgres, dev/tests = SQLite.
- `Support/Delta.php` — `compute(current, previous)` → `{deltaPercent, direction}`; prev=0 & grew → deltaPercent null (label "New").
- `app/Http/Requests/DashboardRequest.php` — empty rules, `toPeriod()`; controller stays thin.
- `TicketStatus::activeCases()` helper added (DRY for not-resolved/closed).

## Model: two zones
- **Live** (snapshot, NOT period-filtered): current-state counts.
- **Period** (filtered by `DashboardPeriod`): event metrics + delta vs previous period + trend + compliance.

## Filter
- Toggle **monthly | yearly**. Monthly = month+year picker, trend granularity `day`. Yearly = year picker, trend granularity `month` (12 dense points).
- Default = current month, monthly. Query `?mode=&month=&year=`; Inertia partial reload `only: [live,periodMetrics,compliance,trend,breakdown,period]`.

## Per-role inventory
- requester: live[active(own)]; period[created,resolved]; compliance=null; breakdown=priority.
- it_agent: live[assigned,unassigned,pending_approval,sla_breached]; period[resolved-by-me]; compliance=gauge; breakdown=status.
- super_admin: live[active,unassigned,pending_approval,sla_breached]; period[created,resolved]; compliance=gauge; breakdown=priority.
- sentiment: created=neutral, resolved=higher_is_better.

## Frontend
- `pages/Dashboard.vue` rewritten thin; components under `components/dashboard/`: PeriodControl, MetricCard, DeltaBadge, TrendChart, BreakdownDonut, SlaGauge. Composable `composables/useDashboard.ts` (label maps, periodUrl via Wayfinder, trend tick fmt).
- Visual: **drop gradient banner + sparkles**; use theme tokens (`--chart-*`, `--muted`, `--border`), align to app design system.
- Charts via `@unovis/vue` (Donut already used; add VisLine/VisAxis/VisXYContainer for trend). Delta colored by sentiment (neutral=muted, good=emerald, bad=destructive).
- **Recent Tickets table REMOVED** (use tickets index); drops `FormatTicketSla` dep from dashboard.
- SLA gauge = `@unovis` donut (token-aware, not manual SVG) + tooltip "X of Y resolved on time" + vs-previous.

## i18n (full, en+id)
- New `lang/{en,id}/dashboard.php`; run `php artisan lang:export` → regenerates `lang/{en,id}.json` consumed by `useTrans`/`trans('dashboard.*')`.
- Backend sends **keys** (metric/live key, enum value), NOT English strings → kills old `getIcon(label.includes(...))` anti-pattern.

## Delivery & tests
- 4 phases: (1) Support+unit tests, (2) builders+orchestrator+controller+feature tests, (3) frontend, (4) `composer run ci:check` + smoke.
- Feature tests per role (boundary dates, delta), unit tests DashboardPeriod (Jan→prev Dec; yearly) + Delta (÷0).
- Verify-before-implement flags in plan: `@unovis` color callback signature, `components/ui/select` existence, `cn` import path.
