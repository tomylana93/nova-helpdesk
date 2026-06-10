# Helpdesk Audit

- Approval flow has a permission split bug: frontend exposes `manage_approvals` in `HandleInertiaRequests`, but `TicketApprovalController` authorizes with ticket `update`, so any user with `update tickets` can approve/reject without `manage approvals`.
- Comment visibility is UI-only for non-agents: `resources/js/pages/tickets/Show.vue` hides the visibility selector unless `isAgent`, but `StoreTicketCommentRequest` accepts both `public` and `internal` and `TicketCommentController` only checks `view` on the ticket before persisting the supplied visibility.
- Ticket relational integrity is under-validated: ticket create/update only use plain `exists` checks for `branch_id`, `department_id`, `queue_id`, `category_id`, and `assigned_to`.
- The edit form intentionally limits assignee options to `UserRole::ItAgent` in `GetTicketFormOptions`, but backend validation allows any existing `users.id`, so crafted requests can assign tickets to non-agent accounts.
- Create flow also allows mismatched branch/department combinations because `department_id` is not constrained to the selected `branch_id`.
- Reproduced locally during audit:
  - `Gate::forUser($user)->allows('update', $ticket) === true` while `$user->can('manage approvals') === false`.
  - `validator(['body' => 'x', 'visibility' => 'internal'], (new StoreTicketCommentRequest)->rules())->passes() === true`.
  - `validator([... 'branch_id' => $branchA->id, 'department_id' => $departmentFromBranchB->id], (new StoreTicketRequest)->rules())->passes() === true`.
