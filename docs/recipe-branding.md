# Recipe: Branding

The starter kit ships with a neutral **"Nova"** placeholder identity. Branding is data-driven, so a derived project overrides it without touching code.

## Where branding comes from

- **Site name** — `GeneralSettings.site_name` (default `'Nova'`). Shared to every Inertia page as `name`; used for the document `<title>` and the brand component.
- **Logo / icon / favicon** — `StyleSettings` holds the asset paths; `App\Support\Branding\BrandingAssetResolver` maps them to public URLs, falling back to the static placeholders under `public/assets/images/*`.
- **Theme / font / layout** — `StyleSettings` (`site_theme`, `site_font`, `site_layout`, `site_auth_layout`, `site_logo_style`).
- **Frontend consumer** — `resources/js/components/AppBrand.vue` is the single component that renders the logo/icon + name (icon-mode vs logo-mode, light/dark variants). It is wired into `AppLogo.vue`, `AppHeader.vue`, and the auth layouts.

> The placeholder assets in `public/assets/images/*` are an intentional neutral fallback. Replace them (or upload your own via Style Settings) in your project — they are not technical debt.

## Override branding (no code)

1. Sign in as an administrator with the **manage settings** permission.
2. **General Settings** → set the site name and description.
3. **Style Settings** → upload your logo, icon, and favicon, and pick theme/font/layout. Uploads use the temporary-upload → promote flow and are stored on the `public` disk.

## Override branding (defaults in code)

To change the shipped defaults for everyone:

- Site name / description defaults: `app/Settings/GeneralSettings.php` and the settings migration `database/settings/2026_06_17_000001_create_general_settings.php`.
- Fallback title: `resources/views/app.blade.php` and `config('app.name')` (set `APP_NAME` in `.env`).
- Replace the static fallback images in `public/assets/images/`.

## Theming

Color palettes are defined in `resources/css/app.css` as `html[data-theme=...]` (light + dark) and fonts as `html[data-font=...]`. The `useStyleSettings` composable applies `data-theme` / `data-font` and the font link at runtime; `useAppearance` (light/dark) is orthogonal to the `site_theme` palette.
