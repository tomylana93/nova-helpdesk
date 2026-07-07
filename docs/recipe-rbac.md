# Recipe: Roles & permissions

Access control is built on `spatie/laravel-permission` with two canonical enums and one data seam.

## The pieces

- `App\Enums\UserRole` — the roles (`super_admin`, `admin`, `user`).
- `App\Enums\Permission` — the permissions (e.g. `manage settings`, `view users`, …).
- `config/roles.php` — the **role → permission map** (the seam).
- `php artisan permission:sync-roles` — creates every role/permission from the enums and applies the map. It validates that every key is a known `UserRole` and every value a known `Permission`, throwing `InvalidArgumentException` otherwise.

## Add a permission

1. Add a case to `App\Enums\Permission` (the value is the permission name string).
2. Grant it to the relevant roles in `config/roles.php`:

   ```php
   UserRole::Admin->value => [
       Permission::ManageSettings->value,
       Permission::ViewUsers->value,
       // ... your new permission
   ],
   ```

3. Enforce it — in a Policy, controller (`$this->authorize(...)` / middleware), or a Gate check.
4. Re-sync:

   ```bash
   php artisan permission:sync-roles
   ```

5. (Optional) Expose it to the frontend for nav-gating by adding it to `auth.abilities` in `HandleInertiaRequests` and the `AuthAbilities` type in `resources/js/types/auth.ts`.

## Add a role

1. Add a case to `App\Enums\UserRole` and a `label()` arm (returns a `__('user.role.<value>')` translation key — add it to `lang/en/user.php` and `lang/id/user.php`).
2. Add the role to the map in `config/roles.php` with its permission list (use `'*'` for all permissions).
3. Update the `UserRoleName` union in `resources/js/types/auth.ts`.
4. Re-sync and update/add tests (see `tests/Feature/Console/SyncRolesConfigTest.php`).

## Notes

- `super_admin` is mapped to `'*'` (all permissions).
- Enums stay canonical and type-safe (used by policies, resources, middleware, factories). Only the **map** is data — that is the whole point of the seam.
