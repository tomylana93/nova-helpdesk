# Responsive Refactor — Design

Date: 2026-06-12
Status: Approved (design); pending implementation plan

## Goal

Make the entire Nova Helpdesk UI work well across mobile (~375px), tablet (~768px),
and desktop. The app shell (shadcn-vue sidebar with mobile `Sheet`) and the shared
layout/datatable primitives are already largely responsive; this is a systematic
audit-and-polish pass, not a rebuild.

## Scope

All 42 Inertia pages under `resources/js/pages/**`, grouped into 7 archetypes that
share patterns. Fixing the shared patterns cascades to many pages.

Archetypes:
1. **Datatable index** — `tickets/Index`, all `admin/master-data/*/Index`.
2. **Form (Create/Edit)** — tickets + all master-data create/edit.
3. **Detail (Show)** — tickets + all master-data show.
4. **Dashboard** — `Dashboard.vue` + `components/dashboard/*`.
5. **Reports** — `reports/Index.vue`.
6. **Settings** — `admin/settings/*`, `settings/Profile`, `settings/Security`.
7. **Auth** — `auth/*` + `layouts/auth/*`.

## Approach

Approach A (chosen): consolidate around the existing responsive primitives
(`PageWrapper`, `DataTableToolbar`, `DataTablePagination`, shadcn-vue sidebar) as the
standard, then sweep each archetype and fix deviations. Lowest risk, consistent.

Rejected: per-page ad-hoc fixes (duplication/inconsistency); rewriting the layout
system (overkill — shell already responsive).

## Conventions (applied uniformly)

- **Spacing/padding:** every page renders through `PageWrapper` (`px-4 py-6 md:px-6`,
  `space-y-6`). Pages not using it adopt it or match its rhythm.
- **Page header:** title + actions stack on mobile (`flex-col` → `md:flex-row`);
  action buttons may be `w-full sm:w-auto` where it improves tap targets.
- **Forms:** single column on mobile → `sm:`/`md:grid-cols-2` on larger; inputs
  `w-full`; primary submit button full-width on mobile (`w-full sm:w-auto`).
- **Detail (Show):** definition-list label/value pairs stack on small screens; avoid
  `min-w-[140px]` label columns that force horizontal overflow.
- **Datatable:** ensure the `overflow-x-auto` table wrapper is present on every index;
  filter selects `w-full` on mobile; toolbar/pagination follow the existing responsive
  pattern (no regressions). Mobile strategy = refined horizontal scroll (NOT card
  reflow), per decision.
- **Dashboard:** metric-card grid stays responsive; `@unovis` chart containers use
  fluid width; no fixed widths.
- **Reports:** filter grid already reflows; tables wrapped for horizontal scroll with
  cell truncation (`max-w-[...] truncate`).
- **Fixed widths:** problematic control widths (`w-[140px]`, `w-[100px]` on selects)
  become `w-full sm:w-[...]`. `max-w-[...] truncate` is fine and retained.
- **Auth:** `AuthSplitLayout` hides the image panel on mobile; card max-width + padding.
- **Overlays:** dialogs (`*TableActions.vue`) and filter sheets size correctly on
  mobile (no overflow, comfortable padding).

## Targets

- Mobile ~375px, tablet ~768px, desktop ≥1024px.

## Non-goals

- No card/stacked datatable reflow (horizontal scroll retained).
- No new automated tests (no Pest browser/visual-regression suite added).
- No changes to backend, routes, or data shape.

## Verification

- User verifies visually across breakpoints.
- Agent runs `pnpm run types:check`, `pnpm run lint:check`, `pnpm run format:check`,
  and `pnpm run build`; the existing Pest suite must stay green (`composer ci:check`
  before handoff). No new tests.

## Per-archetype checklist (implementation)

- [ ] Shared primitives audit (PageWrapper usage, header pattern, overlay sizing).
- [ ] Datatable index archetype.
- [ ] Form (Create/Edit) archetype.
- [ ] Detail (Show) archetype.
- [ ] Dashboard archetype.
- [ ] Reports archetype.
- [ ] Settings archetype.
- [ ] Auth archetype.
- [ ] Final CI green + cross-breakpoint visual pass.
