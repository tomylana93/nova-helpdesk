# Tech Stack

- PHP `^8.5`; Laravel Framework `^13.7`; Fortify `^1.37`; Inertia Laravel `^3.0`; Wayfinder `^0.1`; Boost `^2.2`; Pest `^4.7`; PHPUnit via Pest stack.
- Frontend: Vue `^3.5`, `@inertiajs/vue3` `^3.0`, `@inertiajs/vite` `^3.0`, Vite `^8`, TypeScript `^5.2`, Tailwind CSS `^4.1`, Reka UI, lucide-vue-next, vue-sonner.
- Package managers in use: Composer for PHP, pnpm for Node. Lockfiles present: `composer.lock`, `pnpm-lock.yaml`.
- Vite plugins: `laravel-vite-plugin`, `@inertiajs/vite`, `@tailwindcss/vite`, Vue plugin, `@laravel/vite-plugin-wayfinder` with `formVariants: true`.
- TypeScript uses bundler module resolution, strict mode, `@/*` alias to `resources/js/*`, and includes Vue SFCs under `resources/js/**`.
- Test runtime uses in-memory SQLite via `phpunit.xml`; Feature tests use `RefreshDatabase` globally from `tests/Pest.php`.
- Verified package/runtime set in this workspace: Laravel 13.11, PHP 8.5, Fortify 1.37, Inertia Laravel 3.1, Vue 3.5, Tailwind 4, Wayfinder 0.1.x, ESLint 9, Prettier 3, Pint 1.29, PHPStan/Larastan 3.9, Rector 2.4, Pest 4.
- Frontend package manager is `pnpm@11.1.0`; project checks rely on `vue-tsc`, ESLint flat config, and Prettier for `resources/`.
- Additional backend packages: `spatie/laravel-settings` (typed settings persistence), `spatie/laravel-medialibrary` (media collections/conversions), `spatie/laravel-query-builder` (API filtering/sorting/includes).
