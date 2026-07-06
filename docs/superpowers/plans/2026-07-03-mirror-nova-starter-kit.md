# Mirror Nova Starter Kit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Update `nova-helpdesk` so its shared foundation tracks sibling `/home/tomylana93/projects/nova-starter-kit` as closely as practical.

**Architecture:** Treat this as foundation synchronization, not a helpdesk domain rewrite. Copy starter-kit conventions for tooling, dependencies, static analysis, CI, and release automation, while preserving local helpdesk code and dependencies that are still referenced.

**Tech Stack:** Laravel 13, PHP 8.5, Inertia 3, Vue 3, Tailwind CSS 4, Pest 4, PHPStan/Larastan 3, Rector 2, pnpm, GitHub Actions, release-please.

## Global Constraints

- Keep `php` at `^8.5`.
- Do not remove helpdesk-specific dependencies unless local usage checks prove they are unused.
- Do not hand-edit generated files under `resources/js/actions/**`, `resources/js/routes/**`, or `resources/js/wayfinder/**`.
- Run `php artisan wayfinder:generate --with-form --no-interaction` if any route file, controller, or invokable action changes.
- Do not commit unless the developer explicitly requests it.
- Use Laravel Boost docs for Laravel ecosystem syntax and Context7 for external library documentation if new API questions arise during execution.
- Preserve project-specific helpdesk routes, docs, config, app code, tests, and UI.

---

### Task 1: Add Release Version Config and Test

**Files:**
- Create: `config/version.php`
- Create: `tests/Feature/VersionConfigTest.php`
- Modify: `release-please-config.json`
- Modify: `release-please-config.dev.json`
- Create: `.release-please-manifest.json`
- Create: `.release-please-manifest.dev.json`

**Interfaces:**
- Consumes: current app version from `version.json`, currently `0.22.1`.
- Produces: `config('version.app'): string`, release-please-managed by `config/version.php`.

- [ ] **Step 1: Write the failing version config test**

Create the test with Artisan:

```bash
php artisan make:test --pest VersionConfigTest --no-interaction
```

Replace `tests/Feature/VersionConfigTest.php` with:

```php
<?php

it('exposes the application version from config', function (): void {
    expect(config('version.app'))
        ->toBeString()
        ->toMatch('/^0\.\d+\.\d+(?:-rc\.\d+)?$/');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run:

```bash
php artisan test --compact tests/Feature/VersionConfigTest.php
```

Expected: FAIL because `config('version.app')` is not defined yet.

- [ ] **Step 3: Add `config/version.php`**

Create `config/version.php`:

```php
<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | Bumped automatically by release-please from conventional commits.
    | Do not edit by hand. Read it anywhere via config('version.app').
    |
    */
    'app' => '0.22.1', // x-release-please-version
];
```

- [ ] **Step 4: Add release-please configs adapted for helpdesk**

Create `release-please-config.json`:

```json
{
    "$schema": "https://raw.githubusercontent.com/googleapis/release-please/main/schemas/config.json",
    "packages": {
        ".": {
            "release-type": "simple",
            "package-name": "nova-helpdesk",
            "bump-minor-pre-major": true,
            "bump-patch-for-minor-pre-major": false,
            "draft": false,
            "prerelease": false,
            "include-component-in-tag": false,
            "extra-files": [
                {
                    "type": "generic",
                    "path": "config/version.php"
                }
            ],
            "changelog-sections": [
                { "type": "feat", "section": "Features" },
                { "type": "fix", "section": "Bug Fixes" },
                { "type": "perf", "section": "Performance" },
                { "type": "refactor", "section": "Refactors" },
                { "type": "docs", "section": "Documentation" },
                { "type": "build", "section": "Build System" },
                { "type": "ci", "section": "CI", "hidden": true },
                { "type": "chore", "section": "Chores", "hidden": true },
                { "type": "test", "section": "Tests", "hidden": true },
                { "type": "style", "section": "Styles", "hidden": true }
            ]
        }
    }
}
```

Create `release-please-config.dev.json`:

```json
{
    "$schema": "https://raw.githubusercontent.com/googleapis/release-please/main/schemas/config.json",
    "packages": {
        ".": {
            "release-type": "simple",
            "package-name": "nova-helpdesk",
            "bump-minor-pre-major": true,
            "bump-patch-for-minor-pre-major": false,
            "prerelease": true,
            "prerelease-type": "rc",
            "versioning": "prerelease",
            "draft": false,
            "include-component-in-tag": false,
            "extra-files": [
                {
                    "type": "generic",
                    "path": "config/version.php"
                }
            ],
            "changelog-sections": [
                { "type": "feat", "section": "Features" },
                { "type": "fix", "section": "Bug Fixes" },
                { "type": "perf", "section": "Performance" },
                { "type": "refactor", "section": "Refactors" },
                { "type": "docs", "section": "Documentation" },
                { "type": "build", "section": "Build System" },
                { "type": "ci", "section": "CI", "hidden": true },
                { "type": "chore", "section": "Chores", "hidden": true },
                { "type": "test", "section": "Tests", "hidden": true },
                { "type": "style", "section": "Styles", "hidden": true }
            ]
        }
    }
}
```

Create `.release-please-manifest.json`:

```json
{
    ".": "0.22.1"
}
```

Create `.release-please-manifest.dev.json`:

```json
{
    ".": "0.22.1-rc.1"
}
```

- [ ] **Step 5: Run the test to verify it passes**

Run:

```bash
php artisan test --compact tests/Feature/VersionConfigTest.php
```

Expected: PASS.

---

### Task 2: Mirror Composer Dependencies and Scripts

**Files:**
- Modify: `composer.json`
- Modify: `composer.lock`

**Interfaces:**
- Consumes: Composer package constraints from current `nova-helpdesk` and sibling `nova-starter-kit`.
- Produces: Composer scripts `types:check`, `test`, and `ci:check` matching starter-kit structure with local PHP 8.5 and helpdesk packages preserved.

- [ ] **Step 1: Update `composer.json` dependency constraints**

Edit `composer.json`:

```json
"spatie/laravel-permission": "^8.0"
```

```json
"larastan/larastan": "^3.9"
```

Keep:

```json
"php": "^8.5"
```

Keep current helpdesk-specific package requirements, including `laravel/chisel`, `maatwebsite/excel`, `spatie/laravel-medialibrary`, `spatie/laravel-query-builder`, and `spatie/laravel-settings`.

- [ ] **Step 2: Update Composer scripts**

In `composer.json`, remove `analyse` and `version:bump`, add `types:check`, and set the scripts to this shape:

```json
"dev": [
    "Composer\\Config::disableProcessTimeout",
    "pnpm dlx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74,#86efac\" \"php artisan serve --host=localhost\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"php artisan reverb:start\" \"pnpm run dev\" --names=server,queue,logs,reverb,vite --kill-others"
],
"lint": [
    "pint --parallel"
],
"lint:check": [
    "pint --parallel --test"
],
"refactor": [
    "rector process --no-progress-bar"
],
"refactor:check": [
    "rector process --dry-run --no-progress-bar"
],
"ci:check": [
    "Composer\\Config::disableProcessTimeout",
    "pnpm run lint:check",
    "pnpm run format:check",
    "pnpm run types:check",
    "@test"
],
"types:check": [
    "phpstan analyse"
],
"test": [
    "@php artisan config:clear --ansi",
    "@lint:check",
    "@refactor:check",
    "@types:check",
    "@php artisan test"
]
```

- [ ] **Step 3: Update Composer lockfile**

Run:

```bash
composer update spatie/laravel-permission larastan/larastan --with-all-dependencies
```

Expected: `composer.lock` updates and dependency resolution succeeds.

- [ ] **Step 4: Validate Composer metadata**

Run:

```bash
composer validate --strict
```

Expected: PASS, except for the known existing warning about `maatwebsite/excel` using the preserved `4.x-dev` constraint. That warning is accepted because this plan explicitly keeps the current helpdesk-specific Excel requirement.

---

### Task 3: Mirror Frontend Dependency Tooling

**Files:**
- Modify: `package.json`
- Modify: `pnpm-lock.yaml`
- Modify: `eslint.config.js`

**Interfaces:**
- Consumes: current local frontend dependencies and starter-kit frontend tooling constraints.
- Produces: ESLint config using `eslint-plugin-import-x` and pnpm lockfile matching the updated package graph.

- [ ] **Step 1: Update `package.json` tooling dependencies**

Apply these dependency changes:

```json
"@eslint/js": "^10.0.1",
"@types/node": "^26.0.0",
"concurrently": "^10.0.3",
"eslint": "10.4.1",
"eslint-plugin-import-x": "^4.16.2",
"eslint-plugin-vue": "^10.9.2",
"typescript": "^6.0.3",
"vue-tsc": "^3.3.5"
```

Remove:

```json
"eslint-plugin-import": "^2.32.0"
```

Update runtime dependencies that align with the starter kit:

```json
"@vueuse/core": "^14.3.0",
"reka-ui": "^2.9.8"
```

Keep local packages currently used by helpdesk code, including `@laravel/echo-vue`, `laravel-echo`, `pusher-js`, `@unovis/ts`, `@unovis/vue`, `shadcn-vue`, `lucide-vue-next`, and `@lucide/vue`.

- [ ] **Step 2: Update ESLint config for import-x**

Change `eslint.config.js` from `eslint-plugin-import` to `eslint-plugin-import-x`:

```js
import importPlugin from 'eslint-plugin-import-x';
```

Set plugin key:

```js
plugins: {
    'import-x': importPlugin,
},
```

Set resolver:

```js
settings: {
    'import-x/resolver': {
        typescript: {
            alwaysTryTypes: true,
            project: './tsconfig.json',
        },
    },
},
```

Rename rules:

```js
'import-x/order': [
    'error',
    {
        groups: ['builtin', 'external', 'internal', 'parent', 'sibling', 'index'],
        alphabetize: { order: 'asc', caseInsensitive: true },
    },
],
'import-x/consistent-type-specifier-style': [
    'error',
    'prefer-top-level',
],
```

- [ ] **Step 3: Update pnpm lockfile**

Run:

```bash
pnpm install --lockfile-only
```

Expected: `pnpm-lock.yaml` updates and dependency resolution succeeds.

- [ ] **Step 4: Run frontend checks**

Run:

```bash
pnpm run lint:check
pnpm run format:check
pnpm run types:check
```

Expected: all checks pass. If lint or format fails with auto-fixable issues, run `pnpm run lint` and `pnpm run format`, then rerun the check commands.

---

### Task 4: Mirror Static Analysis and Rector Baselines

**Files:**
- Modify: `phpstan.neon`
- Modify: `rector.php`

**Interfaces:**
- Consumes: starter-kit PHPStan and Rector conventions.
- Produces: stricter static analysis over application, bootstrap, config, database, and routes, plus Rector skip-list parity with `nova-starter-kit`.

- [ ] **Step 1: Update `phpstan.neon`**

Set:

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
        - bootstrap/app.php
        - config/
        - database/
        - routes/

    level: 7
```

- [ ] **Step 2: Update `rector.php` imports**

Add these imports:

```php
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;
```

- [ ] **Step 3: Update `rector.php` skip list**

Add these classes to the existing `withSkip([...])` list, matching the sibling `nova-starter-kit` baseline:

```php
AddClosureVoidReturnTypeWhereNoReturnRector::class,
ClosureReturnTypeRector::class,
AddArrowFunctionReturnTypeRector::class,
AddFunctionVoidReturnTypeWhereNoReturnRector::class,
```

- [ ] **Step 4: Run PHP analysis checks**

Run:

```bash
composer refactor:check
composer types:check
```

Expected: both pass. If Rector reports mechanical changes, run `composer refactor`, review the diff, then rerun `composer refactor:check`. If PHPStan exposes local typing issues, fix only mechanical issues in the touched scope; stop for developer review if a domain decision is required.

---

### Task 5: Mirror GitHub Actions and Dependabot

**Files:**
- Modify: `.github/workflows/lint.yml`
- Modify: `.github/workflows/tests.yml`
- Create: `.github/workflows/release-please.yml`
- Create: `.github/dependabot.yml`

**Interfaces:**
- Consumes: starter-kit CI conventions.
- Produces: quality, test, release, and GitHub Actions dependency automation.

- [ ] **Step 1: Update `.github/workflows/lint.yml`**

Set the workflow to:

```yaml
name: lint

on:
  push:
    branches:
      - dev
      - main
  pull_request:
    branches:
      - dev
      - main

permissions:
  contents: write

jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v6
        with:
          persist-credentials: false

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'

      - name: Setup Node
        uses: actions/setup-node@v6
        with:
          node-version: '24'

      - name: Enable pnpm
        run: corepack enable pnpm

      - name: Install Dependencies
        run: |
          composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
          pnpm install --frozen-lockfile --config.strict-dep-builds=false

      - name: Copy Environment File
        run: cp .env.example .env

      - name: Generate Application Key
        run: php artisan key:generate

      - name: Generate Wayfinder Routes
        run: php artisan wayfinder:generate --with-form --no-interaction

      - name: Run Pint Check
        run: composer lint:check

      - name: Format Frontend Check
        run: pnpm run format:check

      - name: Lint Frontend Check
        run: pnpm run lint:check
```

- [ ] **Step 2: Update `.github/workflows/tests.yml`**

Set the workflow to:

```yaml
name: tests

on:
  push:
    branches:
      - dev
      - main
  pull_request:
    branches:
      - dev
      - main

permissions:
  contents: read

jobs:
  ci:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v6
        with:
          persist-credentials: false

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite
          coverage: none

      - name: Setup Node
        uses: actions/setup-node@v6
        with:
          node-version: '24'

      - name: Enable pnpm
        run: corepack enable pnpm

      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader

      - name: Install Node Dependencies
        run: pnpm install --frozen-lockfile --config.strict-dep-builds=false

      - name: Copy Environment File
        run: cp .env.example .env

      - name: Generate Application Key
        run: php artisan key:generate

      - name: Build Assets
        run: pnpm run build

      - name: Run Type Analysis
        run: |
          composer types:check
          pnpm run types:check

      - name: Tests
        run: php artisan test
```

- [ ] **Step 3: Add release-please workflow**

Create `.github/workflows/release-please.yml`:

```yaml
name: release-please

on:
  push:
    branches:
      - main
      - dev
  workflow_dispatch:

permissions:
  contents: write
  pull-requests: write

jobs:
  stable:
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - uses: googleapis/release-please-action@v4
        with:
          target-branch: main
          config-file: release-please-config.json
          manifest-file: .release-please-manifest.json

  prerelease:
    if: github.ref == 'refs/heads/dev'
    runs-on: ubuntu-latest
    steps:
      - uses: googleapis/release-please-action@v4
        with:
          target-branch: dev
          config-file: release-please-config.dev.json
          manifest-file: .release-please-manifest.dev.json
```

- [ ] **Step 4: Add Dependabot for GitHub Actions**

Create `.github/dependabot.yml`:

```yaml
version: 2
updates:
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
    cooldown:
      default-days: 5
    groups:
      github-actions:
        patterns:
          - "*"
```

- [ ] **Step 5: Validate workflow YAML shape**

Run:

```bash
git diff --check
```

Expected: PASS with no whitespace errors.

---

### Task 6: Full Verification and Cleanup

**Files:**
- Modify as needed only for mechanical fixes surfaced by verification.

**Interfaces:**
- Consumes: all changes from Tasks 1-5.
- Produces: a verified working tree ready for developer review.

- [ ] **Step 1: Run PHP formatter**

Run:

```bash
vendor/bin/pint --dirty --format agent
```

Expected: PASS; PHP files are formatted.

- [ ] **Step 2: Run frontend autofixers**

Run:

```bash
pnpm run lint
pnpm run format
```

Expected: PASS; frontend files are linted and formatted.

- [ ] **Step 3: Confirm Wayfinder is not required**

Run:

```bash
git diff --name-only
```

Expected: no files under `routes/*.php`, `app/Http/Controllers/**`, or invokable action classes changed. If any did change, run:

```bash
php artisan wayfinder:generate --with-form --no-interaction
```

- [ ] **Step 4: Run full CI check**

Run:

```bash
composer ci:check
```

Expected: GREEN.

- [ ] **Step 5: Review changed files**

Run:

```bash
git status --short
git diff --stat
```

Expected: changes are limited to planned config, dependency, workflow, version config, lockfile, and mechanical verification fixes.

- [ ] **Step 6: Prepare final handoff**

Report:

```text
Finishing gate:
[x] vendor/bin/pint --dirty --format agent
[x] pnpm run lint
[x] pnpm run format
[ ] php artisan wayfinder:generate --with-form --no-interaction (not needed: no routes/controllers/actions changed)
[x] composer ci:check → GREEN
[ ] Memory written: not needed: foundation sync only, no new durable domain convention
[ ] Code review run  or  skipped: skipped unless requested by developer
[ ] Commit requested by developer: no
```

If any gate fails after 2-3 focused fix rounds, stop and report the exact failing command, relevant error summary, and the smallest proposed next step.
