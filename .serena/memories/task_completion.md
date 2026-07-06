# Task Completion

- For any behavior change, add/update focused automated tests and run the smallest relevant command first (usually `php artisan test --compact <path>` or `php artisan test --compact --filter=<name>`).
- After PHP edits, run `vendor/bin/pint --dirty --format agent` before handoff.
- After frontend edits, run the minimal relevant subset of `pnpm run lint:check`, `pnpm run format:check`, and `pnpm run types:check`.
- If backend route/controller signatures used by frontend changed, regenerate/check Wayfinder output before validating frontend types.
  - Manual Wayfinder regen must use `php artisan wayfinder:generate --with-form --no-interaction` because `vite.config.ts` enables `wayfinder({ formVariants: true })`; a bare generate can drop `.form()` helpers and break Vue type checks.
- For broad or cross-domain changes, prefer `composer run ci:check`; if skipped, report what was run and what was intentionally not run.
- If UI updates do not appear, verify local assets with `pnpm run dev`, `pnpm run build`, or `composer run dev`.
- Do not mark work complete while behavior is unverified, partially implemented, or blocked by unresolved dependencies.