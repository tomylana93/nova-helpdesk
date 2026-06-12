# Technical Design: Internal IT Helpdesk

> Status: Draft
> Last updated: 2026-06-08
>
> **Refactor note (2026-06-11) — current delivered scope.** The 1-agent-model refactor
> (`docs/it-helpdesk-refactor-design.md`, `docs/it-helpdesk-refactor-steps.md`) supersedes parts of this
> technical design for what is shipped today: **Queue removed** (§4); SLA matches on **type + priority** only (§5);
> the **assigned IT agent** (not super admin) approves service requests (§6); **super_admin is read-only
> oversight** — never an assignment/approval/notification target, full access only via `Gate::before`
> (Personas, §6, §9); statuses are the 7-state machine (`Open, PendingApproval, InProgress,
> WaitingForRequester, Resolved, Closed, Reopened`). This document stays the draft technical design for
> the long-term vision; actual implementations have deprecated Queues and simplified routing and models.


## Overview
The target implementation evolves the current Laravel/Inertia admin platform into a modular internal IT helpdesk. The existing technical foundation should remain in place where it already solves platform concerns well: authentication, settings, shared layout, uploads, localization, permissions, and data-table behavior.

The primary work is domain expansion:
new helpdesk entities, helpdesk-specific roles/permissions, queue-based workflows, SLA logic, approvals, asset linkage, and reporting surfaces.

## Current Foundation To Reuse
- Fortify authentication and active-user login enforcement.
- Shared Inertia middleware props for auth, branding, style, locale, and shell state.
- Existing admin/settings scaffolding and permission approach.
- Action-class pattern for write operations.
- Temporary upload + media promotion flow.
- Deferred server-side table payload pattern.
- Pest-based feature-test conventions.

## Public Interface and Domain Additions

### New Core Entities
- `Branch`
- `Department`
- `Ticket`
- `TicketType`
- `TicketCategory`
- `TicketPriority`
- `TicketStatus`
- `TicketComment`
- `TicketActivity`
- `TicketAttachment`
- `TicketApproval`
- `SlaPolicy`
- `KnowledgeArticle`
- `Asset`
- `AssetAssignment`

### New User-Related Attributes
- `users.branch_id`
- `users.department_id`
- optional helpdesk profile attributes if needed for queue/team membership

### New Role Model
- `requester`
- `it_agent`
- `super_admin`

Current roles should be treated as legacy business roles and replaced or migrated to a helpdesk-oriented access model.

## Proposed Module Boundaries

### Platform
- Authentication
- Profile/security
- General/style/password settings
- Shared layout, navigation, locale, and branding

### Organization
- Branch management
- Department management
- User assignment to branch and department
- Team/queue membership administration

### Helpdesk Operations
- Ticket intake
- Ticket queues and worklists
- Ticket detail timeline
- Assignment and triage
- Comments, notes, attachments
- Resolution and closure

### Service Governance
- SLA policies
- Approval workflow definitions
- Escalation visibility
- Audit reporting

### Knowledge and Assets
- Knowledge article CRUD and search
- Asset inventory CRUD
- Asset ownership and location tracking
- Ticket-to-asset history

## Data Model Direction

### Organization
- `branches`
  - id, code, name, status
- `departments`
  - id, branch_id nullable, code, name, status

### Tickets
- `tickets`
  - id/uuid
  - ticket_number
  - type
  - branch_id
  - department_id
  - requester_id
  - assigned_to nullable
  - queue_id nullable
  - category_id nullable
  - priority
  - status
  - subject
  - description
  - submitted_at
  - first_response_due_at nullable
  - resolution_due_at nullable
  - resolved_at nullable
  - closed_at nullable

- `ticket_comments`
  - ticket_id
  - user_id
  - visibility (`public` or `internal`)
  - body

- `ticket_activities`
  - ticket_id
  - actor_id nullable
  - event
  - metadata json
  - occurred_at

- `ticket_attachments`
  - ticket_id
  - media reference or promoted upload reference

### Approval and SLA
- `ticket_approvals`
  - ticket_id
  - reviewer_id
  - status
  - decided_at nullable
  - decision_note nullable

- `sla_policies`
  - ticket_type
  - priority
  - queue_id nullable
  - first_response_target_minutes
  - resolution_target_minutes
  - escalation_rule metadata

### Knowledge Base
- `knowledge_articles`
  - title
  - slug
  - summary
  - body
  - category
  - status
  - published_at nullable

### Assets
- `assets`
  - asset_tag
  - serial_number nullable
  - branch_id
  - assigned_user_id nullable
  - department_id nullable
  - category
  - vendor/model metadata
  - status
  - purchase/support fields as needed later

## Route and UI Direction

### Keep
- Existing auth routes and settings routes.
- Existing admin area as the platform administration boundary.

### Add
- Helpdesk routes for requester and agent workflows.
- Admin/master-data routes for branches, departments, queues, categories, priorities, KB, assets, and SLA policies.
- Dashboard routes for operational, governance, and requester views as needed.

### Navigation Direction
- Replace current business-role-driven menu assumptions.
- Introduce top-level navigation centered on:
  Dashboard, Tickets, Requests, Knowledge Base, Assets, Reports, Administration.
- Visibility should be permission-driven using the same shared-auth-abilities pattern already present.

## Workflow Design

### Ticket Lifecycle
Suggested baseline states:
- `new`
- `triaged`
- `waiting_for_approval`
- `in_progress`
- `waiting_for_requester`
- `resolved`
- `closed`
- `reopened`

### Approval Lifecycle
- `pending`
- `approved`
- `rejected`
- `cancelled`

### SLA Behavior
- SLA targets assigned on ticket creation or reclassification.
- Breach state derived from due timestamps and lifecycle state.
- Escalation should initially focus on visibility and notifications before heavy automation.

## Migration Strategy

### Preserve
- Users, authentication, password flows, active-user logic.
- Settings infrastructure and current setting records.
- Media and temporary upload infrastructure.
- Shared layouts and component library.

### Add
- New helpdesk domain tables through migrations.
- New policies, requests, actions, controllers, and Inertia pages for helpdesk modules.
- New permissions and role seeding/sync logic.

### Redesign
- Replace current role enum usage in product-facing flows.
- Expand shared auth ability payload to include helpdesk capabilities.
- Rework dashboard and sidebar navigation around helpdesk modules.

### Attachment Handling
- Keep the temporary upload staging pattern.
- Expand validation and promotion rules to support ticket attachment file types safely.
- Continue enforcing ownership checks and pruning for staged uploads.

## Phased Implementation Order

### Phase 1
- Organization master data.
- Role and permission redesign.
- Ticket core schema and CRUD.
- Ticket list/detail surfaces.
- Comments, internal notes, attachments.
- Basic dashboards and requester tracking.

### Phase 2
- SLA policies and due calculations.
- Approval workflows.
- Knowledge base module.
- Notification and escalation visibility.
- Expanded reporting.

### Phase 3
- Asset inventory and ticket linkage.
- Governance-depth reporting and audits.
- Workflow optimization and automation refinements.

## Testing Plan
- Feature tests for every new route surface and permission boundary.
- Feature tests for ticket creation, assignment, transitions, approvals, and closure.
- Feature tests for SLA calculation and breach visibility.
- Feature tests for attachment constraints and ownership behavior.
- Feature tests for requester, agent, and super admin access differences.
- Feature tests for asset-linked and knowledge-linked ticket flows.

## Assumptions
- Multi-tenancy is out of scope.
- Existing admin settings remain relevant as platform controls.
- Helpdesk module growth should follow existing Laravel conventions:
  thin controllers, action classes, form requests, policy checks, and Pest feature tests.
- Generated route helpers and current Inertia conventions should continue to be used once implementation starts.
