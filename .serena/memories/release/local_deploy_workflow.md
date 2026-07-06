# Release / Local Deploy Workflow Decision

- Development flow remains: feature/fix branch -> PR to `dev` -> CI quality/tests -> merge to `dev` -> Release Please maintains RC PR/tag on `dev`.
- Stable flow remains: `dev` -> PR to `main` -> CI quality/tests -> merge to `main` -> Release Please maintains stable PR/tag on `main`.
- Production deploy must stay local by developer preference. Do not move build/deploy to GitHub Actions unless the developer explicitly reopens this decision.
- GitHub Actions is responsible for CI and Release Please metadata/tags only; physical deploy is local.
- Recommended production deploy path: fetch tags, checkout/pull `main`, checkout exact stable tag `vX.Y.Z`, optionally run `composer ci:check`, then run `./deploy.sh vX.Y.Z` locally.
- `deploy.sh` should be made guarded/reproducible: require stable tag argument, reject dirty worktree, reject RC tags, verify checked-out commit equals tag, verify `config/version.php` matches the tag, build assets locally, rsync to VPS, run remote Composer/migrations/role sync/cache/symlink/prune.
- `deploy.sh` must not create production `.env` or contain DB/superadmin secrets. Production `/srv/nova-helpdesk/shared/.env` should be provisioned on the server; deploy should fail clearly if it is missing.
- `version.json`, `composer version:bump`, and `php artisan app:bump-version` are retired. Source of truth is Release Please + Git tags/GitHub Releases, mirrored in `config/version.php`.
- Handoff implementation plan for Opus 4.8: `docs/superpowers/plans/2026-07-06-release-deploy-handoff.md`.

Related memories: `mem:versioning`, `mem:task_completion`, `mem:handoff/git_publish`.