# Contributing to Nova Helpdesk

Thank you for your interest in contributing to Nova Helpdesk! To maintain code quality, security, and consistent versioning, please review and follow these guidelines.

---

## 🌿 Git Branching Model & PR Flow

1. **Working Branch**: Always branch off the `dev` branch for your work:
    - Features: `feat/your-feature-name`
    - Bug fixes: `fix/your-bug-name`
    - Refactoring: `refactor/refactor-target`
2. **Pull Request (PR)**: Target your PR back to the `dev` branch.
    - Run all linting, formatting, and unit tests locally before opening a PR.
    - Provide a clear PR description detailing your changes.
3. **Merging to Main**: The `dev` branch is merged into `main` only for stable releases.

---

## 💬 Conventional Commits & Versioning

Nova Helpdesk uses Semantic Versioning (SemVer) driven by your commit messages. Your commit prefix directly affects the automated release version bump:

| Prefix                                      | Description                                               | SemVer Impact                             |
| :------------------------------------------ | :-------------------------------------------------------- | :---------------------------------------- |
| `feat:`                                     | A new feature                                             | Bumps **Minor** (e.g. `0.8.0` ➡️ `0.9.0`) |
| `fix:`                                      | A bug fix                                                 | Bumps **Patch** (e.g. `0.8.0` ➡️ `0.8.1`) |
| `refactor:`                                 | A code change that neither fixes a bug nor adds a feature | Bumps **Patch**                           |
| `chore:`, `style:`, `test:`, `docs:`, `ci:` | Maintenance, style formatting, testing, docs, etc.        | Bumps **Patch**                           |
| `feat!:` or `BREAKING CHANGE:`              | A breaking API change                                     | Bumps **Major** (e.g. `0.8.0` ➡️ `1.0.0`) |

### Example Commit Messages:

- `feat(auth): implement two-factor authentication recovery codes`
- `fix(tickets): resolve SLA breach notification duplication`
- `docs: add contributing guidelines`

> **Release Please owns versioning.** Do not bump versions by hand and do not edit `config/version.php` (it carries the `x-release-please-version` marker). Pushing to `dev` maintains a release-candidate (`X.Y.Z-rc.N`) PR/tag; merging `dev` → `main` maintains the stable (`X.Y.Z`) PR/tag. The retired `version.json` / `composer version:bump` / `app:bump-version` paths must not be used.

---

## 🚀 Release & Deployment

Production is deployed **locally** from an exact stable tag — deployment is intentionally **not** performed by GitHub Actions (CI and Release Please metadata only).

Infra targets are not tracked. Copy `.env.deploy.example` to `.env.deploy` (git-ignored) once and fill in `DEPLOY_HOST`, `DEPLOY_ROOT`, and `REVERB_HOST`:

```bash
cp .env.deploy.example .env.deploy   # first time only
git fetch origin --prune --tags
git checkout main && git pull --ff-only origin main
git checkout vX.Y.Z          # exact stable tag; RC tags are rejected
composer ci:check            # optional local gate
./deploy.sh vX.Y.Z
```

`deploy.sh` validates a clean worktree, stable tag format, `HEAD == tag commit`, and a matching `config/version.php` before it builds and uploads. Production secrets are **not** in the repo: the server must already have `/srv/nova-helpdesk/shared/.env` provisioned (with `APP_KEY`, DB credentials, etc.). The deploy script never creates that file and never writes DB credentials or other secrets into it — it aborts with a named-variable list if anything required is missing. The only value it may write is a generated `APP_KEY`, and only when one is absent on first deploy.

---

## 🏗️ Architectural Guidelines

### Thin Controllers & Dedicated Action Classes

To maintain clean code:

- Keep controllers thin: Limit controller methods to route binding, simple validation, action invocation, and Inertia response composition.
- Move complex database writes, file storage handling, external requests, and event dispatches into **Action** classes inside `app/Actions/{Domain}/`.
- Inject action dependencies via constructor promoting, and execute using a public `handle(...)` method.

### Frontend Conventions

- Vue components must live in `resources/js/`.
- Ensure components use a single root HTML element.
- Reuse existing ui elements (e.g., custom DataTables, uploaders, and style variables).
- Use Tailwind CSS utility classes instead of arbitrary vanilla styles where possible.

---

## 🧪 Verification & Quality Gates

Before committing your code, you MUST run the validation script to ensure that you match formatting rules and pass all tests:

```bash
# Run Pint, Prettier, TypeScript, PHPStan, Rector dry-run, and Pest test suite
composer run ci:check
```

- **Backend Formatting**: Run Laravel Pint formatter using `vendor/bin/pint --dirty --format agent`.
- **Frontend Formatting**: Run Prettier using `pnpm run format`.
- **Frontend Linting**: Run ESLint checks using `pnpm run lint`.
