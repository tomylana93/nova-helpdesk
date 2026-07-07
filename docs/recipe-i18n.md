# Recipe: Translations (i18n)

The app ships with English (`en`) and Indonesian (`id`). Backend strings use Laravel's standard PHP translation files; the frontend consumes the same strings via exported JSON.

## Source of truth

- `lang/en/*.php` and `lang/id/*.php` — backend translation files, grouped by domain (e.g. `user.php`, `admin.php`, `datatable.php`).
- Keep **key parity** between locales: every key present in `en` must exist in `id` and vice versa.
- The active locale comes from `GeneralSettings.site_locale` (global, applied per request by the `SetApplicationLocale` middleware).

## Add or change a string

1. Edit the relevant file under `lang/en/` and add the matching key under `lang/id/`.
2. Reference it in PHP with `__('user.role.admin')`.
3. Export the strings for the frontend:

   ```bash
   php artisan lang:export
   ```

   This runs `FrontendLocaleExporter`, producing `lang/{locale}.json`. These JSON files are **gitignored build artifacts** — regenerate them, don't hand-edit.

## Use a string in Vue

The `useTrans` composable replicates Laravel's translation engine (including `:placeholder` replacement) and reads the current locale from the shared page props:

```ts
import { useTrans } from '@/composables/useTrans';

const { __ } = useTrans();
const label = __('user.role.admin');
```

## Add a new locale

1. Create `lang/<locale>/` with the full set of files (mirror the keys from `en`).
2. Add the locale to the `App\Enums\SiteLocale` enum so it can be selected in General Settings.
3. Run `php artisan lang:export` and rebuild the frontend.
