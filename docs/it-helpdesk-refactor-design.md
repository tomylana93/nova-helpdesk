# Design: IT Helpdesk Flow Refactor (1-Agent Model)

> Status: Approved for planning
> Last updated: 2026-06-10
> Supersedes the lifecycle/role assumptions in `docs/it-helpdesk-prd.md` for the current delivery.
> The PRD remains the long-term vision (SLA, KB, assets); this document narrows scope to a clear,
> operable flow for a single-company IT helpdesk where **realistically one agent** works tickets and
> **super admin is a system account only**.

## Table of Contents
- [Goal](#goal)
- [Decisions (locked)](#decisions-locked)
- [Roles](#roles)
- [Status State Machine](#status-state-machine)
- [Assignment](#assignment)
- [Requester UX](#requester-ux)
- [Agent UX](#agent-ux)
- [Super Admin UX](#super-admin-ux)
- [Notifications Matrix](#notifications-matrix)
- [Master Data & SLA Impact](#master-data--sla-impact)
- [Modern Patterns](#modern-patterns)
- [Data Model Changes](#data-model-changes)
- [Out of Scope](#out-of-scope)

## Goal
Deliver an unambiguous ticket lifecycle and UX for three personas, removing super admin from the
ticket lifecycle while preserving their full privileges, and trimming requester friction to the
minimum. Use modern, explicit patterns (state machine, thin controllers + actions, targeted
notifications) so the flow is testable and easy to extend when more agents are added.

## Decisions (locked)
1. **Agent approves Service Requests.** A `PendingApproval` state is retained but reframed: the
   assigned agent (not super admin) approves/rejects before work begins. Incidents skip approval.
2. **Auto-assign on creation.** New tickets are assigned to an active agent immediately.
3. **Minimal requester form.** Category, priority, subject, description, attachments. Branch &
   department auto-derive from the requester's profile (**guaranteed present** — see decision 4).
   Queue removed.
4. **Requester users must have branch + department.** User master data validation requires
   `branch_id` and `department_id` when the role is `requester`; not required for `it_agent` /
   `super_admin`. This guarantees the ticket form can safely derive org context server-side.
5. **Simplified statuses.** 7 states; `Triaged` dropped, `WaitingForApproval` renamed to
   `PendingApproval`.
6. **Super admin excluded from the lifecycle flow, but can view the ticket table.** The "Tickets"
   nav and index/show stay visible to super admin as **read-only** (oversight). They are never a
   notification/assignment/approval target and see no lifecycle action controls; full access still
   available via `Gate::before`.
7. **Queue deprecated** entirely (form, nav, SLA dimension). DB may be reset; migrations edited.

## Roles
| Role | Ticket lifecycle | Master data & settings |
|------|------------------|------------------------|
| `requester` | Create, track, reply when asked, confirm-close or reopen | — |
| `it_agent` | Full owner: approve SR, work, reply, change status, resolve, close | — |
| `super_admin` | **Not involved** (no notifications, not an assignment/approval target); full access via `Gate::before` | **Full owner** |

Permission grants unchanged in shape (`SyncRolesCommand`): `manage approvals` stays on `it_agent`.
Super admin passes all gates via `Gate::before`. The lifecycle simply never *targets* super admin.

## Status State Machine
Enum `TicketStatus` becomes the single source of truth for legality of transitions via
`canTransitionTo(TicketStatus $to): bool`. Controllers/actions never call a bare `update(['status'])`
without consulting it; `UpdateTicketRequest` (or a dedicated transition request) rejects illegal moves.

States: `Open`, `PendingApproval`, `InProgress`, `WaitingForRequester`, `Resolved`, `Closed`, `Reopened`.

```
INCIDENT
  Open ─▶ InProgress ─▶ Resolved ─▶ Closed
              ▲             │
  WaitingForRequester ◀─────┘
              │
         Reopened ◀── (requester reopens from Resolved/Closed) ─▶ InProgress

SERVICE REQUEST
  PendingApproval ─approve─▶ InProgress ─▶ ... (as above)
                  └─reject──▶ Closed   (reason saved on TicketApproval trail)
```

Allowed transitions (initial → set):
- `Open` → `InProgress`, `WaitingForRequester`, `Closed`
- `PendingApproval` → `InProgress` (approve), `Closed` (reject)
- `InProgress` → `WaitingForRequester`, `Resolved`, `Closed`
- `WaitingForRequester` → `InProgress`, `Resolved`, `Closed`
- `Resolved` → `Closed` (confirm), `Reopened` (requester) 
- `Closed` → `Reopened` (requester, within reason)
- `Reopened` → `InProgress`, `WaitingForRequester`, `Resolved`

Every accepted transition writes a `TicketActivity` row and fires the matching targeted notification.

## Assignment
`CreateTicket` resolves an assignee through an `AssignTicketToAgent` action:
- Exactly one active `it_agent` → assign to them.
- Multiple active agents → **least open-ticket load**, tie-break by oldest agent `created_at`.
- Zero active agents → leave `assigned_to = null`; ticket appears under an "Unassigned" filter; no
  notification target. (When an agent is later created, it surfaces in their Open list.)

Initial status after assignment:
- Incident → `Open`
- Service Request → `PendingApproval`

## Requester UX
- **Create** (`tickets/Create`): category, priority, subject, description, attachments only. Branch &
  department resolved server-side from profile (guaranteed present for requesters). No queue.
- **My Tickets** (`tickets/Index`): own tickets, status badges, "New Ticket" CTA.
- **Show** (`tickets/Show`): status timeline, public agent replies, a reply box that is emphasized
  when status is `WaitingForRequester`. When `Resolved`, show **Confirm Resolved** (→ `Closed`) and
  **Reopen** (→ `Reopened`) actions. Internal notes never visible.
- Requester notifications: created, status changed, agent replied, approval outcome, resolved/closed.

## Agent UX
- **Inbox** filters: "Open / Assigned to me", "Pending Approval", "Waiting on Requester",
  "Overdue (SLA)".
- **Show** agent controls driven by the state machine: approve/reject (SR only), start work, set
  waiting, resolve, close; clear separation of **internal note vs public reply**; change
  priority/category.
- Agent notifications: ticket auto-assigned (new), SR needs approval, requester replied, SLA
  risk/breach (now targeted to the assigned agent).

## Super Admin UX
- Sidebar shows **Settings**, **Master Data**, and **Tickets** (read-only oversight of the table).
- Can open the ticket index and Show pages, but **lifecycle action controls are hidden** (no
  approve/reject, assign, status change, reply, resolve/close) — viewing only.
- Never pulled into the flow: not an assignment/approval target, receives no ticket notifications.
  Full mutating access still technically available via `Gate::before` for emergencies, but the UI
  does not surface it.

## Notifications Matrix
| Event | Requester | Assigned Agent | Super Admin |
|-------|-----------|----------------|-------------|
| Ticket created | ✅ confirmation | ✅ new (or "needs approval" for SR) | ❌ |
| Approval decided (SR) | ✅ outcome | — | ❌ |
| Status changed | ✅ | — | ❌ |
| Public reply by agent | ✅ | — | ❌ |
| Public reply by requester | — | ✅ | ❌ |
| Resolved | ✅ | — | ❌ |
| Reopened | — | ✅ | ❌ |
| SLA at-risk / breached | — | ✅ | ❌ |

All notifications are `database` + `broadcast` (existing `TicketNotification`), delivered to the
recipient's personal Echo channel. The shared `helpdesk.agents` broadcast for new tickets is removed
(superseded by the persisted, auto-assigned notification). `TicketCreated`/`helpdesk.agents` channel
is retired or repurposed only if still needed for SLA; default is removal.

## Master Data & SLA Impact
- **Queue**: model, migration, table, nav, form options, policy, and the `queue_id` columns on
  `tickets` and `sla_policies` are removed. SLA matching reduces to `type` + `priority` specificity.
- **Branch / Department**: kept for org context & reporting; **required for requester users** (user
  master data validation), auto-derived onto their tickets. Not required for agent/super admin users.
- **Category**: kept (parent/sub) — only field in the minimal form besides priority.
- **SLA**: kept and functional; `AssignSlaPolicy` specificity becomes (1) `priority`+`type`,
  (2) `priority`+`type IS NULL`. Escalation notifies the assigned agent.

## Modern Patterns
- `TicketStatus` state machine (`canTransitionTo`, `isOpen`, `label`, `variant`).
- Thin controllers; intent-named actions with `handle()` (`CreateTicket`, `AssignTicketToAgent`,
  `TransitionTicketStatus`, `ApproveTicket`, `RejectTicket`, `AddTicketComment`).
- Form Requests own validation + transition authorization; pass `validated()` subsets into actions.
- `TicketActivity` as the audit trail for every state/assignment/approval change.
- Queueable, targeted `TicketNotification`; no broadcast fan-out to roles.
- Inertia v3 (`Inertia::defer`, `<Form>`/`useForm`), Wayfinder typed routes, Pest TDD per transition
  and per role guard.

## Data Model Changes
- `tickets.status` enum values updated (remove `triaged`, `waiting_for_approval`; add `pending_approval`,
  `open`, `reopened` if not already present). Migrations edited; local DB reset.
- Drop `tickets.queue_id`, `sla_policies.queue_id`, and the `queues` table.
- No change to `requester_id`, `assigned_to`, comments, attachments, approvals (reused; approver is now
  the agent).
- Seeders/factories updated to the new status set and to seed at least one active agent.

## Out of Scope
Knowledge base, asset inventory, multi-tenant, round-robin assignment, auto-close timers, and the
broader PRD Phase 3 items. Multi-agent is *supported* by the assignment rule but not optimized.
