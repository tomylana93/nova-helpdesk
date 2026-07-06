# Release And Deploy Workflow Handoff Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Clean up the release/deploy workflow so Release Please remains the source of truth for versions, production deploys are still built and run locally, and stale/redundant release paths are removed.

**Architecture:** Keep GitHub responsible for PR review, CI, Release Please RC/stable metadata, changelog, and tags. Keep physical production deployment local via `deploy.sh`, but make it guarded, reproducible, and free of hardcoded secrets. Production deploys must run from an exact stable tag or the matching stable `main` commit, never from dirty local state or `dev`.

**Tech Stack:** Laravel 13, PHP 8.5, Inertia Vue 3, pnpm 11, Vite, Release Please v5, GitHub Actions, local shell deploy via rsync/ssh to VPS.

## Global Constraints

- Do not move production deploy/build to GitHub Actions; the developer explicitly wants build and deploy to run from the local machine.
- Release Please is the only supported version bump path.
- Git tags / GitHub Releases are the release source of truth; `config/version.php` mirrors the current application version.
- `version.json`, `composer version:bump`, and `php artisan app:bump-version` are retired and must not be used.
- Production deploy must target stable releases only, not RC tags.
- Hardcoded production credentials must not live in tracked scripts.
- `deploy.sh` should be safe to run repeatedly, but must reject unsafe deploy state.
- No direct push to `main`; stable releases flow through `dev` to `main` PRs.
- Do not edit `config/version.php` by hand except through Release Please metadata changes.
- Follow project Step 0 / Serena / pre-flight rules in `AGENTS.md` before implementation.

---

## Current Workflow Summary

Development flow:

```text
feature/fix branch
  -> PR to dev
  -> GitHub CI quality + tests
  -> merge to dev
  -> Release Please maintains RC PR/tag on dev
```

Stable release flow:

```text
dev
  -> PR dev to main
  -> GitHub CI quality + tests
  -> merge to main
  -> Release Please maintains stable PR/tag on main
```

Production deploy flow:

```text
local machine
  -> build assets locally via pnpm run build
  -> rsync working tree to VPS release directory
  -> remote composer install --no-dev
  -> php artisan migrate --force
  -> php artisan permission:sync-roles --no-interaction
  -> php artisan init:superadmin --no-interaction
  -> cache config/routes/views/events
  -> php artisan storage:link
  -> switch /srv/nova-helpdesk/current symlink
  -> prune old releases
```

Observed files:

- `.github/workflows/release-please.yml` runs Release Please for `dev` prereleases and `main` stable releases.
- `release-please-config.dev.json` configures RC releases.
- `release-please-config.json` configures stable releases.
- `.release-please-manifest.json` currently tracks stable `0.23.0`.
- `.release-please-manifest.dev.json` currently tracks stale RC `0.22.1-rc.1`.
- `config/version.php` currently tracks app version `0.23.0`.
- `version.json` currently tracks stale `0.22.1` and is redundant.
- `deploy.sh` and `.deploy/` exist locally but are ignored by git.

## Recommended Target Workflow

Development and RC flow remains:

```text
feature/fix branch
  -> PR to dev
  -> CI quality + tests
  -> merge to dev
  -> Release Please maintains RC PR/tag on dev
```

Stable release flow remains:

```text
dev
  -> PR dev to main
  -> CI quality + tests
  -> merge to main
  -> Release Please stable PR/tag on main
```

Production deploy remains local:

```text
git fetch origin --prune --tags
git checkout main
git pull --ff-only origin main
git checkout vX.Y.Z
composer ci:check
./deploy.sh vX.Y.Z
```

`deploy.sh` must validate:

- working tree is clean
- target tag exists locally
- target tag matches `^v[0-9]+\.[0-9]+\.[0-9]+$`
- target tag is not an RC/prerelease
- checked-out commit equals the target tag commit
- `config/version.php` contains the same version without the leading `v`
- required local commands exist: `git`, `pnpm`, `ssh`, `rsync`
- production `.env` already exists on the server

## Implementation Tasks

### Task 1: Remove Retired Versioning Surface

**Files:**
- Delete: `version.json`
- Modify: `README.md`
- Modify: `CONTRIBUTING.md`
- Modify: `.serena/memories/handoff/git_publish.md` if still relevant after memory update

**Interfaces:**
- Consumes: existing Release Please configs and `config/version.php`
- Produces: one documented versioning path: Release Please + tags + `config/version.php`

- [ ] Check references to `version.json`, `composer version:bump`, and `app:bump-version`.
- [ ] Delete `version.json`.
- [ ] Remove or rewrite references to retired version bump tooling.
- [ ] Ensure docs say Release Please owns version bumps from Conventional Commits.
- [ ] Run a search confirming retired strings no longer appear outside historical changelog/plans.
- [ ] Commit with `chore(release): remove retired versioning path`.

### Task 2: Reconcile Release Please RC Baseline

**Files:**
- Modify: `.release-please-manifest.dev.json`

**Interfaces:**
- Consumes: stable baseline in `.release-please-manifest.json`
- Produces: RC branch manifest that starts from the current stable baseline

- [ ] Confirm current stable manifest version.
- [ ] Decide the correct dev prerelease baseline. For current state, align from `0.23.0` so the next RC can become `0.24.0-rc.1` for a feature or `0.23.1-rc.1` for a fix, depending on Release Please commit analysis.
- [ ] Update `.release-please-manifest.dev.json` only if Release Please expects that baseline in this branch model.
- [ ] Run no app tests for this metadata-only change unless paired with code changes.
- [ ] Commit with `chore(release): sync dev prerelease baseline`.

### Task 3: Make Local Deploy Guarded And Reproducible

**Files:**
- Modify: `.gitignore`
- Modify/Create tracked: `deploy.sh`
- Optional tracked templates: `.deploy/nginx/helpdesk.sinergilogistik.com`
- Optional tracked templates: `.deploy/supervisor/nova-helpdesk.conf`

**Interfaces:**
- Consumes: stable tag argument `vX.Y.Z`
- Produces: deterministic VPS release directory named with tag and commit hash

- [ ] Stop ignoring `deploy.sh`; keep secrets out of it before tracking.
- [ ] Decide whether `.deploy/` should be tracked as sanitized ops templates. If tracked, verify no secret values exist.
- [ ] Change `deploy.sh` to require exactly one argument: `./deploy.sh vX.Y.Z`.
- [ ] Add local guard functions for clean worktree, tag format, stable-only tag, tag checkout, version match, and required binaries.
- [ ] Use release directory naming that includes tag and commit, for example `/srv/nova-helpdesk/releases/v0.23.0-3aeea21`.
- [ ] Keep local Vite build in the script.
- [ ] Keep rsync upload to VPS.
- [ ] Keep remote composer install, migration, role sync, superadmin init, cache, storage link, symlink switch, and prune.
- [ ] Do not create or edit production `.env` in the deploy script.
- [ ] If `/srv/nova-helpdesk/shared/.env` is missing, fail with a clear error.
- [ ] Commit with `chore(deploy): guard local production deploys`.

### Task 4: Remove Hardcoded Production Secrets From Deploy Script

**Files:**
- Modify: `deploy.sh`
- Optional docs update: `README.md` or `CONTRIBUTING.md` only if the developer wants deploy instructions documented in repo docs

**Interfaces:**
- Consumes: existing `/srv/nova-helpdesk/shared/.env` on the VPS
- Produces: deploy script with no DB password, no generated superadmin email, and no production `.env` provisioning

- [ ] Remove the block that creates `/srv/nova-helpdesk/shared/.env` from `.env.example`.
- [ ] Remove hardcoded `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and superadmin values from the script.
- [ ] Add a remote preflight check that prints required missing variables by name without printing values.
- [ ] Keep `php artisan key:generate --force` only if the shared `.env` exists but lacks `APP_KEY`; otherwise require pre-provisioning.
- [ ] Commit with `chore(deploy): rely on provisioned production env`.

### Task 5: Align Documentation And Agent Memory

**Files:**
- Modify: `README.md`
- Modify: `CONTRIBUTING.md`
- Modify Serena memory: `mem:versioning`
- Modify Serena memory: `mem:handoff/git_publish`

**Interfaces:**
- Consumes: final approved workflow from this handoff
- Produces: consistent docs and agent memory for future sessions

- [ ] Document that Release Please handles RC on `dev` and stable on `main`.
- [ ] Document that production deploy is local-only and must target a stable tag.
- [ ] Document that deploy is intentionally not performed by GitHub Actions.
- [ ] Remove old instructions that say to run `composer version:bump`.
- [ ] Update `mem:versioning` if details change.
- [ ] Update `mem:handoff/git_publish` so it no longer contradicts Release Please.
- [ ] Commit with `docs(release): document local deploy workflow`.

## Verification Checklist

Run before handoff or commit, scoped to touched files:

```bash
git status -sb
git diff --check
```

For PHP/frontend code changes, use the project finishing gate from `AGENTS.md`:

```bash
vendor/bin/pint --dirty --format agent
pnpm run lint
pnpm run format
php artisan wayfinder:generate --with-form --no-interaction # only if routes/controllers/actions changed
composer ci:check
```

For docs-only or shell-only edits, at minimum run:

```bash
bash -n deploy.sh
git diff --check
```

If `deploy.sh` changes, do not run production deploy as verification unless the developer explicitly asks.

## Handoff Notes For Opus 4.8

- The developer wants local production deploy builds. Do not propose moving deploy/build to GitHub Actions unless they explicitly reopen that decision.
- GitHub Actions remains responsible for CI and Release Please only.
- Treat `version.json` as retired; remove it rather than resynchronizing it.
- Treat hardcoded credentials in `deploy.sh` as a cleanup target.
- Be careful with `.release-please-manifest.dev.json`: verify Release Please behavior before changing the baseline if branch history has moved since this handoff.
- This is a Deep-path change. Use `superpowers:brainstorming` if scope changes, then `superpowers:writing-plans` / execution skill before editing.
