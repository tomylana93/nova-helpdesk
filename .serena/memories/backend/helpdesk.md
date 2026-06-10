# Backend / Helpdesk

- Routes in `routes/helpdesk.php` under `auth` + `active` middleware.
- Ticketing model:
  - `Ticket` (`app/Models/Ticket.php`): Key fields are `ticket_number` (auto-generated in `creating` hook, e.g. `INC-XXXXX` or `REQ-XXXXX` based on `TicketType`), `type` (`TicketType` enum), `status` (`TicketStatus` enum, defaults to `New`), `priority` (`TicketPriority` enum, defaults to `Low`), `subject`, `description`, dates (`submitted_at`, `first_response_due_at`, `resolution_due_at`, `resolved_at`, `closed_at`).
  - `TicketComment` (`app/Models/TicketComment.php`): Stores comments; fields are `ticket_id`, `user_id`, `body`, `visibility` (`public` or `internal`).
  - `TicketApproval` (`app/Models/TicketApproval.php`): Stores approval details; fields are `ticket_id`, `reviewer_id`, `status` (`pending`, `approved`, `rejected`), `decided_at`, `decision_note`.
  - `TicketAttachment` (`app/Models/TicketAttachment.php`): Associates uploaded files with tickets.
  - `TicketActivity` (`app/Models/TicketActivity.php`): Records ticket lifecycle events (e.g. created, assigned, updated).
- Actions (`app/Actions/Helpdesk/`):
  - `CreateTicket`: Stores ticket, runs `AssignSlaPolicy`, and logs activity.
  - `UpdateTicket`: Updates ticket details, changes assignee/status/priority, and logs activity.
  - `AssignSlaPolicy`: Finds active `SlaPolicy` matching by specificity: (1) `priority` + `type` + `queue_id`, (2) `priority` + `type` + `queue_id IS NULL`, (3) `priority` + `type IS NULL` + `queue_id IS NULL`. Calculates response and resolution due times.
  - `ApproveTicket`: Sets approval status to `approved` and ticket status to `InProgress`.
  - `RejectTicket`: Sets approval status to `rejected` and ticket status to `Closed`.
  - `AddTicketComment`: Stores a comment on a ticket.
- Tables & Filters:
  - `TicketTable` (`app/Tables/Helpdesk/TicketTable.php`) uses `spatie/laravel-query-builder`.
  - Requesters are restricted to viewing only their own tickets (`requester_id === auth()->id()`).
  - Support exact filtering on `status`, `type`, `priority`, and custom global search on `subject` and `ticket_number`.
