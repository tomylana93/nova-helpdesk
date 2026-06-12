# Handoff / Git Publish

- Before committing, inspect `git status -sb` and diff; stage only files in scope.
- Use Conventional Commits. For features use `feat(...)`, then run `composer version:bump` so `version.json`, `CHANGELOG.md`, release commit, and tag are created by project tooling.
- After version bump, run `composer run ci:check` before push/PR unless explicitly skipped and reported.
- Push the working branch with tags: `git push origin <branch> --follow-tags`.
- **For PR-to-main handoff**: Always create a Pull Request (PR) on GitHub from the working branch (e.g. `dev`) to `main` using the GitHub CLI (`gh pr create`). DO NOT merge directly/locally to `main`.
- Merge the PR on GitHub (using `gh pr merge --merge` or via web) to preserve history/release commits, then run `git fetch origin --prune --tags`.
- After merge, sync local `main` via fast-forward (`git checkout main && git pull origin main`), switch back to the original branch, fast-forward it to `origin/main`, then push that branch so it reflects the merged result.
- End with branch, commit/tag, PR URL/number, merge status, CI result, and final local branch/status.
