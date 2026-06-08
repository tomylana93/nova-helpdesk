# PRD Notes: Internal IT Helpdesk

## Raw Requirements
- Build an internal IT helpdesk product for a single company with multiple branches.
- Include the current implementation baseline inside the PRD, not just the target vision.
- Treat the existing application as reusable technical foundation, but redesign roles and domain navigation for helpdesk use.
- Cover a broad operating scope:
  ticketing, approvals, SLA, knowledge base, asset linkage, reporting, and governance.
- Package the work as a phased roadmap instead of a single all-at-once release.
- Write the final PRD and technical design in English.

## Constraints
- Current repo is a Laravel 13 + Inertia Vue 3 application, not yet a helpdesk domain app.
- Existing domain data is limited to users, roles/permissions, settings, temporary uploads, sessions, jobs, and media.
- The present role model is not aligned with IT support operations and should not be treated as final product design.
- The target product role model is restricted to `super_admin`, `agent`, and `requester`.
- The plan should reuse stable technical building blocks already covered by tests where practical.

## Current Implementation Findings

### Product / UX Baseline
- Home redirects guests to login and authenticated users to dashboard.
- Dashboard page is still placeholder UI with no operational widgets.
- Main authenticated navigation only exposes:
  Dashboard, Settings, and Master Data.
- The app already has a reusable Inertia shell:
  sidebar, header, breadcrumbs, shared props, branding, theme, font, and locale.

### Auth / Identity
- Fortify is used for login, forgot password, reset password, and password confirmation views.
- Login rejects non-active users.
- Login can warn users when they are still using the configured default password.
- Authenticated routes are protected by `auth` + `active`.

### Admin / Platform Capabilities
- Admin settings exist for general, style, and password configuration.
- Master data currently covers user CRUD only.
- Permissions are already enforced for admin surfaces.
- Users support media-backed avatars and personal profile/security settings.

### Upload / Media Baseline
- Temporary upload pipeline exists with validation, ownership checks, rate limiting, and pruning.
- Upload flow already supports image-based profile and branding use cases.
- Current upload constraints are image-focused; future attachment support may need broader MIME handling.

### Data / Schema Baseline
- Core tables present:
  `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `settings`, `temporary_uploads`, `media`, `sessions`, `jobs`, `failed_jobs`, `cache`.
- No helpdesk tables exist yet for tickets, departments, branches, assets, approvals, SLA, KB, or activity logs.

## Inferred Patterns (from codebase)

| Edge Case | Source | Pattern Applied |
|-----------|--------|-----------------|
| Unauthorized admin access | `tests/Feature/Admin/AdminAuthorizationTest.php` | Return `403` for users without permission |
| Non-active user login | `app/Providers/FortifyServiceProvider.php` | Reject login with validation message |
| Upload abuse | `tests/Feature/TemporaryUploadTest.php` | Rate limiting plus active upload cap |
| Stale temporary files | `tests/Feature/TemporaryUploadTest.php` | Scheduled pruning command |
| Shared app state | `app/Http/Middleware/HandleInertiaRequests.php` | Use shared Inertia props for auth, branding, style, locale |
| CRUD persistence | `app/Actions/MasterData/Users/*` | Thin controllers with action classes and transactions |
| Admin data tables | `app/Tables/MasterData/UserTable.php` | Deferred payload, filterable/sortable server-driven table |

## Edge Cases

### Auto-handled (following current patterns)
- Authenticated platform routes should remain behind `auth` + active user enforcement.
- Restricted admin/helpdesk modules should use permission-gated route and policy checks.
- Large grid/list views should reuse deferred Inertia props and server-driven tables.
- Upload staging should remain ownership-aware and prunable.
- Locale, branding, and theme should remain shared at the middleware layer.

### Open Product Decisions Resolved for This PRD
- Product type: internal IT helpdesk.
- Organization model: single company, multi-branch.
- Delivery model: phased roadmap.
- Documentation language: English.
- Planning priorities: operational control, employee experience, and governance/audit are all first-class outcomes.

## Research Findings
- Existing stack is suitable for modular back-office workflows:
  auth, role checks, deferred tables, uploads, settings, and Inertia layouts are already in place.
- The largest product gap is domain modeling, not infrastructure.
- Current role enum suggests an earlier logistics/operations orientation, which reinforces the need for role redesign instead of preserving current business roles.
- The current application is a good platform base for internal helpdesk because it already has:
  multi-surface admin routing, reusable forms/tables, localization support, and audit-friendly server-side control points.

## Architecture Options

- Option A: Incremental Helpdesk Modules on Existing Platform
  - Pros: Reuses auth, settings, permissions, uploads, UI shell, and test conventions.
  - Cons: Requires careful domain migration to avoid leaking current role assumptions.

- Option B: Major Domain Reframe on Same Codebase
  - Pros: Cleaner domain structure and navigation from the start.
  - Cons: Higher rewrite scope and more churn across admin and shared surfaces.

- Option C: Fresh Helpdesk App with Minimal Reuse
  - Pros: Maximum design freedom.
  - Cons: Wastes working platform foundation and increases delivery cost.

**Selected**: Option A with targeted role redesign and phased domain expansion.

## Roadmap Shape
- Phase 1: Org foundation, ticketing core, queues, comments, attachments, base dashboard.
- Phase 2: SLA, approvals governed by super admins, knowledge base, richer notifications, operational reporting.
- Phase 3: Asset inventory linkage, governance depth, audit/reporting maturity, optimization.

## Key PRD Themes To Preserve
- Reuse stable platform capabilities already implemented.
- Explicitly separate current implementation from target-state requirements.
- Avoid pretending the app is already a helpdesk.
- Redesign roles, permissions, and navigation around helpdesk personas.
- Treat branch and department context as first-class data in the target model.
