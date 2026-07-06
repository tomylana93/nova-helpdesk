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