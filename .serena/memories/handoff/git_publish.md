# Handoff / Git Publish

- Before committing, inspect `git status -sb` and diff; stage only files in scope.
- Use Conventional Commits (`feat(...)`, `fix(...)`, etc.). Do NOT bump versions by hand: Release Please owns version bumps, `CHANGELOG.md`, release commits, and tags. `composer version:bump` / `app:bump-version` / `version.json` are retired — see `mem:versioning` and `mem:release/local_deploy_workflow`.
- Run `composer run ci:check` before push/PR unless explicitly skipped and reported.
- Push the working branch with tags: `git push origin <branch> --follow-tags`.
- **For PR-to-main handoff**: Always create a Pull Request (PR) on GitHub from the working branch (e.g. `dev`) to `main` using the GitHub CLI (`gh pr create`). DO NOT merge directly/locally to `main`.
- Merge the PR on GitHub (using `gh pr merge --merge` or via web) to preserve history/release commits, then run `git fetch origin --prune --tags`.
- After merge, sync local `main` via fast-forward (`git checkout main && git pull origin main`).
- Run production VPS deployment locally from an exact stable tag: `git fetch origin --prune --tags`, `git checkout main && git pull --ff-only origin main`, `git checkout vX.Y.Z`, optionally `composer ci:check`, then `./deploy.sh vX.Y.Z`. The script guards for clean worktree, stable-only tag (rejects RC), HEAD==tag commit, and `config/version.php` match before building assets locally, rsync'ing a `vX.Y.Z-<hash>` release, and running remote Composer/migrations/role-sync/cache/symlink/prune + supervisor restart. Deploy is intentionally local-only (never GitHub Actions); production `.env` must be pre-provisioned in `/srv/nova-helpdesk/shared/.env` (deploy fails if missing). Treat failures as deployment blockers.
- After successful deploy, switch back to the original branch, fast-forward it to `origin/main`, then push that branch so it reflects the merged result.
- GitHub stable release polish after deploy:
  - Ensure the new release tag and the previous release tag exist on remote (`git push origin vX.Y.Z` as needed) so GitHub generated changelog links work.
  - Use GitHub release notes format with `## What's Changed` and `**Full Changelog**: .../compare/previous...current`; `gh api repos/{owner}/{repo}/releases/generate-notes` can generate notes when `previous_tag_name` exists remotely, but manually include the feature PR if the release tag commit predates the PR merge commit.
  - Mark the release latest with `gh release edit <tag> --latest`.
  - For an Immutable badge like GitHub's release UI, enable repo immutable releases via `gh api repos/{owner}/{repo}/immutable-releases -X PUT`; existing releases remain mutable until republished, so toggle the target release draft/published (`gh release edit <tag> --draft && gh release edit <tag> --draft=false --latest`) and verify `immutable: true` with `gh api repos/{owner}/{repo}/releases/tags/<tag>`.
- End with branch, commit/tag, PR URL/number, merge status, CI result, deploy result, release URL/latest/immutable status, and final local branch/status.
