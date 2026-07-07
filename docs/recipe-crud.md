# Recipe: Add a CRUD module

This walks through adding a new resource (say, `Project`) with the same data-table pattern used by user management. See [architecture](architecture.md) for the layering rationale.

## 1. Model, migration, factory

```bash
php artisan make:model Project -mf
```

Fill in the migration columns and the factory, then migrate:

```bash
php artisan migrate
```

## 2. Actions (use cases)

Create one invokable action per write operation:

```bash
php artisan make:action MasterData/Projects/CreateProject
php artisan make:action MasterData/Projects/UpdateProject
```

Each action receives a typed payload and performs exactly one job. Keep business logic here, not in the controller.

## 3. Table

Generate a table class and extend the pattern from `app/Tables/MasterData/UserTable.php`:

```bash
php artisan make:table MasterData/ProjectTable
```

`AbstractTable` (built on `spatie/laravel-query-builder`) gives you query, filtering, sorting, and pagination. Override:

- `query(): Builder` — the base query (select only the columns you render; eager-load relations).
- `defaultSort(): string|AllowedSort|null` — e.g. `'-created_at'`.
- `filterConfigurations(): array` — use the `searchFilter()` / `selectFilter()` helpers. Enum filters pair naturally with `YourEnum::options()` (via the `HasOptions` concern).
- `allowedSorts(): array` — whitelist sortable columns with `AllowedSort::field(...)`.
- `row($model): array` — shape a single row for the frontend (map enum values to `->label()` for display).

## 4. FormRequests

Create `Store`/`Update` requests. Add a strict `payload(): array` accessor with an `@return array{...}` shape and call `$request->payload()` from the controller — this keeps PHPStan level 7 happy.

## 5. Controller + routes

Create a resourceful controller that authorizes via a Policy, delegates writes to your Actions, and returns Inertia pages. Register routes in `routes/web.php` under the appropriate middleware (`auth`, `active`, permission checks).

## 6. Policy

```bash
php artisan make:policy ProjectPolicy --model=Project
```

Back the policy methods with `Permission` enum cases (see [recipe-rbac](recipe-rbac.md)).

## 7. Vue pages

Add pages under `resources/js/pages/admin/master-data/projects/` (`Index`, `Create`, `Edit`, `Show`). Reuse the existing data-table components and `PageWrapper`. Import typed endpoints from `@/actions` / `@/routes`.

## 8. Regenerate types & test

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact
```

Write a feature test mirroring `tests/Feature/Admin/MasterData/UserManagementTest.php`.
