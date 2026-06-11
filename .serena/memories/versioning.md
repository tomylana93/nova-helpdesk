# Versioning and Commits

- **Versioning Scheme**: Semantic Versioning (`major.minor.patch`) tracked in `version.json`.
- **Version Bumping**: Execute `composer version:bump` (or `php artisan app:bump-version`). By default, it runs in `auto` mode.
- **Auto Bumping Rules**: Bumps version by parsing git commit log since the last release tag:
  - Bumps `major` if any commit contains `BREAKING CHANGE:` or `BREAKING:` (or has `!:` signature like `feat!:`).
  - Bumps `minor` if any commit is `feat:`.
  - Bumps `patch` if commits only contain `fix:`, `refactor:`, `chore:`, `style:`, `test:`, `docs:`, or `ci:`.
- **Commit Format Guidelines**:
  - Always write commit messages following the Conventional Commits specification.
  - Prefix commit messages with appropriate type:
    - `feat:` for new features (triggers minor bump).
    - `fix:` for bug fixes (triggers patch bump).
    - `refactor:` for code changes that neither fix a bug nor add a feature (triggers patch bump).
    - `chore:`, `style:`, `test:`, `docs:`, `ci:` for auxiliary tasks (triggers patch bump).
  - Add `!` (e.g. `feat!:`, `fix!:`) or include `BREAKING CHANGE:` in the description for breaking changes (triggers major bump).
- **Auto Git Tagging**: Bumping version automatically stages `version.json`, commits `chore(release): vX.Y.Z`, and creates local Git tag `vX.Y.Z`.
