# Backend Core

- Routes are split in `routes/web.php`, `routes/settings.php`, `routes/admin.php`, and `routes/helpdesk.php`. The last three are required from `routes/web.php`.
- `FortifyServiceProvider` registers Inertia auth views (`auth/Login`, `auth/ForgotPassword`, `auth/ResetPassword`, `auth/ConfirmPassword`).
- Auth gate is status-based: login rejects non-active users; `EnsureActiveUser` middleware (alias `active`) logs out inactive users on protected routes.
- Fortify optional features currently enabled in config: reset passwords only.
- `User::sendPasswordResetNotification()` suppresses reset notifications for inactive users.
- `HandleAppearance` shares appearance/site branding data to Blade; `HandleInertiaRequests` shares settings-derived props (`name`, `locale`, `auth.user`, `auth.abilities`, `style`, `branding`, `sidebarOpen`) to Inertia.
- `SetApplicationLocale` applies locale from `GeneralSettings.site_locale`.
- Action pattern: `app/Actions/{Domain}` with public `handle(...)`; existing domains include `Fortify`, `Settings`, `MasterData`, `Uploads`, and `Helpdesk`.
- Table pattern uses `app/Tables/AbstractTable` with `spatie/laravel-query-builder` for deferred Inertia datatables.
- Settings pattern uses `app/Settings/{Group}` (`general`, `style`, `password`) via `spatie/laravel-settings`.
- Read `mem:backend/admin` for admin/settings/master-data behavior, `mem:backend/helpdesk` for ticketing core, and `mem:backend/uploads-media` for temporary uploads + media library flow.
- Read `mem:backend/helpdesk-audit` before changing ticket approval, ticket comments, or ticket relationship validation; it records current unresolved authorization/integrity risks in the helpdesk flow.
