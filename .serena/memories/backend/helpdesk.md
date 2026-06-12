# Backend / Helpdesk

- Routes in `routes/helpdesk.php` under `auth` + `active` middleware. Lifecycle endpoints:
  `tickets.transition` (agent status change, `update` policy), `tickets.approve`/`tickets.reject`
  (SR approval), `tickets.reopen`/`tickets.confirm-resolved` (requester), `tickets.comments.store`.
- Role-aware Show (`TicketController@show`) passes `viewerRole` + `canAct`/`canReply`/
  `canSeeInternal`/`availableTransitions`/`canApprove`/`canReopen`/`canConfirm`.
  - `it_agent`: full lifecycle actor (canAct, transitions from `TicketStatus::agentActionableTransitions()`).
  - `super_admin` + `auditor`: read-only oversight — `canSeeInternal` true, but canAct/availableTransitions/
    canApprove false. super_admin's `Gate::before` bypass and auditor's oversight access must NOT surface
    requester controls; guarded by `$isRequesterActor`.
  - `$isRequesterActor = $isOwner && !$isAgent && !$isSuperAdmin` (owner === `ticket->requester_id`).
    A requester OR an auditor who OPENED a ticket gets `canReply`(public)/`canReopen`/`canConfirm` on THAT ticket.
  - `canSeeInternal = $isAgent || $isSuperAdmin || $isAuditor` (visibility = oversight privilege, decoupled
    from action capability). Comment composer internal/public selector gated by `canAct` (agents only).
  - Auditor = read-only org-wide oversight + reports that can also open tickets like a requester. See
    `mem:backend/admin` for the auditor role/permission catalog.
- `TicketTable` has an agent-inbox `view` filter (mine/unassigned/overdue) shown to it_agent, auditor, super_admin;
  requesters are restricted to their own tickets.
- Ticketing model:
  - `Ticket` (`app/Models/Ticket.php`): Key fields are `ticket_number` (auto-generated in `creating` hook, e.g. `INC-XXXXX` or `REQ-XXXXX` based on `TicketType`), `type` (`TicketType` enum), `status` (`TicketStatus` enum, defaults to `Open`), `priority` (`TicketPriority` enum, defaults to `Low`), `subject`, `description`, dates (`submitted_at`, `first_response_due_at`, `resolution_due_at`, `resolved_at`, `closed_at`).
  - `TicketComment` (`app/Models/TicketComment.php`): Stores comments; fields are `ticket_id`, `user_id`, `body`, `visibility` (`public` or `internal`).
  - `TicketApproval` (`app/Models/TicketApproval.php`): Stores approval details; fields are `ticket_id`, `reviewer_id`, `status` (`pending`, `approved`, `rejected`), `decided_at`, `decision_note`).
  - `TicketAttachment` (`app/Models/TicketAttachment.php`): Associates uploaded files with tickets.
  - `TicketActivity` (`app/Models/TicketActivity.php`): Records ticket lifecycle events (e.g. created, assigned, updated).
- Actions (`app/Actions/Helpdesk/`):
  - `CreateTicket`: Stores ticket, runs `AssignSlaPolicy`, and logs activity. Org context (`branch_id`/`department_id`)
    is ALWAYS inherited from the creator's profile, never asked on the form — requester AND auditor accounts are
    guaranteed to have branch+department (see `mem:backend/admin`).
  - `UpdateTicket`: Updates ticket details, changes assignee/status/priority, and logs activity.
  - `AssignSlaPolicy`: Finds active `SlaPolicy` by specificity (Phase 5, queue removed): (1) `priority` + `type`, (2) `priority` + `type IS NULL`. Sets `first_response_due_at`/`resolution_due_at` by direct assignment + `save()` (these are NON-fillable system fields — `update([...])` silently drops them).
  - `ApproveTicket`: Sets approval status to `approved` and ticket status to `InProgress`.
  - `RejectTicket`: Sets approval status to `rejected` and ticket status to `Closed`.
  - `AddTicketComment`: Stores a comment on a ticket.
- Tables & Filters:
  - `TicketTable` (`app/Tables/Helpdesk/TicketTable.php`) uses `spatie/laravel-query-builder`.
  - Requesters are restricted to viewing only their own tickets (`requester_id === auth()->id()`).
  - Support exact filtering on `status`, `type`, `priority`, and custom global search on `subject` and `ticket_number`.
