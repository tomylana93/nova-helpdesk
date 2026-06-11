# Conventions

- Follow `AGENTS.md` + Laravel Boost workflow: activate relevant domain skills, consult Boost docs before Laravel ecosystem changes, prefer framework generators, and keep edits aligned with sibling patterns.
- PHP style: Laravel Pint formatting, explicit parameter/return types, curly braces for all control structures, and PHPDoc for non-trivial typing/context.
- Keep Laravel structure conventional: controllers in `app/Http/Controllers` (keep controllers thin, offload business/persistence logic to Action classes, and avoid defining private helper methods inside controllers), requests in `app/Http/Requests`, concerns in `app/Concerns`, and established route split under `routes/`.
- Inertia/Vue conventions: pages in `resources/js/pages`, single root element per component, layout wiring centralized in `resources/js/app.ts`.
- Always use generated Wayfinder helpers from `@/actions` and `@/routes`; do not hand-edit generated files under `resources/js/actions/**`, `resources/js/routes/**`, or `resources/js/wayfinder/**`.
- Frontend TypeScript uses strict mode with `@/*` alias and ESLint import-order/type-import rules.
- Frontend formatting uses Prettier (single quotes, semicolons, 4-space indentation, Tailwind class sorting).
- Auth behavior is status-based (`UserStatus` + `active` middleware); Fortify optional feature currently enabled is reset password only.
- Prefer Pest feature tests (with `RefreshDatabase` where applicable) for behavior-level verification.
- Follow Conventional Commits and manage Semantic Versioning using `mem:versioning`.