# Branching & Release Flow

Actual git flow for this repo (three long-lived branches):

`dev` (working) → `staging` (pre-release) → `main` (stable release)

- **`dev`** — working/integration branch. New feature work and dependency updates start here.
- **`staging`** — pre-release branch. Promote `dev` → `staging` via PR for release-candidate validation.
- **`main`** — protected, stable release branch. Promote `staging` → `main` via PR. Required checks `ci` + `quality` must pass before merge (ruleset: PR required, no direct/force push).

## Promotion mechanics
- Each promotion is a GitHub PR into the next branch; merge with a merge commit (keep branches in sync, don't squash long-lived branches).
- After merging `staging` → `main`, fast-forward `staging` back to `main` and keep `dev` current so history stays linear.
- release-please drives changelog + SemVer bumps (0.x) from conventional commits; pushing to `main` opens a metadata-only release PR that tags stable `X.Y.Z`.

## In sync with CLAUDE.md / AGENTS.md
As of 2026-07-07, `.ai/guidelines/02-ai-workflow.md` (and the regenerated `CLAUDE.md` / `AGENTS.md`) document this three-branch `dev` → `staging` → `main` flow and the dual-manifest re-baseline step. The guidelines and this memory now agree — no discrepancy to work around.

## Two independent release-please manifests — re-baseline gotcha
This repo runs release-please twice with **separate config+manifest per branch**:
- `main`: `release-please-config.json` + `.release-please-manifest.json` (`prerelease: false`, stable `X.Y.Z`).
- `dev`: `release-please-config.dev.json` + `.release-please-manifest.dev.json` (`prerelease: true`, `prerelease-type: rc`, `versioning: prerelease`).

The two manifests are **independent** — the `main` job never touches `.release-please-manifest.dev.json`. With `versioning: prerelease`, if the dev manifest already holds a prerelease value, release-please only bumps the rc number (`0.1.0-rc.1 → rc.2`) and **ignores** what `main` shipped. So after a stable release the dev line can drift *behind* stable (bug seen 2026-07-07: `main` at `0.2.0` but dev PR proposed `0.1.0-rc.2`).

**MANDATORY runbook step — after every stable release on `main`:** set `.release-please-manifest.dev.json` to the new **stable** version (e.g. `0.2.0`). Because the value is now stable (no `-rc`), the next dev run computes the next core bump from commits and appends the rc → e.g. `0.2.1-rc` (first) then `0.2.1-rc.1`, so rc always *leads* stable. Commit as `chore(release): re-baseline dev prerelease manifest to X.Y.Z`; the next push to `dev` auto-updates the open dev release PR.

- **deploy.sh failure safety**: `activate_release` triggers rollback if `restart_workers` fails, and handles first-deploy failure (removes the `current` symlink and fails with "no previous release to restore" message).
- **Required CI checks**: `composer test:deploy` is a required check called in `composer test`, `composer ci:check`, and `.github/workflows/tests.yml`.
- **Test coverage**: `tests/Deployment/deploy_test.sh` is source-safe and covers dirty worktree, tag missing/unreachable, version mismatch, checksum failure, first deploy failure, restart failure, and dry-run behaviors using isolated git/ssh/rsync mocks.

Related: `mem:task_completion`, `mem:suggested_commands`.

