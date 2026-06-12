# Handoff / Git Publish

- Before committing, inspect `git status -sb` and diff; stage only files in scope.
- Use Conventional Commits. For features use `feat(...)`, then run `composer version:bump` so `version.json`, `CHANGELOG.md`, release commit, and tag are created by project tooling.
- After version bump, run `composer run ci:check` before push/PR unless explicitly skipped and reported.
- Push the working branch with tags: `git push origin <branch> --follow-tags`.
- **For PR-to-main handoff**: Always create a Pull Request (PR) on GitHub from the working branch (e.g. `dev`) to `main` using the GitHub CLI (`gh pr create`). DO NOT merge directly/locally to `main`.
- Merge the PR on GitHub (using `gh pr merge --merge` or via web) to preserve history/release commits, then run `git fetch origin --prune --tags`.
- After merge, sync local `main` via fast-forward (`git checkout main && git pull origin main`).
- Run production VPS deployment from the merged `main` checkout with `./deploy.sh`; it builds assets locally, uploads a release to the server, runs remote Composer/migrations/role sync/cache steps, updates the `current` symlink, and prunes old releases. Treat failures as deployment blockers and do not continue branch sync until resolved.
- After successful deploy, switch back to the original branch, fast-forward it to `origin/main`, then push that branch so it reflects the merged result.
- GitHub stable release polish after deploy:
  - Ensure the new release tag and the previous release tag exist on remote (`git push origin vX.Y.Z` as needed) so GitHub generated changelog links work.
  - Use GitHub release notes format with `## What's Changed` and `**Full Changelog**: .../compare/previous...current`; `gh api repos/{owner}/{repo}/releases/generate-notes` can generate notes when `previous_tag_name` exists remotely, but manually include the feature PR if the release tag commit predates the PR merge commit.
  - Mark the release latest with `gh release edit <tag> --latest`.
  - For an Immutable badge like GitHub's release UI, enable repo immutable releases via `gh api repos/{owner}/{repo}/immutable-releases -X PUT`; existing releases remain mutable until republished, so toggle the target release draft/published (`gh release edit <tag> --draft && gh release edit <tag> --draft=false --latest`) and verify `immutable: true` with `gh api repos/{owner}/{repo}/releases/tags/<tag>`.
- End with branch, commit/tag, PR URL/number, merge status, CI result, deploy result, release URL/latest/immutable status, and final local branch/status.
