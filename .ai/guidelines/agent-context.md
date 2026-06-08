# Agent Context Orchestration

These rules optimize how AI agents should use this project's MCP tools, Serena memories, and domain skills. They augment the default Laravel Boost guidelines.

## Startup Sequence

- Activate the Serena project for this repository before code exploration when Serena tools are available.
- Read Serena's initial instructions once per session before coding tasks.
- Use `mem:core` as the Serena memory entry point; follow its references only for domains touched by the task.
- Call Boost `application-info` early in a new session when package/runtime versions matter.
- Use Boost `search-docs` before code changes that involve Laravel ecosystem packages. Scope by package when the relevant package is known.

## Tool Routing

- Use Boost `search-docs` for Laravel, Fortify, Inertia, Pest, Tailwind, Wayfinder, Vite, and related ecosystem syntax or behavior.
- Use Boost `database-schema` before creating or changing migrations, models, relationships, validation tied to persisted fields, or queries.
- Use Boost `database-query` for read-only database inspection; do not use Tinker for simple SELECT-style checks.
- Use Boost `browser-logs`, `last-error`, and `read-log-entries` before guessing at frontend/backend runtime failures.
- Use Boost `get-absolute-url` before sharing a local application URL.
- Use Serena memories for stable project conventions and architecture; avoid rediscovering facts already stored there.
- Use Serena symbolic tools for code navigation when editing PHP, Vue, TypeScript, or SCSS; avoid reading entire source files unless necessary.
- Use shell commands for Artisan, package scripts, git status/diff, and fast text/file search with `rg`.

## Mandatory Skill Usage

- Before substantial code, test, frontend, auth, UI, docs, or workflow work, inspect the available project skills and activate every skill whose `description` matches the task.
- Treat skill `description` frontmatter as the source of truth for activation triggers and exclusions.
- Do not rely on `AGENTS.md` as a complete skill inventory; project skills may be added, removed, or changed independently.
- If multiple skills apply, use the minimal set that covers the task and state which ones are being used.
- If an obvious relevant skill exists but cannot be used, state the reason briefly and continue with the closest fallback.
- Common project-critical domains that usually have skills include Laravel backend, Fortify/auth, Inertia Vue, Wayfinder route integration, Pest tests, Tailwind UI/layout, shadcn-vue components, and Playwright/browser workflows. This list is a reminder, not the authoritative inventory.

## Skill Inventory Maintenance

- Do not paste full skill contents into `AGENTS.md`; skills remain source-of-truth in their own `SKILL.md` files.
- When a new project skill is added under `.agents/skills/**/SKILL.md`, make sure its frontmatter `description` has explicit activation triggers and exclusions.
- Do not maintain a complete skill list in `AGENTS.md`; only add workflow-level reminders for broad domains if they materially improve activation.
- If a new skill changes the default workflow for implementation, refactor, verification, docs, or memory management, update `.ai/guidelines/agent-context.md` and regenerate `AGENTS.md` with `php artisan boost:install --guidelines --no-interaction`.
- If a new skill captures a stable project convention that future agents should discover through Serena, update the relevant Serena memory and link it from `mem:core` or another parent memory.
- After adding or changing project skills, check `git status --short` and verify the expected `SKILL.md`, guideline, and memory changes are included.

## Working Pattern

- Load the minimal relevant skill files and documentation for the task; do not bulk-read every skill.
- Check sibling files and existing components before introducing new structures.
- Prefer generated Wayfinder imports from `@/actions` and `@/routes`; do not hand-edit generated Wayfinder files.
- Prefer feature tests for behavior changes and run the smallest relevant verification command first.
- After PHP edits, run `vendor/bin/pint --dirty --format agent`.
- After frontend edits, run the relevant subset of lint, format, type, and build checks.
- After broad implementations, refactors, or cross-domain changes, prefer `composer run ci:check` before handoff when time permits; otherwise report the focused checks that were run and the skipped broader CI check.
- If an MCP tool or skill is unavailable in the current client, state the missing capability briefly and use the closest local fallback.

## Implementation And Refactor Flow

- Intake: restate the requested outcome internally, inspect `git status --short`, and identify whether the task is backend, frontend, auth, route-integration, UI, test, or cross-domain.
- Context: load `mem:core`, then only the relevant domain memories and skills; use Boost docs before Laravel ecosystem changes.
- Discovery: inspect existing sibling files, routes, components, tests, and generated Wayfinder boundaries before choosing an approach.
- Safety: before changing public methods, routes, controller actions, request payloads, component props, or generated types, search references and update all call sites or preserve backward compatibility.
- Planning: for non-trivial work, decide the smallest coherent change set and the minimum verification commands before editing.
- Implementation: prefer Laravel generators for new backend artifacts, preserve existing directory conventions, avoid new dependencies without approval, and keep generated files generated.
- Refactor: preserve behavior unless the user explicitly requests a behavior change; make incremental edits instead of unrelated rewrites.
- Verification: run focused tests/checks first, run formatters after edits, regenerate/check Wayfinder after route/controller changes, and use `composer run ci:check` for broad or risky changes when time permits.
- Handoff: report changed files, verification run, and any skipped broader checks or residual risks.

## Thin Controllers And Actions

- Keep controllers thin: route model binding, authorization, validated input handoff, action invocation, and HTTP/Inertia response composition only.
- Move business logic out of controller methods when it includes multi-step writes, transactions, branching workflows, external service calls, event dispatching, file/storage operations, complex Eloquent mutations, or logic reused by more than one entry point.
- For `store`, `update`, and `destroy`, prefer action classes named by intent: `Create{Model}`, `Update{Model}`, and `Delete{Model}` or domain-specific verbs when clearer.
- Put general application actions under `app/Actions/{Domain}` with namespace `App\Actions\{Domain}`. Keep Fortify-specific actions under `app/Actions/Fortify`.
- Create action classes with `php artisan make:class Actions/{Domain}/{ActionName} --no-interaction`.
- Prefer a public `handle(...)` method for application action classes. Use `__invoke()` only when the surrounding codebase already uses invokable actions for that domain or a framework contract requires a specific method.
- Inject dependencies through the action constructor. Pass request-derived data, route models, and authenticated users as explicit `handle()` arguments.
- Use Form Requests for validation and authorization where appropriate; pass `$request->validated()` or a documented subset into the action instead of the whole request unless the action explicitly needs request services.
- Keep transactions inside the action when the action owns the write consistency boundary.
- Return domain results from actions, such as models, DTO-like arrays, or value objects. Let controllers decide redirects, Inertia responses, status codes, and flash messages.
- Avoid moving simple one-line persistence into an action unless it improves reuse, testability, or keeps a controller method consistently thin with nearby methods.
- Test behavior through feature tests first. Add focused unit tests for actions only when the action has complex branching or is reused independently from HTTP.

## Serena Memory Management

- Use `mem:core` as the entry point and only follow memory references relevant to the current task.
- Read `mem:memory_maintenance` before adding, renaming, or restructuring Serena memories.
- Add or update memories only for stable, non-obvious project facts that would prevent future rediscovery.
- Do not store task-local notes, temporary debugging observations, volatile line-level details, generic framework knowledge, secrets, credentials, or user-private data.
- Keep memories dense and operational: terse bullets, durable invariants, paths or commands only when they are stable.
- Prefer updating an existing domain memory over creating a new one unless the topic needs its own reusable entry point.
- When adding a new memory, link it from an appropriate parent memory using the `mem:` prefix so future agents can discover it.
- If implementation changes invalidate an existing memory, update the memory in the same task before handoff.

## Project Docs And Task Tracking

- Do not create documentation files unless the user explicitly requests them, but if `docs/PRD.md`, `docs/TASKS.md`, or similar project docs already exist, read the relevant sections during intake for feature, refactor, or planning work.
- Treat user instructions in the current conversation as higher priority than stale docs. When docs conflict with the user's request, follow the user and update the affected doc if the user asked for doc maintenance or if the doc is part of the active task workflow.
- Treat `docs/PRD.md` as product intent and acceptance criteria, not as implementation truth. If implementation or user direction makes PRD content stale, mark or update the smallest affected section rather than rewriting unrelated content.
- Treat `docs/TASKS.md` as an execution checklist. Only check off a task after the implementation is complete, relevant verification has passed, and the result is stable enough to hand off.
- Do not check off tasks for partial work, unverified behavior, skipped blockers, or changes that still require user confirmation.
- When a checklist item is blocked or superseded, annotate it briefly instead of marking it complete.
- Keep task doc edits minimal and traceable: update only the relevant checklist lines, status notes, or acceptance criteria touched by the work.
- If docs mention behavior that no longer matches code, verify against code/tests before changing either side; do not assume the docs are current.
- Do not duplicate PRD/TASKS details into Serena memories unless they have become stable, non-obvious project conventions that future agents need outside the task context.
