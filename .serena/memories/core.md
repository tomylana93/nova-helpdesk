# Core

- Laravel + Inertia Vue admin-style app with backend in `app/`, routes in `routes/`, Vue SPA in `resources/js/`, styles in `resources/css/app.css`, tests in `tests/`.
- `routes/web.php`: home redirects to `login` (guest) or `dashboard` (auth); dashboard under `auth`+`active`; temporary-uploads store/destroy; requires `routes/settings.php`, `routes/admin.php`, and `routes/helpdesk.php`.
- `routes/settings.php`: profile edit/update, security edit, password update under `auth`+`active`; security edit requires password confirmation; password update throttled `6,1`.
- `routes/admin.php`: admin panel under `auth`+`active` with prefix `admin` and name `admin.`; includes settings (general, style, password) and master-data (users, branches, departments, queues, ticket categories, SLA policies).
- `routes/helpdesk.php`: ticket operations, comments, and approvals/rejections under `auth`+`active`.
- Middleware stack: `HandleAppearance` (shares branding/theme/font to Blade), `HandleInertiaRequests` (shares name, locale, auth, style, branding, sidebarOpen), `SetApplicationLocale` (sets locale from `GeneralSettings`), `EnsureActiveUser` aliased as `active`.
- Auth is Fortify-backed with Inertia views; rejects non-active users at login and password reset.
- Multi-language support: `lang/en/` and `lang/id/`; locale driven by `GeneralSettings.site_locale`.
- Media library integrated on `User` model for avatar uploads.
- Read `mem:agent/context` for MCP/tool/skill routing, `mem:tech_stack` for packages, `mem:backend/core` for backend structure, `mem:backend/helpdesk` for helpdesk ticketing system, `mem:frontend/core` for frontend structure, `mem:conventions` for style rules, `mem:suggested_commands` for commands, and `mem:task_completion` before finishing changes.
