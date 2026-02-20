# Project Overview

- Name: `nova-helpdesk` (Laravel 12 + Inertia Vue starter-based app).
- Purpose: web helpdesk-style application scaffolded from Laravel Vue starter kit with authentication/profile settings and dashboard flows.
- Backend stack: PHP 8.5, Laravel 12, Inertia Laravel v2, Fortify, Wayfinder.
- Frontend stack: Vue 3 + TypeScript, Inertia Vue 3, Vite, Tailwind CSS 4, Reka UI/lucide.
- Testing stack: Pest 4 (Feature tests use RefreshDatabase), PHPUnit 12.
- Main route entrypoints:
    - `/` -> redirects to `/login`
    - `/dashboard` -> Inertia page `Dashboard` (auth + verified)
    - account/settings-related endpoints are at root paths: `/profile`, `/password`, `/appearance`, `/two-factor`.
- High-level structure:
    - `app/` domain logic, controllers, models, actions.
    - `routes/` web/console routes (`settings` routes are consolidated in `routes/web.php`).
    - `resources/js/` Inertia app: `pages`, `components`, `layouts`, `actions`, `routes`, `composables`.
    - `tests/Feature`, `tests/Unit`.
    - tooling config in `composer.json`, `package.json`, `pint.json`, `eslint.config.js`, `.prettierrc`.
