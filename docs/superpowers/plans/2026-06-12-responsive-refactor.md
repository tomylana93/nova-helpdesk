# Responsive Refactor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make every Nova Helpdesk page work well at mobile (~375px), tablet (~768px), and desktop by sweeping each archetype and fixing the concrete deviations from the already-responsive shared patterns.

**Architecture:** The app shell (shadcn-vue sidebar + mobile `Sheet`) and shared primitives (`PageWrapper`, `DataTable*`) are already responsive. This is an audit-and-polish pass: bring outlier pages in line with the canonical patterns and remove fixed-width/hard-grid anti-patterns. No backend, route, or data changes. No new automated tests — the user verifies visually; the agent keeps `pnpm` checks and the Pest suite green.

**Tech Stack:** Vue 3 SFC, Inertia v3, Tailwind CSS v4, shadcn-vue/Reka UI, `@unovis/vue` charts.

---

## Canonical responsive patterns (reference)

**Page header (title + actions):** handled by `PageWrapper` —
`flex flex-col gap-3 md:flex-row md:items-start md:justify-between`, actions wrap.

**Form field + button bar** (from `admin/master-data/branches/Create.vue`, already correct):

```html
<form class="flex flex-col gap-6" @submit.prevent="submit">
  <div class="grid gap-2"> <!-- label + input, full width --> </div>
  <SelectTrigger class="w-full"> ... </SelectTrigger>
  <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
    <Button class="w-full sm:w-auto" ... />
  </div>
</form>
```

**Multi-column field group:** `grid grid-cols-1 gap-6 sm:grid-cols-2` (base = 1 col).

**Definition list (Show):** stack on mobile, columns on larger:
`grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2`. Label uses
`text-sm font-medium text-muted-foreground` with NO `min-w-[...]`.

**Fixed-width control:** `w-full sm:w-[140px]` (full width when stacked on mobile).

---

## Task 1: Dashboard archetype

**Files:**
- Modify: `resources/js/components/dashboard/PeriodControl.vue:89,107`
- Audit: `resources/js/pages/Dashboard.vue`, `resources/js/components/dashboard/*`

- [ ] **Step 1: Make PeriodControl selects full-width on mobile**

In `PeriodControl.vue`, change the two fixed-width triggers:

```html
<!-- line 89 -->
<SelectTrigger class="w-full sm:w-[140px]">
<!-- line 107 -->
<SelectTrigger class="w-full sm:w-[100px]">
```

Verify the control's container stacks on mobile (wrap the selects in
`flex flex-col gap-2 sm:flex-row sm:items-center` if they are currently inline-only).

- [ ] **Step 2: Audit Dashboard.vue grids (already responsive)**

Confirm these stay intact (no change expected): metric grids
`grid gap-4 sm:grid-cols-2 lg:grid-cols-4` / `lg:grid-cols-3`, and the
content grid `grid gap-6 lg:grid-cols-3` with `lg:col-span-2`. Ensure `@unovis`
chart wrappers have no fixed pixel width (fluid container). Replace any
`p-6`-only outer padding with `p-4 md:p-6` for tighter mobile gutters.

- [ ] **Step 3: Verify checks**

Run: `pnpm run types:check && pnpm run lint:check`
Expected: no errors. Then user does a visual pass on `/dashboard` at 375/768/1280.

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/dashboard resources/js/pages/Dashboard.vue
git commit -m "refactor(ui): responsive dashboard period control and gutters"
```

## Task 2: Detail (Show) archetype

**Files:**
- Modify: `resources/js/pages/admin/master-data/departments/Show.vue`
- Modify: `resources/js/pages/admin/master-data/ticket-categories/Show.vue`
- Modify: `resources/js/pages/admin/master-data/branches/Show.vue`
- Modify: `resources/js/pages/admin/master-data/users/Show.vue`
- Modify: `resources/js/pages/admin/master-data/sla-policies/Show.vue:51`
- Audit: `resources/js/pages/tickets/Show.vue:215`

- [ ] **Step 1: Remove forced label widths (4 master-data Show pages)**

In each of the four Show pages, replace every label cell class
`min-w-[140px] text-sm font-medium text-muted-foreground` with
`text-sm font-medium text-muted-foreground` (drop `min-w-[140px]`). If a row is
laid out as `flex`, change it to stack on mobile:
`flex flex-col gap-1 sm:flex-row sm:gap-4`.

Run to find every occurrence:
`rg -n "min-w-\[140px\]" resources/js/pages`

- [ ] **Step 2: Fix hard 2-column definition list in sla-policies/Show.vue**

Line 51: change `<dl class="grid grid-cols-2 gap-x-8 gap-y-4">` to
`<dl class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">`.

- [ ] **Step 3: Soften tickets/Show definition grid**

`tickets/Show.vue:215`: change `grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-3`
to `grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2 lg:grid-cols-3` so the meta
block is single-column on phones.

- [ ] **Step 4: Verify checks**

Run: `pnpm run types:check && pnpm run lint:check`
Expected: no errors. User visually checks each Show page at 375px.

- [ ] **Step 5: Commit**

```bash
git add resources/js/pages/admin/master-data/*/Show.vue resources/js/pages/tickets/Show.vue
git commit -m "refactor(ui): stack detail definition lists on mobile"
```

## Task 3: Datatable index archetype

**Files:**
- Audit: `resources/js/components/datatable/DataTable.vue` (table overflow wrapper)
- Audit: all `*/Index.vue` using `DataTable` and every `*TableActions.vue` overlay

- [ ] **Step 1: Confirm horizontal-scroll wrapper on the table**

In `DataTable.vue`, confirm the `<Table>` is inside an element with
`overflow-x-auto` (or add `class="w-full overflow-x-auto"` to the wrapping div).
This guarantees refined horizontal scroll on mobile (the chosen strategy).

- [ ] **Step 2: Audit filter controls + action dialogs**

For each `*TableActions.vue` (`tickets/TicketTableActions.vue`, and the five
master-data `*TableActions.vue`), confirm any `DialogContent`/`Sheet` uses a
mobile-safe width (e.g. `class="w-[calc(100vw-2rem)] sm:max-w-lg"` rather than a
fixed `w-[600px]`). Confirm filter `SelectTrigger`s are `w-full` (the toolbar
already wraps via `flex flex-wrap gap-3`).

- [ ] **Step 3: Verify checks**

Run: `pnpm run types:check && pnpm run lint:check`
Expected: no errors. User checks `tickets` + master-data index pages at 375px
(toolbar wraps, table scrolls, pagination stacks).

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/datatable resources/js/pages
git commit -m "refactor(ui): ensure datatable scroll and mobile-safe overlays"
```

## Task 4: Form (Create/Edit) archetype

**Files:**
- Audit/Modify: all `*/Create.vue` and `*/Edit.vue` under `tickets/` and
  `admin/master-data/*/` plus `admin/settings/*/Edit.vue`

- [ ] **Step 1: Bring every form in line with the canonical pattern**

`branches/Create.vue` and `tickets/Create.vue` are the reference (already
correct). For each form page, confirm: outer `form` is `flex flex-col gap-6`;
each field is `grid gap-2`; `SelectTrigger` is `w-full`; multi-column groups use
`grid grid-cols-1 gap-6 sm:grid-cols-2` (base 1 col); the button bar is
`flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center` with each
`Button class="w-full sm:w-auto"`. Fix any page that hardcodes `grid-cols-2`
without a base single-column.

Find candidates: `rg -ln 'type="submit"' resources/js/pages`

- [ ] **Step 2: Verify checks**

Run: `pnpm run types:check && pnpm run lint:check`
Expected: no errors. User checks tickets + master-data create/edit at 375px.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages
git commit -m "refactor(ui): normalize responsive form layouts"
```

## Task 5: Reports archetype

**Files:**
- Audit: `resources/js/pages/reports/Index.vue`

- [ ] **Step 1: Confirm filter grid + table**

The filter grid already reflows
(`grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5`) and
period controls stack (`flex flex-col gap-3 lg:flex-row`). Confirm the results
table sits inside an `overflow-x-auto` wrapper and cells keep truncation
(`max-w-[240px] truncate` etc.). Add the wrapper if missing. Ensure export
buttons stack/wrap on mobile (`flex flex-wrap gap-2`).

- [ ] **Step 2: Verify checks**

Run: `pnpm run types:check && pnpm run lint:check`
Expected: no errors. User checks `/reports` at 375/768.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/reports/Index.vue
git commit -m "refactor(ui): responsive reports table and export actions"
```

## Task 6: Settings archetype

**Files:**
- Modify: `resources/js/pages/admin/settings/style/Edit.vue:224`
- Audit: `resources/js/pages/admin/settings/*`, `resources/js/pages/settings/Profile.vue`,
  `resources/js/pages/settings/Security.vue`, `resources/js/layouts/settings/Layout.vue`

- [ ] **Step 1: Soften the style picker grid**

`style/Edit.vue:224`: if the `grid grid-cols-2 gap-3` block holds wide controls,
change to `grid grid-cols-1 gap-3 sm:grid-cols-2`. Leave it as-is only if the two
cells are small toggles that already fit at 375px.

- [ ] **Step 2: Audit settings layout + profile/security forms**

Confirm `layouts/settings/Layout.vue` collapses its side-nav on mobile (the
section nav should stack above content below `md`). Confirm Profile/Security
forms follow the Task 4 button-bar/field pattern. The avatar uploader row should
stack (`flex flex-col gap-4 sm:flex-row sm:items-center`).

- [ ] **Step 3: Verify checks**

Run: `pnpm run types:check && pnpm run lint:check`
Expected: no errors. User checks settings pages at 375px.

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/admin/settings resources/js/pages/settings resources/js/layouts/settings
git commit -m "refactor(ui): responsive settings layouts and pickers"
```

## Task 7: Auth archetype

**Files:**
- Audit: `resources/js/layouts/auth/*`, `resources/js/pages/auth/*`

- [ ] **Step 1: Confirm auth layouts (already mostly responsive)**

`AuthSplitLayout.vue` already hides the image panel below `lg` (`hidden lg:flex`)
and constrains the card (`w-full ... sm:w-[350px]`). Confirm `AuthCardLayout` and
`AuthSimpleLayout` center a `w-full max-w-sm`/`max-w-md` card with comfortable
mobile padding (`px-4`). Confirm each `auth/*` page form uses full-width inputs
and a full-width primary button on mobile.

- [ ] **Step 2: Verify checks**

Run: `pnpm run types:check && pnpm run lint:check`
Expected: no errors. User checks `/login`, `/forgot-password`, reset, confirm at 375px.

- [ ] **Step 3: Commit (only if changes were needed)**

```bash
git add resources/js/layouts/auth resources/js/pages/auth
git commit -m "refactor(ui): responsive auth layouts"
```

## Task 8: Final verification + handoff

- [ ] **Step 1: Full CI gate**

Run: `composer run ci:check`
Expected: eslint, prettier, vue-tsc, phpstan, rector, pint all pass; Pest suite
green (no regressions — no test files changed).

- [ ] **Step 2: Production build sanity**

Run: `pnpm run build`
Expected: build completes with no errors.

- [ ] **Step 3: User cross-breakpoint visual pass**

User reviews each archetype at 375 / 768 / 1280. Fix any reported spot, re-run
Step 1, recommit.

- [ ] **Step 4: Handoff**

Follow `mem:handoff/git_publish` only when the user asks (feat/refactor commits →
`composer version:bump` → `composer run ci:check` → push → PR → merge → deploy).
