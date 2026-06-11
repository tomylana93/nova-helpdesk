# Handoff / Git Publish

- Before committing, inspect `git status -sb` and diff; stage only files in scope.
- Use Conventional Commits. For features use `feat(...)`, then run `composer version:bump` so `version.json`, `CHANGELOG.md`, release commit, and tag are created by project tooling.
- After version bump, run `composer run ci:check` before push/PR unless explicitly skipped and reported.
- Push the working branch with tags: `git push origin <branch> --follow-tags`.
- For PR-to-main handoff: create PR from current branch to `main`, merge with a merge commit when release/history commits should be preserved, then `git fetch origin --prune --tags`.
- After merge, sync local `main` via fast-forward, switch back to the original branch, fast-forward it to `origin/main`, then push that branch so it reflects the merged result.
- End with branch, commit/tag, PR URL/number, merge status, CI result, and final local branch/status.