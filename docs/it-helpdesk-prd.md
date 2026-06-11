# PRD: Internal IT Helpdesk

> Status: Draft
> Last updated: 2026-06-08
>
> **Refactor note (2026-06-11) — current delivered scope.** The 1-agent-model refactor
> (`docs/it-helpdesk-refactor-design.md`, `docs/it-helpdesk-refactor-steps.md`) supersedes parts of this
> PRD for what is shipped today: **Queue removed** (§4); SLA matches on **type + priority** only (§5);
> the **assigned IT agent** (not super admin) approves service requests (§6); **super_admin is read-only
> oversight** — never an assignment/approval/notification target, full access only via `Gate::before`
> (Personas, §6, §9); statuses are the 7-state machine (`Open, PendingApproval, InProgress,
> WaitingForRequester, Resolved, Closed, Reopened`). This PRD stays the long-term vision; affected
> sections below carry inline refactor notes.

## Table of Contents
- [Problem Statement](#problem-statement)
- [Current Implementation](#current-implementation)
- [Goals and Non-Goals](#goals-and-non-goals)
- [Success Criteria](#success-criteria)
- [Scope and Roadmap](#scope-and-roadmap)
- [Personas and Roles](#personas-and-roles)
- [Product Requirements](#product-requirements)
- [User Flows](#user-flows)
- [Current-to-Target Gap Summary](#current-to-target-gap-summary)
- [Implementation Plan](#implementation-plan)

## Problem Statement
The current application is a solid internal admin platform, but it does not yet solve the end-to-end needs of an internal IT helpdesk. It lacks ticket management, support workflows, approvals, SLA tracking, asset relationships, knowledge capture, and helpdesk-specific reporting.

The objective is to evolve this application into an internal IT helpdesk for a single company operating across multiple branches. The new product must support daily support operations, improve the employee support experience, and provide governance and auditability for IT service delivery.

## Current Implementation

### What Exists Today
- Fortify-based authentication with active-user enforcement.
- User profile and security settings.
- Admin settings for general branding, style, locale, and password defaults.
- User master data management with role assignment, status, filtering, and sorting.
- Shared app shell built with Inertia Vue:
  dashboard route, sidebar layout, breadcrumbs, locale/style/branding shared props.
- Temporary upload pipeline with validation, ownership checks, pruning, and image-focused media handling.
- Permission-protected admin surfaces and existing automated Pest coverage.

### What Does Not Exist Yet
- Ticket, request, incident, problem, change, or approval records.
- Branch, department, or requester organizational structure.
- IT-specific roles and permissions.
- SLA policies, due-time calculation, or escalation workflows.
- Helpdesk dashboards and support metrics.
- Knowledge base article management.
- Asset inventory and asset-to-ticket relationships.
- Support activity history, watcher/follower model, or notification center.

### Product Positioning of Current Implementation
The current codebase should be treated as platform foundation, not as finished product direction. Authentication, settings, uploads, shared layout, and table patterns are worth preserving. Current business roles and menu structure should be redesigned to match IT helpdesk operations.

## Goals and Non-Goals

### Goals
- Provide a central IT support workspace for employees across branches.
- Enable employees to submit and track incidents and service requests.
- Enable IT teams to triage, assign, prioritize, resolve, and audit work.
- Support approval-based requests where fulfillment requires super admin or policy approval.
- Introduce SLA management, visibility, and escalation.
- Build organizational context around branch, department, requester, and assigned team.
- Connect tickets to assets and knowledge so support becomes more efficient over time.
- Provide dashboards and reports for operational control, employee experience, and governance.

### Non-Goals
- Public customer support or multi-tenant external client support.
- Full ITIL coverage in the first release beyond what is explicitly phased here.
- CMDB-grade configuration management in the first delivery phase.
- Procurement, finance, or general enterprise workflow replacement outside IT support needs.

## Success Criteria

### Operational Control
- 100% of IT support work is represented as structured tickets or approved service requests.
- Team leads can view ticket status, owner, priority, SLA state, and branch context in one place.
- Escalation rules can identify overdue and at-risk work without manual spreadsheet tracking.

### Employee Experience
- Employees can create, update, and track requests without contacting IT through informal channels.
- Common service needs can be fulfilled through guided request forms and knowledge articles.
- Employees receive clear status visibility, response expectations, and closure history.

### Governance and Audit
- Approval-required requests retain decision history and timestamps.
- Ticket lifecycle changes, assignments, and closures are audit-trailed.
- IT management can review workload, SLA performance, and branch-level service patterns.

## Scope and Roadmap

### Phase 1: Foundation and Ticketing Core
- Branch and department master data.
- Helpdesk role and permission redesign.
- Ticket intake for incidents and service requests.
- Ticket categories, priorities, statuses, queues, assignees, comments, and attachments.
- Basic operational dashboard and list/report views.
- Requester-facing ticket submission and status tracking.

### Phase 2: Service Operations Expansion
- SLA policy definition by ticket type, priority, and queue.
- Due-date calculation, breach tracking, and escalation.
- Approval workflows for selected service requests.
- Knowledge base article management and article-to-ticket linkage.
- Notification strategy for requesters, agents, and super admins.
- Expanded team and branch performance reporting.

### Phase 3: Governance and Asset Maturity
- Asset inventory and asset ownership/location data.
- Asset-to-ticket linking and support history per asset.
- Deeper audit reporting and operational analytics.
- Workflow refinement, automation hooks, and optimization.

## Personas and Roles

### Personas
- Employee Requester: submits incidents and service requests, tracks progress, responds to questions.
- IT Agent: triages, works, updates, assigns, and resolves tickets.
- Super Admin: governs platform settings, approval-controlled flows, reporting access, and system-wide controls.

> **Refactor note:** In current scope the super admin is **read-only oversight** of tickets — it can view
> the ticket index/detail but is never an assignment/approval/notification target and the UI surfaces no
> lifecycle controls. Service-request approval is owned by the **assigned IT agent**.

### Target Roles
- `requester`
- `it_agent`
- `super_admin`

The target role model replaces the current non-IT business-role orientation. Existing auth and permission mechanics can be reused, but role names, grants, and navigation should be redesigned around these personas.

## Product Requirements

### 1. Organization and Identity
- The system must support a single company with multiple branches.
- Users must belong to a branch and optionally a department.
- Helpdesk views must be able to filter and report by branch and department.
- Existing active/inactive user status behavior should remain in place.

### 2. Ticket Intake
- Employees must be able to create incidents and service requests.
- Ticket forms must capture requester, branch, department, category, priority, subject, description, and attachments.
- Service request types may include predefined forms and approval requirements.
- The system must generate unique ticket identifiers.

### 3. Ticket Operations
- Agents must be able to triage, assign, change status, reprioritize, comment, and resolve tickets.
- Team leads must be able to rebalance workloads and oversee queue health.
- Ticket timelines must preserve comments, state changes, assignment changes, and approvals.
- Tickets must support internal notes and requester-visible updates as separate concepts.

### 4. Queues, Categories, and Priority

> **Refactor note:** Queues are **removed** in current scope (1-agent model): no queue model, nav, form,
> or SLA dimension. Tickets auto-assign to the single active agent; org context is branch + department
> (derived from the requester profile). Categories and priority remain.

- Tickets must be grouped by queue or functional team.
- Categories and subcategories must classify work.
- Priority should drive urgency, workload visibility, and SLA behavior.
- Managers must be able to analyze workload by queue, category, priority, branch, and assignee.

### 5. SLA and Escalation

> **Refactor note:** SLA matching is **type + priority** only (queue dimension dropped). Escalation
> notifies the **assigned agent** (persisted notification) — no role broadcast fan-out.

- SLA policies must be configurable by ticket type and priority.
- The system must calculate response and resolution targets.
- At-risk and breached tickets must be identifiable in work queues and dashboards.
- Escalation must support notification or state-based escalation paths.

### 6. Approval Workflow

> **Refactor note:** In current scope the **assigned IT agent** (not the super admin) approves/rejects
> service requests from the `PendingApproval` state. Super admin is excluded from the approval flow.

- Selected request types must require approval before fulfillment.
- Super admins must be able to approve, reject, or request clarification for approval-controlled requests.
- Approval decisions must be timestamped and auditable.
- Fulfillment must not proceed until required approvals are satisfied.

### 7. Knowledge Base
- IT teams must be able to publish and maintain internal support articles.
- Articles should be searchable and categorizable.
- Agents should be able to link articles to tickets.
- Employees should be able to consult articles during ticket submission when relevant.

### 8. Asset Linkage
- IT must be able to register assets and relate them to users, branches, and tickets.
- Ticket history should show associated asset context when relevant.
- Asset linkage should support incident diagnosis and service history review.

### 9. Notifications and Visibility

> **Refactor note:** Current scope targets notifications to the **requester** and the **assigned agent**
> only, on their personal channels; **super_admin is never a notification target** and the shared
> `helpdesk.agents` broadcast has been removed.

- Requesters should receive updates for submission, assignment visibility where appropriate, approval outcome, and closure.
- Agents should receive updates for assignment, mentions, approvals pending, and SLA risk.
- Super admins should have visibility into backlog, overdue work, branch-level trends, and approval queues.

### 10. Reporting and Audit
- Dashboards must show open work, aging, SLA status, assignee workload, and branch-level demand.
- Reports must support operational, employee-experience, and governance use cases.
- Critical events must be auditable:
  creation, update, assignment, approval, escalation, resolution, reopen, and closure.

## User Flows

### Incident Submission
1. Employee selects incident form.
2. Employee enters issue details, branch context, and optional asset/attachment.
3. System creates ticket, assigns identifier, and sets initial queue/status.
4. Requester receives confirmation and can track progress.

### Service Request with Approval
1. Employee selects service request type.
2. System captures structured request data.
3. Request enters approval state if required.
4. Approver decides.
5. Approved request moves to fulfillment queue; rejected request closes with reason.

### Ticket Triage and Resolution
1. Agent reviews unassigned queue.
2. Ticket is categorized, prioritized, and assigned.
3. Worklog, comments, and internal notes accumulate on the timeline.
4. Resolution is recorded.
5. Requester is notified and ticket can be closed or reopened.

### SLA Escalation
1. Ticket receives due targets from SLA policy.
2. Queue and dashboard surfaces expose at-risk work.
3. Escalation triggers notification or super admin intervention.
4. Final reports reflect compliance or breach.

### Knowledge and Asset-Assisted Support
1. Requester or agent finds a relevant knowledge article.
2. Agent links asset and article to the ticket when applicable.
3. Resolution history builds reusable context for future requests.

## Current-to-Target Gap Summary

| Area | Current State | Target State |
|------|---------------|--------------|
| Auth | Implemented | Reuse |
| User management | Implemented | Extend with branch/department/helpdesk roles |
| Settings | Implemented | Reuse |
| Uploads/media | Implemented for image-focused flows | Extend for ticket attachments |
| Dashboard | Placeholder only | Operational helpdesk dashboard |
| Permissions | Implemented for admin | Redesign for helpdesk personas |
| Ticketing | Not implemented | Net-new |
| SLA | Not implemented | Net-new |
| Approval flows | Not implemented | Net-new |
| Knowledge base | Not implemented | Net-new |
| Assets | Not implemented | Net-new |
| Audit/reporting | Minimal | Expanded helpdesk analytics and activity history |

## Implementation Plan
- Preserve the platform foundation:
  auth, active-user enforcement, settings, shared app shell, media/upload pipeline, and server-driven table patterns.
- Introduce organization master data before or alongside ticketing so branch-aware operations are first-class.
- Redesign roles and permissions before broadening navigation.
- Deliver helpdesk capabilities in phases to keep the transformation executable and testable.

See `docs/it-helpdesk-tech.md` for the target technical design.
