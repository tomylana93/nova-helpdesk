# Changelog

All notable changes to this project will be documented in this file.

## [0.13.2] - 2026-06-11

### Other Changes
- Merge pull request #23 from tomylana93/dev
- fix deployment migration issues
- Merge pull request #22 from tomylana93/dev

## [0.13.1] - 2026-06-11

### Fixed
- fix(dashboard): align SLA gauge card header with other metric cards
- fix(dashboard): constrain donut size so SLA gauge and breakdown render correctly

### Other Changes
- Merge pull request #21 from tomylana93/dev

## [0.13.0] - 2026-06-11

### Added
- feat(dashboard): rebuild dashboard page with period zones
- feat(dashboard): add TrendChart and PeriodControl components
- feat(dashboard): add BreakdownDonut component
- feat(dashboard): add MetricCard and SlaGauge components
- feat(dashboard): add DeltaBadge component
- feat(dashboard): add useDashboard composable
- feat(dashboard): add dashboard prop types
- feat(dashboard): add dashboard i18n keys (en/id)
- feat(dashboard): validate period query and render new payload
- feat(dashboard): add GetDashboardData orchestrator
- feat(dashboard): add AdminDashboard builder
- feat(dashboard): add AgentDashboard builder
- feat(dashboard): add RequesterDashboard builder
- feat(dashboard): add driver-aware TicketMetricQueries
- feat(dashboard): add Delta calculator
- feat(dashboard): add DashboardPeriod value object
- feat(dashboard): add TicketStatus::activeCases helper

### Other Changes
- test(dashboard): use Date facade for test-now per rector
- Merge pull request #20 from tomylana93/dev

## [0.12.2] - 2026-06-11

### Fixed
- fix(settings): update site name and description to reflect correct branding

## [0.12.1] - 2026-06-11

### Other Changes
- Merge pull request #19 from tomylana93/dev
- style(frontend): fix prettier formatting on Dashboard.vue

## [0.12.0] - 2026-06-11

### Added
- feat(helpdesk): add ticket number sequencing and first response tracking

### Other Changes
- Merge pull request #18 from tomylana93/dev

## [0.11.1] - 2026-06-11

### Other Changes
- docs(memory): add git publish handoff workflow
- Merge pull request #17 from tomylana93/dev

## [0.11.0] - 2026-06-11

### Added
- feat(tickets): show remaining sla in ticket table

## [0.10.2] - 2026-06-11

### Fixed
- fix(tickets): auto-populate submitted_at and fix SLA due dates crash

### Other Changes
- Merge branch 'fix/ticket-submitted-at' into dev

## [0.10.1] - 2026-06-11

### Fixed
- fix(dashboard): resolve HelpCircle warning and missing SLA compliance props crash

### Other Changes
- Merge branch 'fix/dashboard-errors' into dev

## [0.10.0] - 2026-06-11

### Added
- feat(master-data): add SLA policy import support

### Other Changes
- Merge branch 'feat/import-sla-policies' into dev
- Merge pull request #16 from tomylana93/dev

## [0.9.0] - 2026-06-11

### Added
- feat(dashboard): implement role-based dashboard with charts, metrics, and SLA compliance radial gauge

### Changed & Refactored
- refactor(dashboard): extract query and metrics compilation to GetDashboardMetrics Action

### Other Changes
- Merge pull request #15 from tomylana93/dev
- Merge pull request #14 from tomylana93/dev
- docs: add ticket lifecycle flow, env documentation, CONTRIBUTING.md, and SECURITY.md
- Merge pull request #13 from tomylana93/dev
- docs: remove Laravel Pint badge and add MIT License
- Merge pull request #12 from tomylana93/dev
- docs: use slightly rounded style for all badges
- Merge pull request #11 from tomylana93/dev
- docs: make GitHub Actions status badge style uniform
- Merge pull request #10 from tomylana93/dev
- docs: add dark-mode responsive logo and badges to README
- Merge pull request #9 from tomylana93/dev

## [0.8.0] - 2026-06-11

### Added
- feat(release): implement automated changelog and add README
- feat(release): add auto mode for bump version and conventions memory
- feat: add semantic versioning and app:bump-version command
- feat: implement notification system for unassigned ticket creation and enhance channel authorization
- feat: enhance ticket creation and management with category and department handling
- feat: add notifications system with real-time updates
- feat(audit): add helpdesk audit documentation highlighting approval flow bugs and validation issues
- feat: streamline SLA policy options and enhance datatable translations for priorities
- feat(lang): add Indonesian translations for settings section
- feat: add ticket management features including approval, comments, and ticket details view
- feat(master-data): add import master data command and ignore csv files

### Fixed
- fix: update category placeholder text to remove optional indication
- fix: resolve audit findings and enhance ticket approval process with validations
- fix: add 'opencode' to agents list in boost.json and format .mcp.json for consistency
- fix: update shadcnVue command from npx to pnpm for consistency
- fix: update project name from "nova-core" to "nova-helpdesk"

### Changed & Refactored
- refactor: make app name dynamic and SSR-safe fallback to Nova Helpdesk
- refactor: complete IT helpdesk flow refactor (phases 1-8)
- refactor: remove obsolete mcp.json configuration file
- refactor(tests): remove ImportMasterDataCommandTest as it is no longer needed

### Other Changes
- Merge pull request #8 from tomylana93/dev
- Merge pull request #7 from tomylana93/dev
- Merge pull request #6 from tomylana93/dev
- Merge pull request #5 from tomylana93/dev
- Merge pull request #4 from tomylana93/refactor/helpdesk-flow
- Merge pull request #3 from tomylana93/dev
- docs: update backend documentation for admin, core, and helpdesk sections
- Refactor code structure for improved readability and maintainability
- Merge pull request #2 from tomylana93/dev
- Add 'all_parents' label to English and Indonesian datatable language files
- Fix button layout alignment on master-data and settings index pages
- Implement Queue and Ticket Category CRUD master data
- Add serena and shadcn-vue configurations to mcp_config.json
- Add mcp_config.json for MCP server configuration and update boost.json to include 'antigravity' agent
- Update composer.lock to reflect dependency version changes for Laravel and Symfony packages
- Add settings.json for serena-hooks configuration
- Add PRD, task plan, and technical design documents for internal IT helpdesk project
- Add new skills for Pest testing, Tailwind CSS development, and Wayfinder integration; update guidelines and configuration files
- Refactor code structure for improved readability and maintainability
- remove UserSeederTest as UserSeeder is no longer used
- remove UserSeeder and update DatabaseSeeder to prevent seeding in production
- initial commit

