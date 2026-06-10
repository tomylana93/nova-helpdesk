<laravel-boost-guidelines>
=== .ai/agent-context rules ===

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

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/wayfinder (WAYFINDER) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/vue3 (INERTIA_VUE) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `pnpm run build`, `pnpm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `pnpm run build` or ask the user to run `pnpm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== spatie/laravel-medialibrary rules ===

## Media Library

- `spatie/laravel-medialibrary` associates files with Eloquent models, with support for collections, conversions, and responsive images.
- Always activate the `medialibrary-development` skill when working with media uploads, conversions, collections, responsive images, or any code that uses the `HasMedia` interface or `InteractsWithMedia` trait.

</laravel-boost-guidelines>
