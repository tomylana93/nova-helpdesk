# Frontend / Localization

- Source of truth: PHP language files under `lang/{en,id}/`. Keep matching key structures between English and Indonesian; `tests/Feature/TranslationParityTest.php` enforces parity.
- Exporter: `php artisan lang:export` runs `App\Support\Localization\FrontendLocaleExporter` and writes generated `lang/{en,id}.json` assets for Vue. JSON exports are generated artifacts; do not hand-edit them.
- Vite wiring: `vite.config.ts` registers `laravelLangExport()` before the Laravel/Inertia plugins; it runs `lang:export` during build and watches `lang/**/*.php` during dev so frontend translations stay fresh.
- Frontend consumption: `resources/js/composables/useTrans.ts` loads exported JSON via `import.meta.glob` and reads `SharedPageProps.locale`; arrays/options built from translations should be `computed()` so locale changes are reactive.
- CLI shortcut: `pnpm run lang:export` delegates to `php artisan lang:export`.
- Related memories: app shell/locale settings in `mem:frontend/core` and `mem:backend/core`; completion commands in `mem:task_completion`.