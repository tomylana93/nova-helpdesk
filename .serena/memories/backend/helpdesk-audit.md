# Helpdesk Audit

- All audit findings from 2026-06-08 have been fully resolved and covered by tests:
  - [x] **Permission split bug**: Fixed by adding the `approve` ability to `TicketPolicy` checking for `manage approvals` permission and updating `TicketApprovalController` to authorize against `approve` instead of `update`.
  - [x] **Comment visibility**: Fixed by adding custom validation to `StoreTicketCommentRequest` to fail if a requester submits an `internal` comment.
  - [x] **Ticket relational integrity**: Fixed by validating that `branch_id`, `department_id`, `queue_id`, and `category_id` references are active (`status => active`).
  - [x] **Edit form assignee limitations**: Fixed by validating that `assigned_to` possesses the `it_agent` role in `UpdateTicketRequest`.
  - [x] **Mismatched branch/department combinations**: Fixed by validating in request classes that the selected `department_id` belongs to the selected `branch_id`.
