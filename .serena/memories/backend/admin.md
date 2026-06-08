# Backend / Admin

- `routes/admin.php` under `auth` + `active`, prefix `admin`, name `admin.`.
- Settings routes under `admin/settings`: index inertia page (`admin/settings/Index`), general edit/update, style edit/update, password edit/update. Password routes use `RequirePassword` middleware.
- Settings controllers: `app/Http/Controllers/Admin/Settings/GeneralSettingsController`, `StyleSettingsController`, `PasswordSettingsController`.
- Settings actions: `app/Actions/Settings/UpdateGeneralSettings`, `UpdateStyleSettings`, `UpdatePasswordSettings`.
- Settings requests: `app/Http/Requests/Admin/Settings/Update{General,Style,Password}SettingsRequest`.
- Settings classes (`app/Settings/*`): `GeneralSettings` (site_name, site_description, site_locale), `StyleSettings` (theme, font, layout, auth_layout, logo_style, asset paths), `PasswordSettings` (default_user_password, encrypted via `encrypted()`).
- Master data under `admin/master-data`: index inertia page (`admin/master-data/Index`), users resource (index, create, store, show, edit, update).
- User CRUD: `UserController` (`app/Http/Controllers/Admin/MasterData/`), actions `CreateUser`/`UpdateUser` (`app/Actions/MasterData/Users/`), requests `StoreUserRequest`/`UpdateUserRequest` (`app/Http/Requests/Admin/MasterData/`), table `app/Tables/MasterData/UserTable`.
- Admin authorization uses Spatie Permission + Laravel policies. Current permission enum is `app/Enums/AdminPermission.php` with `manage settings`, `view users`, `create users`, `update users`.
- Role catalog lives in `app/Enums/UserRole.php`; current admin-facing special roles are `super_admin` and `admin` plus cargo/logistics domain roles used in user management.
- `app/Console/Commands/SyncRolesCommand.php` (`permission:sync-roles`) is the source of truth for creating roles/permissions and assigning the current admin permission matrix. It grants all current admin permissions to role `admin`, leaves `super_admin` to Gate bypass, and does not delete extra DB roles/permissions.
- `AppServiceProvider` registers `Gate::before` so `super_admin` bypasses policy checks, and binds settings classes to `AdminSettingsPolicy`.
- `UserPolicy` gates user index/create/show/edit/update; settings controllers and settings update requests authorize against `AdminSettingsPolicy`; user create/update requests authorize via policy-aware `authorize()` methods.
- Frontend auth UI should use shared `auth.abilities` booleans (`manage_settings`, `view_users`, `create_users`, `update_users`) from `HandleInertiaRequests` to hide admin navigation/cards/actions, but backend policy checks remain authoritative.
- `app/Tables/MasterData/UserTable` eager-loads user roles and returns `role` + `roleLabel` in row payloads; the admin users index renders a dedicated Role column.
- `app/Http/Resources/UserResource.php` centralizes user payload shaping for show/edit pages and includes the single active role string under `role`.
- `database/seeders/UserSeeder.php` seeds three sample users (`active`, `disabled`, `suspended`) and assigns stable example roles (`admin`, `dispatcher`, `compliance_officer`).
- Style enums: `SiteTheme`, `SiteFont`, `SiteLayout`, `SiteAuthLayout`, `SiteLogoStyle`.
