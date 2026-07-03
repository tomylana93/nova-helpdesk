<laravel-boost-guidelines>
=== .ai/01-serena rules ===

# Serena MCP

The Serena MCP server provides semantic code tools (symbol search, references, precise editing) and a per-project instruction manual.

## Session Start — Mandatory First Actions

**Before any Bash, Read, grep, or file operation**, call these two in order:

1. `activate_project` — activate the project in Serena.
2. `initial_instructions` — read the project instruction manual.

Any tool call that is not `activate_project` before these two completes is a rule violation, even if the task looks trivial. There are no exceptions.

## Code Navigation — Serena First, grep Never

Use Serena's semantic tools for all code exploration and refactoring. Do **not** use `grep`, `Bash cat`, or `Read` on large files when a symbolic tool covers the same need.

| Need | Use |
|------|-----|
| Understand a class/file structure | `get_symbols_overview` or `find_symbol` with `depth` |
| Find all callers of a function | `find_referencing_symbols` |
| Find a specific symbol | `find_symbol` |
| Search by pattern | `search_for_pattern` |
| Edit a method body | `replace_symbol_body` |
| Insert code near a symbol | `insert_before_symbol` / `insert_after_symbol` |

**Concrete examples:**

```
❌ grep -rn "AppearanceTabs" resources/js
✅ mcp__serena__find_referencing_symbols("AppearanceTabs")

❌ cat -n FrontendLocaleExporter.php
✅ mcp__serena__find_symbol("FrontendLocaleExporter", depth=2)

❌ grep -rEln "lang:export|LangExport" app
✅ mcp__serena__search_for_pattern("lang:export", path="app")

❌ Read full file to find one method
✅ mcp__serena__find_symbol("methodName") → read only the body
```

**Exceptions where full `Read` is acceptable:**
- File is ≤ 100 lines.
- File is non-code (config, docs, `.env.example`).
- You genuinely need the entire file (e.g. reading a test to understand full flow).
- Never re-read a file already read in full in the same session.

## Fallback (Serena MCP not connected)

Say so explicitly in the evidence block, then fall back to ripgrep/Read — but still read only the symbols you need. Do not slurp whole files or rewrite files wholesale.

> **Model note:** Claude Code (Opus 4.8) and Codex CLI (GPT-5.5) have a `serena-hooks` SessionStart/PreToolUse reminder, but a reminder is **not a guarantee** — prior sessions ran Bash before `activate_project` even with hooks active. Follow these rules from the text, not from the hook. **Antigravity (Gemini 3.5 Flash) has no compatible session-start hook**, so it must comply purely from this text; its sessions warrant the closest developer review.

=== .ai/02-ai-workflow rules ===

# AI Agent Workflow (Developer-Directed, Two-Path)

This project balances rapid progress with the developer's understanding of the architecture. The developer acts as the director, reviewing designs, raising constraints, and interviewing the AI (via `/grill-me`) on the plan. The AI agent performs all implementation and testing, ensuring they follow the approved design and explaining decisions concisely. Three agents run this project, all first-class and sharing these rules via `AGENTS.md`/`CLAUDE.md`: Claude Code (Opus 4.8), Codex CLI (GPT-5.5), and Antigravity (Gemini 3.5 Flash). Match the task to the model: reserve the Deep (architectural) path for the stronger reasoning models (Opus 4.8, GPT-5.5). Keep the fast model (Gemini 3.5 Flash) on Routine, tightly-scoped tasks (single-file CRUD, copy/format tweaks); when a fast-model task turns out to be Deep, it must stop and escalate to the developer rather than improvise an architectural change.

## Step 0 — Triage every task

At the start of each task, output exactly one of these lines and then **stop**:

> `Step 0 — Deep: [one-line reason]. Awaiting your confirmation before proceeding.`
> `Step 0 — Routine: [one-line reason]. Awaiting your confirmation before proceeding.`

Do **not** run any tool, read any file, or write any code until the developer replies. The only exception is a single-word correction or typo fix where the scope is unambiguous — in that case state the triage inline and continue.

When in doubt, default to Deep.

- **Deep (architectural)** if any: introduces a new TMS domain concept (Shipment, Vehicle, Route, Order…) or a relation between them; is expensive to reverse (new migration/schema, module boundary, Inertia↔Vue pattern, auth/authorization); or the developer hasn't done it before in this stack.
- **Routine** if all: follows an existing repo pattern (similar CRUD, add column, shadcn component); reversible and local.

## Mandatory Tool Evidence

The compliance contract is **visible and in order**. After developer confirmation and Serena activation, post this block before touching any file:

```
---
Pre-flight:
- Classification: [Deep/Routine] — [reason]
- Serena: activated ✓ | initial_instructions read ✓  (or: unavailable — fallback: …)
- Memories: [mem:name] read  (or: not needed — [reason])
- Docs: search-docs used ✓  (or: not needed — [reason])
- Context7: used ✓  (or: not needed — [reason])
- Skills: [skill-name] read ✓  (or: not applicable — [reason])
- UI: @/components/ui checked ✓  (or: no UI involved)
---
```

No Bash, Read, Edit, or Write call may appear before this block (Serena `activate_project` and `initial_instructions` are the only allowed prior calls). Missing block = non-compliant session.

If a required MCP/tool is unavailable, stop and report the unavailable tool plus the documented fallback before proceeding.

## Agent Model Boundaries

- **Claude Code CLI (Opus 4.8):** allowed for Deep and Routine work.
- **Codex CLI (GPT-5.5):** allowed for Deep and Routine work.
- **Antigravity CLI (Gemini 3.5 Flash):** Routine only. If the task touches schema, auth/authorization, new dependencies, new TMS domain concepts, module boundaries, or new frontend/backend patterns, stop and ask the developer to reroute to Claude Code or Codex.

## Deep path (AI writes; developer directs)

1. `brainstorming` skill → explore intent and alternatives.
2. `writing-plans` skill → written plan. Developer reviews and uses `/grill-me` to stress-test the design.
3. Once the plan is approved, the AI executes: TDD test-first (AI writes the tests, then the implementation in one turn, verifying they pass/GREEN).
4. AI automatically captures each architectural decision as one short Serena memory ("why X, not Y").

## Routine path (AI writes; stays tested)

- Brief discussion, then AI writes test-first + full implementation, explaining the "why" concisely. Domain skills (laravel-best-practices, inertia-vue, wayfinder, fortify, pest, tailwind) apply automatically.

## Always

- TDD test-first is the default loop on both paths. Test-after is only acceptable when the developer explicitly labels the task as a "spike" or "throwaway" — the AI must never self-classify to skip tests.
- Code navigation via Serena symbolic tools; library docs via Context7 MCP; Vue UI via shadcn MCP.

## Finishing a task — gate before committing to `dev`

Run every step in order. Do not skip. Post the checklist in your reply before committing.

```
Finishing gate:
[ ] vendor/bin/pint --dirty --format agent          (always)
[ ] pnpm run lint   (eslint --fix)                  (always)
[ ] pnpm run format (prettier --write)              (always)
[ ] php artisan wayfinder:generate                  (if any route file, controller, or action changed)
[ ] composer ci:check → GREEN                       (always; inner loop uses --filter, this is the outer gate)
[ ] Memory written: [mem:name]  or  not needed: [reason]
[ ] Code review run  or  skipped: [reason]
[ ] Commit requested by developer: yes / no
```

**Wayfinder trigger — run `wayfinder:generate` if any of these changed:** `routes/*.php`, any controller file, any invokable action class.

**On ci:check failure:** re-run auto-fix for format/lint issues; for logic failures (test/phpstan/types) use `systematic-debugging` skill and re-run, but **stop and report** after ~2–3 unsuccessful rounds or if the fix touches a Deep-path decision — never loop blindly. See `mem:task_completion` for the exact command list.

**Memory upkeep threshold:** write or update a Serena memory only if the task produced something durable — a new TMS domain concept/decision, a changed convention, or changed tooling. Most routine tasks produce no memory. If memories were deleted/renamed, consider `serena memories check`. Follow `mem:memory_maintenance`.

**Commit rule:** commit directly to `dev` only when the developer explicitly asks. Never auto-push to `main`. CI runs on push. `dev` → `main` for releases only.

## Releases & versioning

- **SemVer `0.x`** (pre-1.0). release-please drives changelog + version bumps from conventional commits. Source of truth = git tags / GitHub Releases; `config/version.php` mirrors it (auto-bumped — never edit by hand).
- **Conventional commits matter** (they feed release-please): `feat:` → minor, `fix:`/`perf:` → patch, `feat!:`/`BREAKING CHANGE:` → minor while `0.x`. `chore:`/`ci:`/`test:`/`style:` are hidden from the changelog.
- **Pre-release on `dev`**: pushing to `dev` makes release-please maintain a release-candidate PR; merging it tags `X.Y.Z-rc.N` (GitHub pre-release).
- **Stable on `main`**: promote via a `dev` → `main` PR (required checks `ci` + `quality` must pass — `main` is protected). After merge, release-please opens a metadata-only release PR on `main`; merging it tags stable `X.Y.Z` + GitHub Release.
- `main` is protected (ruleset): PR required, no direct/force push. The release-please PR only bumps version/changelog, so merging it via admin bypass is acceptable; real code review happens on the `dev` → `main` PR.

=== .ai/03-mcp-routing rules ===

# AI Agent Workflow (Developer-Directed, Two-Path)

This project balances rapid progress with the developer's understanding of the architecture. The developer acts as the director, reviewing designs, raising constraints, and interviewing the AI (via `/grill-me`) on the plan. The AI agent performs all implementation and testing, ensuring they follow the approved design and explaining decisions concisely. Three agents run this project, all first-class and sharing these rules via `AGENTS.md`/`CLAUDE.md`: Claude Code (Opus 4.8), Codex CLI (GPT-5.5), and Antigravity (Gemini 3.5 Flash). Match the task to the model: reserve the Deep (architectural) path for the stronger reasoning models (Opus 4.8, GPT-5.5). Keep the fast model (Gemini 3.5 Flash) on Routine, tightly-scoped tasks (single-file CRUD, copy/format tweaks); when a fast-model task turns out to be Deep, it must stop and escalate to the developer rather than improvise an architectural change.

## Step 0 — Triage every task

At the start of each task, output exactly one of these lines and then **stop**:

> `Step 0 — Deep: [one-line reason]. Awaiting your confirmation before proceeding.`
> `Step 0 — Routine: [one-line reason]. Awaiting your confirmation before proceeding.`

Do **not** run any tool, read any file, or write any code until the developer replies. The only exception is a single-word correction or typo fix where the scope is unambiguous — in that case state the triage inline and continue.

When in doubt, default to Deep.

- **Deep (architectural)** if any: introduces a new TMS domain concept (Shipment, Vehicle, Route, Order…) or a relation between them; is expensive to reverse (new migration/schema, module boundary, Inertia↔Vue pattern, auth/authorization); or the developer hasn't done it before in this stack.
- **Routine** if all: follows an existing repo pattern (similar CRUD, add column, shadcn component); reversible and local.

## Mandatory Tool Evidence

The compliance contract is **visible and in order**. After developer confirmation and Serena activation, post this block before touching any file:

```
---
Pre-flight:
- Classification: [Deep/Routine] — [reason]
- Serena: activated ✓ | initial_instructions read ✓  (or: unavailable — fallback: …)
- Memories: [mem:name] read  (or: not needed — [reason])
- Docs: search-docs used ✓  (or: not needed — [reason])
- Context7: used ✓  (or: not needed — [reason])
- Skills: [skill-name] read ✓  (or: not applicable — [reason])
- UI: @/components/ui checked ✓  (or: no UI involved)
---
```

No Bash, Read, Edit, or Write call may appear before this block (Serena `activate_project` and `initial_instructions` are the only allowed prior calls). Missing block = non-compliant session.

If a required MCP/tool is unavailable, stop and report the unavailable tool plus the documented fallback before proceeding.

## Agent Model Boundaries

- **Claude Code CLI (Opus 4.8):** allowed for Deep and Routine work.
- **Codex CLI (GPT-5.5):** allowed for Deep and Routine work.
- **Antigravity CLI (Gemini 3.5 Flash):** Routine only. If the task touches schema, auth/authorization, new dependencies, new TMS domain concepts, module boundaries, or new frontend/backend patterns, stop and ask the developer to reroute to Claude Code or Codex.

## Deep path (AI writes; developer directs)

1. `brainstorming` skill → explore intent and alternatives.
2. `writing-plans` skill → written plan. Developer reviews and uses `/grill-me` to stress-test the design.
3. Once the plan is approved, the AI executes: TDD test-first (AI writes the tests, then the implementation in one turn, verifying they pass/GREEN).
4. AI automatically captures each architectural decision as one short Serena memory ("why X, not Y").

## Routine path (AI writes; stays tested)

- Brief discussion, then AI writes test-first + full implementation, explaining the "why" concisely. Domain skills (laravel-best-practices, inertia-vue, wayfinder, fortify, pest, tailwind) apply automatically.

## Always

- TDD test-first is the default loop on both paths. Test-after is only acceptable when the developer explicitly labels the task as a "spike" or "throwaway" — the AI must never self-classify to skip tests.
- Code navigation via Serena symbolic tools; library docs via Context7 MCP; Vue UI via shadcn MCP.

## Finishing a task — gate before committing to `dev`

Run every step in order. Do not skip. Post the checklist in your reply before committing.

```
Finishing gate:
[ ] vendor/bin/pint --dirty --format agent          (always)
[ ] pnpm run lint   (eslint --fix)                  (always)
[ ] pnpm run format (prettier --write)              (always)
[ ] php artisan wayfinder:generate                  (if any route file, controller, or action changed)
[ ] composer ci:check → GREEN                       (always; inner loop uses --filter, this is the outer gate)
[ ] Memory written: [mem:name]  or  not needed: [reason]
[ ] Code review run  or  skipped: [reason]
[ ] Commit requested by developer: yes / no
```

**Wayfinder trigger — run `wayfinder:generate` if any of these changed:** `routes/*.php`, any controller file, any invokable action class.

**On ci:check failure:** re-run auto-fix for format/lint issues; for logic failures (test/phpstan/types) use `systematic-debugging` skill and re-run, but **stop and report** after ~2–3 unsuccessful rounds or if the fix touches a Deep-path decision — never loop blindly. See `mem:task_completion` for the exact command list.

**Memory upkeep threshold:** write or update a Serena memory only if the task produced something durable — a new TMS domain concept/decision, a changed convention, or changed tooling. Most routine tasks produce no memory. If memories were deleted/renamed, consider `serena memories check`. Follow `mem:memory_maintenance`.

**Commit rule:** commit directly to `dev` only when the developer explicitly asks. Never auto-push to `main`. CI runs on push. `dev` → `main` for releases only.

## Releases & versioning

- **SemVer `0.x`** (pre-1.0). release-please drives changelog + version bumps from conventional commits. Source of truth = git tags / GitHub Releases; `config/version.php` mirrors it (auto-bumped — never edit by hand).
- **Conventional commits matter** (they feed release-please): `feat:` → minor, `fix:`/`perf:` → patch, `feat!:`/`BREAKING CHANGE:` → minor while `0.x`. `chore:`/`ci:`/`test:`/`style:` are hidden from the changelog.
- **Pre-release on `dev`**: pushing to `dev` makes release-please maintain a release-candidate PR; merging it tags `X.Y.Z-rc.N` (GitHub pre-release).
- **Stable on `main`**: promote via a `dev` → `main` PR (required checks `ci` + `quality` must pass — `main` is protected). After merge, release-please opens a metadata-only release PR on `main`; merging it tags stable `X.Y.Z` + GitHub Release.
- `main` is protected (ruleset): PR required, no direct/force push. The release-please PR only bumps version/changelog, so merging it via admin bypass is acceptable; real code review happens on the `dev` → `main` PR.

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
- @laravel/echo-vue (ECHO_VUE) - v2
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- laravel-echo (ECHO) - v2
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
