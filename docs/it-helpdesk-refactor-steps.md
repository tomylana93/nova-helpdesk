# Refactor Steps: IT Helpdesk Flow (1-Agent Model)

> Companion to `docs/it-helpdesk-refactor-design.md`. Execute top-to-bottom; each phase is
> independently testable. Local DB may be reset (`php artisan migrate:fresh --seed`). TDD:
> write/adjust the failing test before each behavior change.
>
> **Verification per phase (required order):**
> 1. Auto-fix with existing repo tools first: `composer lint` (Pint), `composer refactor` (Rector),
>    `pnpm run lint` (ESLint --fix), `pnpm run format` (Prettier --write).
> 2. Then run the gate: **`composer ci:check`** (lint:check + format:check + types:check + phpstan
>    `@analyse` + rector dry-run `@refactor:check` + full `@test`). Must pass before moving on.
>
> Use focused `php artisan test --compact --filter=...` during the red/green loop; finish each phase
> with the full `composer ci:check` gate.

## Phase 0 — Guardrails ✅ DONE
- [x] Confirm green baseline (`composer ci:check` fully green: 129 tests).
- [x] Branch off `dev` for the refactor (`refactor/helpdesk-flow`).

## Phase 1 — Status state machine ✅ DONE
- [x] Update `TicketStatus`: states `Open, PendingApproval, InProgress, WaitingForRequester, Resolved,
      Closed, Reopened`; drop `New, Triaged, WaitingForApproval`. Update `label()`/`variant()`.
- [x] Add `canTransitionTo(TicketStatus $to): bool` (+ `allowedTransitions()`) per the design table.
- [x] Tests: `tests/Unit/TicketStatusTest.php` covers status set, `isOpen`, legal + illegal
      transitions. Cascade renames applied across actions, model, factory, TS types, Show.vue, tests.

## Phase 2 — Assignment ✅ DONE
- [x] Add `App\Actions\Helpdesk\AssignTicketToAgent` (`handle(Ticket): ?User`): single active agent →
      that agent; multiple → least open-ticket load, tie-break oldest; none → null.
- [x] `CreateTicket`: auto-assign via the action; initial status (`Open` incident / `PendingApproval`
      SR); notify only the assigned agent (assigned / approval_request); **super_admin removed from
      fan-out**; `helpdesk.agents` broadcast (`TicketCreated`) no longer dispatched on create.
- [x] Tests: `AssignTicketToAgentTest` (single/multi/none/disabled); NotificationTest covers
      auto-assign + agent notified, SR → PendingApproval to agent, super_admin never notified.

## Phase 3 — Transitions & approval ✅ DONE
- [x] Add `App\Actions\Helpdesk\TransitionTicketStatus` enforcing `canTransitionTo`, writing
      `TicketActivity`, stamping `resolved_at`/`closed_at` (and clearing them on Reopen), notifying the
      counterpart party (requester↔assigned agent).
- [x] `ApproveTicket`/`RejectTicket` keep `PendingApproval` guard + `approve` policy; agent-as-reviewer
      verified by test (`it agent can approve a pending service request`). approval_request now targets
      the assigned agent (Phase 2).
- [x] `UpdateTicket` routes status changes through the transition action; illegal moves rejected in
      `UpdateTicketRequest` via a `canTransitionTo` closure.
- [x] Requester actions: `TicketLifecycleController@reopen` (`Resolved`/`Closed`→`Reopened`) and
      `@confirm` (`Resolved`→`Closed`); policy abilities `reopen` + `confirmResolution`; routes
      `tickets.reopen` / `tickets.confirm-resolved`; lang keys added (en + id).
- [x] Tests: `tests/Feature/TicketLifecycleTest.php` (7) — agent resolve stamps + notifies, illegal
      transition rejected, requester reopen/confirm + ownership/state guards, agent approve.

## Phase 4 — Requester user validation + minimal ticket form ✅ DONE
- [x] User master data: `branch_id` + `department_id` required only when role is `requester`
      (`Rule::requiredIf` + `isRequester()` in `StoreUserRequest`/`UpdateUserRequest`); not required
      for `it_agent`/`super_admin`.
- [x] `StoreTicketRequest`: dropped `branch_id`/`department_id`/`queue_id`; keeps
      type/subject/description/priority/category.
- [x] `CreateTicket`: always derives `branch_id`/`department_id` from the requester profile (removed
      the agent/request-driven branching).
- [x] `tickets/Create.vue`: removed branch/department/queue fields and `isAgent` logic; minimal form
      (type/subject/description/priority/category) via `useForm`. (`GetTicketFormOptions.queueOptions`
      still sent for Edit.vue; removed in Phase 5.)
- [x] Tests: `MasterDataUserTest` — requester requires branch+department, staff does not;
      `HelpdeskTicketTest` — ticket inherits branch/department from profile (replaced the obsolete
      request-level branch/department validation tests).

## Phase 5 — Queue removal ✅ DONE
- [x] Removed `Queue` model, `QueueController`, `QueueTable`, `QueuePolicy`, `CreateQueue`/`UpdateQueue`,
      `Store/UpdateQueueRequest`, `QueueResource`/`QueueOptionResource`, factory, `queues.csv` +
      `ImportMasterDataCommand@importQueues`, `AdminPermission::ManageQueues`, `routes/admin.php`
      resource, frontend `queues/` pages + `types/queue.ts`, master-data nav card, sidebar/header nav
      gates, SLA/ticket form queue fields, and en/id translations (+`lang:export`, `wayfinder:generate`).
- [x] Edited migrations: dropped `queues` table migration, `tickets.queue_id`, `sla_policies.queue_id`.
- [x] `AssignSlaPolicy`: specificity reduced to `priority`+`type` / `priority`+`type IS NULL`. **Bug fix:**
      `first_response_due_at`/`resolution_due_at` are non-fillable system fields, so the old
      `update([...])` silently dropped them — now assigned directly + `save()`.
- [x] `migrate:fresh --seed` green. Factories cleaned of `queue_id`; statuses already new (Phase 1).
      `DatabaseSeeder` is a no-op prod guard — data is seeded via CSV import + `permission:sync-roles`,
      so no agent seeding belongs here.
- [x] Tests: new `tests/Feature/AssignSlaPolicyTest.php` (3) asserts specific match, type-agnostic
      fallback, and no-match leaves due dates null; removed `MasterDataQueueTest`; cleaned `TicketCoreTest`.
      Full `composer ci:check` green (166 tests).

## Phase 6 — Notifications targeting & SLA escalation ✅ DONE
- [x] Audited every `notify`/`Notification::send`: all `->notify()` target requester or assigned agent;
      `assigned_to` is validated as `it_agent` (`UpdateTicketRequest`), so super_admin is never a
      `->notify()` target. The only super_admin exposure was the `helpdesk.agents` broadcast channel
      (authorized it_agent **and** super_admin) — now removed.
- [x] `CheckSlaEscalation`: now notifies the assigned agent only (persisted `TicketNotification` on the
      personal channel via `notifyAssignee()`). Dropped the unassigned-ticket role fan-out
      (`Notification::send($agents)`) and the `SlaEscalated` broadcast. Unassigned overdue tickets have
      no notification target (surface via the Phase 7 "Overdue" inbox filter).
- [x] Removed dead events `app/Events/SlaEscalated.php` + `app/Events/TicketCreated.php` (the latter
      already undispatched since Phase 2) and the `helpdesk.agents` channel in `routes/channels.php`.
- [x] `NotificationDropdown.vue`: removed the `useEcho` `helpdesk.agents`/`SlaEscalated` listener +
      `SlaEscalatedEvent` interface (kept the personal `useEchoNotification` channel).
- [x] Tests: `NotificationTest` `check-sla` rewritten — assigned agent gets `sla_warning`/`sla_breached`,
      idempotent re-run sends nothing, and an **unassigned** overdue ticket notifies nobody (no fan-out).
      Full `composer ci:check` green (167 tests).

## Phase 7 — Role-aware UX ✅ DONE
- [x] Super admin keeps "Tickets" (index/Show render) but is **read-only**: `show()` passes
      `viewerRole`/`canAct`/`canReply`/`canReopen`/`canConfirm`, all false for super_admin. The
      `canReopen`/`canConfirm` flags are guarded by `$isRequester` so super_admin's `Gate::before`
      bypass never surfaces controls. (Full mutating access via `Gate::before`/direct URL is retained.)
- [x] `tickets/Show.vue`: state-machine action buttons for the agent driven by a new
      `TicketStatus::agentActionableTransitions()` (excludes approval moves + requester-only Reopened),
      posted to a new `tickets.transition` endpoint (`TicketLifecycleController@transition`, `update`
      policy + `TransitionTicketStatus` enforcement). Internal-note vs public-reply split via `canAct`
      (visibility toggle) / `canReply`; requester Confirm/Reopen buttons on Resolved/Closed; super_admin
      sees no action controls. Reply box shows a "waiting for your reply" hint when `WaitingForRequester`.
- [x] Agent inbox filter on `tickets/Index.vue`: new `view` select filter (Assigned to me / Unassigned /
      Overdue) added in `TicketTable` for agents+super_admin only; Pending Approval & Waiting on
      Requester are already covered by the existing status filter.
- [x] Smoke test: `tests/Feature/TicketRoleUxTest.php` renders Index/Create/Show for agent, requester,
      and super_admin (server-render smoke, asserts 200 + Inertia component). NOTE: a true browser
      JS-error smoke (Pest v4 `visit()`) was not added — no browser/Playwright Pest harness is configured
      in this repo; deferred rather than introducing new test infrastructure unrequested.
- [x] Thin controllers preserved (transition list lives on the `TicketStatus` enum). Full
      `composer ci:check` green (176 tests) + `pnpm run build` succeeds.

## Phase 8 — Cleanup & docs ✅ DONE
- [x] Serena memories updated across the refactor: `backend/helpdesk` (queue removed, SLA fix, lifecycle
      endpoints + role-aware show + view filter), `backend/admin` (queue/`manage queues`/`QueuePolicy`
      removed), `helpdesk/refactor-plan` (status through Phase 8). `frontend/core` needed no change — it
      describes general structure and does not enumerate the master-data nav cards that changed.
- [x] Annotated `docs/it-helpdesk-prd.md`: top-of-doc refactor banner + inline `> Refactor note:` on
      Personas/Roles, §4 Queues, §5 SLA, §6 Approval, §9 Notifications. PRD kept as the long-term vision.
- [x] Final `composer ci:check` green before handoff (after auto-fixes).

## Risk Notes
- Editing existing migrations means existing local data is discarded — coordinate `migrate:fresh`.
- Status enum value rename touches DB rows, factories, seeders, and any hardcoded status strings in
  tests/frontend — grep for old values (`new`, `triaged`, `waiting_for_approval`).
- Removing queue touches SLA matching — verify `AssignSlaPolicy` tests cover the reduced specificity.
