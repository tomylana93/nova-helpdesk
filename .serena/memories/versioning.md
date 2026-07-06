# Versioning and Releases

- Source of truth is Git tags / GitHub Releases managed by Release Please, mirrored at runtime in `config/version.php` via `config('version.app')`.
- `version.json`, `composer version:bump`, and `php artisan app:bump-version` are legacy/retired and must not be used for new release work.
- Release Please configs:
  - Stable releases from `main`: `release-please-config.json` + `.release-please-manifest.json`.
  - Release candidates from `dev`: `release-please-config.dev.json` + `.release-please-manifest.dev.json` with `rc` prereleases.
- Conventional Commits drive changelog and bump type:
  - `feat:` -> minor in 0.x.
  - `fix:` / `perf:` / `refactor:` -> patch.
  - `feat!:` or `BREAKING CHANGE:` -> pre-1.0 minor per current release-please config.
  - `ci:`, `chore:`, `test:`, and `style:` are hidden from changelog sections.
- Do not edit `config/version.php` by hand except through release automation metadata changes; the line contains the `x-release-please-version` marker.
- Commit directly to `dev` only when explicitly requested. Never push directly to `main`; stable releases flow through `dev` -> `main` PR and Release Please metadata PRs.