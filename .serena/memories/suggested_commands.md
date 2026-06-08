# Suggested Commands

- Fresh setup: `composer run setup`
- Full local stack (Laravel server, queue, pail, Vite): `composer run dev`
- Vite only: `pnpm run dev`
- Production frontend build: `pnpm run build`
- SSR build: `pnpm run build:ssr`
- Run all tests (with config clear + Pint check): `composer test`
- Focused tests: `php artisan test --compact`, or `php artisan test --compact --filter=<name>`, or `php artisan test --compact <test-path>`
- PHP formatter after edits: `vendor/bin/pint --dirty --format agent`
- PHP lint/analysis/refactor checks: `composer lint:check`, `composer analyse`, `composer refactor:check`
- Frontend checks: `pnpm run lint:check`, `pnpm run format:check`, `pnpm run types:check`
- Frontend auto-fix: `pnpm run lint`; formatting fix: `pnpm run format`
- Aggregate CI-style check: `composer run ci:check`
- Useful inspection: `php artisan route:list --except-vendor`, `php artisan config:show <key>`, `git status --short`, `rg -n <pattern> app resources/js routes tests`
- Optional automated PHP rewrite before final formatting: `composer refactor`