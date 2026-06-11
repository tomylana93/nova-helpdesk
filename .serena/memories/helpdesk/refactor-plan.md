# Helpdesk Flow Refactor Plan (1-Agent Model)

Active refactor narrowing the helpdesk to a single-company, ~1-agent model. Full design in
`docs/it-helpdesk-refactor-design.md`; phased checklist in `docs/it-helpdesk-refactor-steps.md`.
Supersedes lifecycle/role assumptions in `docs/it-helpdesk-prd.md` for current scope (PRD stays the
long-term vision). See also `mem:backend/helpdesk`, `mem:backend/admin`.

## Locked decisions
- **Agent approves Service Requests** (not super admin). Keep an approval-pending state.
- **Auto-assign** new tickets to an active agent (single → that agent; many → least open-ticket load,
  tie-break oldest; none → unassigned).
- **Minimal requester ticket form**: category, priority, subject, description, attachments. Branch &
  department derived from requester profile, not asked.
- **Requester users require branch + department** (user master data validation); agent/super_admin do
  not. Guarantees ticket-form derivation is safe.
- **Simplified statuses** (7): `Open, PendingApproval, InProgress, WaitingForRequester, Resolved,
  Closed, Reopened`. Dropped `New, Triaged`; renamed `WaitingForApproval` → `PendingApproval`.
  `TicketStatus::canTransitionTo()` is the state-machine source of truth.
- **Super admin = system account, read-only oversight**: can view the ticket table (index/Show stay
  visible) but is NOT in the lifecycle flow — no lifecycle action controls in UI, never a
  notification/assignment/approval target; full mutating access still via `Gate::before`.
- **Queue deprecated entirely**: remove model/controller/table/policy/nav, `tickets.queue_id`,
  `sla_policies.queue_id`, `queues` table. SLA matching reduces to `type`+`priority`.

## Constraints
- Migrations may be edited; local DB may be reset (`migrate:fresh --seed`).
- Use modern patterns: enum state machine, thin controllers + intent-named actions with `handle()`,
  Form Requests own validation + transition authorization, `TicketActivity` audit trail, queueable
  targeted `TicketNotification` (no role broadcast fan-out), Inertia v3 + Wayfinder, Pest TDD.
- Verification per phase: auto-fix first (`composer lint` Pint, `composer refactor` Rector, `pnpm run
  lint`/`format`), then gate with **`composer ci:check`** (lint+format+types+phpstan+rector
  dry-run+test). Must be green before next phase.

## Status: COMPLETE — Phases 0–8 DONE (see `docs/it-helpdesk-refactor-steps.md` for the checklist).
Phase 8 refreshed Serena memories and annotated `docs/it-helpdesk-prd.md` (top banner + inline refactor
notes on Personas/§4 Queues/§5 SLA/§6 Approval/§9 Notifications); PRD stays the long-term vision. Phase 7 added role-aware ticket UX: `TicketController@show` exposes
`viewerRole`/`canAct`/`canReply`/`canSeeInternal`/`availableTransitions`/`canApprove`/`canReopen`/
`canConfirm` (canReopen/canConfirm guarded by `$isRequester` so super_admin's `Gate::before` bypass never
surfaces controls — super_admin is read-only); new `tickets.transition` endpoint
(`TicketLifecycleController@transition`) drives agent status buttons via
`TicketStatus::agentActionableTransitions()`; `TicketTable` gained an agent-only `view` filter
(mine/unassigned/overdue). Phase 5 removed Queue entirely (model/controller/actions/table/policy/`manage queues`
permission/nav/translations, `queue_id` on tickets+sla_policies, `queues` table); `AssignSlaPolicy`
specificity reduced to `type`+`priority` and fixed to set due dates via direct assignment+`save()`
(non-fillable system fields). Phase 6 made SLA escalation notify the assigned agent only (no role
fan-out); removed dead events `SlaEscalated`+`TicketCreated` and the `helpdesk.agents` broadcast channel;
super_admin is now never a notification/broadcast target. Full `composer ci:check` green (167 tests).
Update this memory and `mem:backend/helpdesk` / `mem:backend/admin` as phases land.
