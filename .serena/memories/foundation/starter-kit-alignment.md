# Foundation Starter Kit Alignment

On 2026-07-07 the helpdesk foundation was aligned with sibling `nova-starter-kit` based on `docs/foundation-drift-audit.md` after the developer chose `adopt` for the listed drift items except keeping the existing 2FA foundation.

Durable conventions now in place:
- RBAC permission mappings live behind `config/roles.php`; superadmin role lives behind `config/superadmin.php`.
- Users include nullable unique `phone`, nullable `last_login_at`, and soft deletes; login tracking uses `forceFill(['last_login_at' => Date::now()])->saveQuietly()` from the login event.
- Admin user CRUD supports phone, soft delete, restore, and XLSX export.
- Settings profile updates go through `App\Actions\Settings\UpdateProfile`.
- Frontend icon imports should use the installed `@lucide/vue` package, not `lucide-vue-next`.
- Dashboard/realtime dependencies `@unovis/vue` and `@laravel/echo-vue` are installed because existing code imports them.
- Tests touched by foundation work should use named routes via `route(...)` rather than hardcoded request URLs.