# Foundation Drift Refactor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Align `nova-helpdesk` with the approved `docs/foundation-drift-audit.md` decisions while preserving helpdesk-specific domain behavior.

**Architecture:** Treat `nova-starter-kit` as the upstream reference, but adapt its foundation patterns to helpdesk domain concepts. Keep helpdesk-only ticketing/assets/SLA features intact; only adopt approved foundation seams, auth/user capabilities, CI/tooling, tests, docs, and limited frontend type/UI baseline changes.

**Tech Stack:** Laravel 13, PHP 8.5, Fortify, Inertia v3, Vue 3, Wayfinder, Spatie Permission, Spatie Media Library, Maatwebsite Excel, Pest 4, Tailwind v4, shadcn-vue.

## Global Constraints

- Do not overwrite helpdesk domain code with starter kit code wholesale.
- Use `AdminPermission` and helpdesk `UserRole`; do not replace them with starter kit's smaller `Permission` enum.
- TDD is required for behavior changes: write the failing Pest test first, verify RED, implement, verify GREEN.
- Use Laravel generators for new migrations/tests where practical.
- Use Wayfinder helpers; regenerate Wayfinder when routes/controllers/actions change.
- Do not import `.env*`, `.serena/cache`, generated Wayfinder files by hand, or local database files from the starter kit.
- Preserve existing user changes in the dirty worktree; inspect before editing any already modified file.

---

## Approved Decisions

- ADOPT: `shouldRenderJsonWhen(api/*)`.
- ADOPT: session cookie fallback variant `nova-helpdesk`.
- ADOPT: CI `staging` branch trigger.
- ADOPT: `.githooks/pre-push` after inspecting hook content.
- ADOPT: RBAC config seam with helpdesk permission enum.
- ADOPT: user `phone`, `softDeletes`, `last_login_at`, delete/restore actions.
- ADOPT: profile update Action.
- ADOPT: users export.
- KEEP: 2FA request/foundation.
- ADOPT: shadcn/UI baseline drift audit and selected alignment.
- ADOPT: TypeScript foundation types affected by user/auth/export.
- ADOPT: starter-only foundation tests where relevant.
- ADOPT: starter docs recipes.
- ADOPT: `.editorconfig` and `deploy.sh` alignment; skip `.env*`.

## Files Map

- Bootstrap/config/workflows: `bootstrap/app.php`, `config/session.php`, `.github/workflows/lint.yml`, `.github/workflows/tests.yml`, `.editorconfig`, `deploy.sh`, `.githooks/pre-push`.
- RBAC seam: `config/roles.php`, `config/superadmin.php`, `app/Console/Commands/SyncRolesCommand.php`, `database/seeders/DatabaseSeeder.php`, role tests.
- User/auth backend: `database/migrations/*users*`, `app/Models/User.php`, `database/factories/UserFactory.php`, `app/Actions/MasterData/Users/*.php`, `app/Http/Controllers/Admin/MasterData/UserController.php`, `app/Http/Requests/Admin/MasterData/*UserRequest.php`, `app/Http/Resources/UserResource.php`, `app/Policies/UserPolicy.php`, `routes/admin.php`, `app/Providers/AppServiceProvider.php`.
- Profile backend: `app/Actions/Settings/UpdateProfile.php`, `app/Http/Controllers/Settings/ProfileController.php`, `tests/Feature/Settings/ProfileTest.php` or existing sibling test.
- Export users: `app/Exports/UsersExport.php`, `app/Http/Requests/Admin/MasterData/ExportUserRequest.php`, `app/Http/Controllers/Admin/MasterData/UserController.php`, `routes/admin.php`, frontend export link if existing page pattern supports it.
- Upload testing: `database/factories/TemporaryUploadFactory.php`, `app/Models/TemporaryUpload.php`, relevant upload tests.
- Frontend: `resources/js/types/auth.ts`, `resources/js/types/user.ts`, `resources/js/types/index.ts`, selected `resources/js/components/ui/**`, user create/edit/show pages if phone/export/restore UI requires it.
- Docs/tests: `docs/architecture.md`, `docs/recipe-*.md`, selected tests from `nova-starter-kit/tests/**`.

---

### Task 1: Quick Foundation Fixes

**Files:**
- Modify: `bootstrap/app.php`
- Modify: `config/session.php`
- Modify: `.github/workflows/lint.yml`
- Modify: `.github/workflows/tests.yml`
- Modify: `.editorconfig`
- Modify: `deploy.sh`
- Create: `.githooks/pre-push` if the starter hook is generic and safe
- Test: `tests/Feature/Foundation/ApiExceptionRenderingTest.php`

**Interfaces:**
- Produces: `api/*` requests prefer JSON error rendering.
- Produces: `SESSION_COOKIE` fallback uses `nova-helpdesk-session`.
- Produces: CI runs on `dev`, `staging`, and `main`.

- [ ] **Step 1: Write the failing API exception rendering test**

Create `tests/Feature/Foundation/ApiExceptionRenderingTest.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

it('renders api route exceptions as json by default', function () {
    Route::get('/api/foundation-drift-test', static function (): void {
        abort(418, 'Foundation drift test');
    });

    $this->get('/api/foundation-drift-test')
        ->assertStatus(418)
        ->assertHeader('content-type', 'application/json')
        ->assertJsonPath('message', 'Foundation drift test');
});
```

- [ ] **Step 2: Verify RED**

Run: `php artisan test --compact tests/Feature/Foundation/ApiExceptionRenderingTest.php`

Expected: FAIL because `/api/*` exceptions are not forced to JSON when no `Accept: application/json` header is sent.

- [ ] **Step 3: Implement quick fixes**

In `bootstrap/app.php`, import `Illuminate\Http\Request` and restore:

```php
$exceptions->shouldRenderJsonWhen(
    fn (Request $request) => $request->is('api/*'),
);
```

In `config/session.php`, set the fallback app name used in the cookie slug to `nova-helpdesk`.

In both workflow files, add `staging` to `on.push.branches` and `on.pull_request.branches`.

Compare `.editorconfig`, `deploy.sh`, and `.githooks/pre-push` against `/home/tomylana93/projects/nova-starter-kit`; adopt only generic non-secret improvements.

- [ ] **Step 4: Verify GREEN**

Run: `php artisan test --compact tests/Feature/Foundation/ApiExceptionRenderingTest.php`

Expected: PASS.

---

### Task 2: RBAC Config Seam

**Files:**
- Create: `config/roles.php`
- Create: `config/superadmin.php`
- Modify: `app/Console/Commands/SyncRolesCommand.php`
- Modify: `database/seeders/DatabaseSeeder.php` if it duplicates role-permission maps
- Test: `tests/Feature/Console/SyncRolesCommandTest.php`

**Interfaces:**
- Produces: `config('roles.permissions')` mapping role names to `AdminPermission` values.
- Produces: `config('superadmin.role')` or equivalent central source for the super admin role.
- Consumes: `App\Enums\AdminPermission` and `App\Enums\UserRole`.

- [ ] **Step 1: Write failing role seam tests**

Create or update `tests/Feature/Console/SyncRolesCommandTest.php`:

```php
<?php

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use Spatie\Permission\Models\Role;

it('syncs role permissions from config roles seam', function () {
    config()->set('roles.permissions', [
        UserRole::ItAgent->value => [
            AdminPermission::ViewTickets->value,
        ],
    ]);

    $this->artisan('permission:sync-roles')->assertSuccessful();

    expect(Role::findByName(UserRole::ItAgent->value)->permissions()->pluck('name')->all())
        ->toBe([AdminPermission::ViewTickets->value]);
});
```

- [ ] **Step 2: Verify RED**

Run: `php artisan test --compact tests/Feature/Console/SyncRolesCommandTest.php --filter='syncs role permissions from config roles seam'`

Expected: FAIL because `SyncRolesCommand` currently uses an inline role map.

- [ ] **Step 3: Implement RBAC seam**

Create `config/roles.php` with the current helpdesk role-permission mapping:

```php
<?php

use App\Enums\AdminPermission;
use App\Enums\UserRole;

return [
    'permissions' => [
        UserRole::SuperAdmin->value => array_map(
            static fn (AdminPermission $permission): string => $permission->value,
            AdminPermission::cases(),
        ),
        UserRole::ItAgent->value => [
            AdminPermission::ViewTickets->value,
            AdminPermission::CreateTickets->value,
            AdminPermission::UpdateTickets->value,
            AdminPermission::ManageApprovals->value,
            AdminPermission::ViewReports->value,
        ],
        UserRole::Auditor->value => [
            AdminPermission::ViewTickets->value,
            AdminPermission::CreateTickets->value,
            AdminPermission::ViewReports->value,
        ],
        UserRole::Requester->value => [
            AdminPermission::ViewTickets->value,
            AdminPermission::CreateTickets->value,
        ],
    ],
];
```

Create `config/superadmin.php`:

```php
<?php

use App\Enums\UserRole;

return [
    'role' => UserRole::SuperAdmin->value,
];
```

Update `SyncRolesCommand::syncRolePermissions()` to iterate `config('roles.permissions')`.

- [ ] **Step 4: Verify GREEN**

Run: `php artisan test --compact tests/Feature/Console/SyncRolesCommandTest.php`

Expected: PASS.

---

### Task 3: User Schema, Model, Factory, and Login Tracking

**Files:**
- Create: new migration adding `phone`, `last_login_at`, `deleted_at` to `users`
- Modify: `app/Models/User.php`
- Modify: `database/factories/UserFactory.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/Auth/LoginTrackingTest.php`
- Test: `tests/Feature/Admin/MasterData/UserFoundationTest.php`

**Interfaces:**
- Produces: nullable unique `users.phone`, nullable `users.last_login_at`, nullable `users.deleted_at`.
- Produces: `User` supports `SoftDeletes`, casts `last_login_at` to datetime, and fillable includes `phone`.
- Produces: login updates `last_login_at`.

- [ ] **Step 1: Write failing login tracking test**

Create `tests/Feature/Auth/LoginTrackingTest.php`:

```php
<?php

use App\Models\User;
use Illuminate\Support\Facades\Date;

it('records the user last login timestamp', function () {
    Date::setTestNow(now());

    $user = User::factory()->create([
        'password' => 'password',
        'last_login_at' => null,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    expect($user->refresh()->last_login_at?->equalTo(now()))->toBeTrue();
});
```

- [ ] **Step 2: Write failing schema/model test**

Create `tests/Feature/Admin/MasterData/UserFoundationTest.php`:

```php
<?php

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

it('has starter-aligned user foundation columns and model traits', function () {
    expect(Schema::hasColumns('users', ['phone', 'last_login_at', 'deleted_at']))->toBeTrue();
    expect(class_uses_recursive(User::class))->toContain(SoftDeletes::class);
});
```

- [ ] **Step 3: Verify RED**

Run: `php artisan test --compact tests/Feature/Auth/LoginTrackingTest.php tests/Feature/Admin/MasterData/UserFoundationTest.php`

Expected: FAIL because the columns and login listener do not exist.

- [ ] **Step 4: Implement migration/model/listener**

Use: `php artisan make:migration add_foundation_user_fields_to_users_table --table=users --no-interaction`

Migration `up()` adds:

```php
$table->string('phone')->nullable()->unique()->after('email');
$table->timestamp('last_login_at')->nullable()->after('must_change_password');
$table->softDeletes();
```

Migration `down()` drops the unique index and columns.

Update `User`:
- add `SoftDeletes`
- add `phone` to fillable
- add `last_login_at` to casts
- add PHPDoc properties as needed

Update `AppServiceProvider` to listen for `Illuminate\Auth\Events\Login` and save `last_login_at = Date::now()`.

Update `UserFactory` to generate nullable/unique phone if the existing factory pattern supports it.

- [ ] **Step 5: Verify GREEN**

Run: `php artisan test --compact tests/Feature/Auth/LoginTrackingTest.php tests/Feature/Admin/MasterData/UserFoundationTest.php`

Expected: PASS.

---

### Task 4: User CRUD Delete/Restore, Phone Validation, and Resources

**Files:**
- Create: `app/Actions/MasterData/Users/DeleteUser.php`
- Create: `app/Actions/MasterData/Users/RestoreUser.php`
- Modify: `app/Actions/MasterData/Users/CreateUser.php`
- Modify: `app/Actions/MasterData/Users/UpdateUser.php`
- Modify: `app/Http/Requests/Admin/MasterData/StoreUserRequest.php`
- Modify: `app/Http/Requests/Admin/MasterData/UpdateUserRequest.php`
- Modify: `app/Http/Resources/UserResource.php`
- Modify: `app/Http/Controllers/Admin/MasterData/UserController.php`
- Modify: `routes/admin.php`
- Modify: `app/Policies/UserPolicy.php`
- Test: `tests/Feature/Admin/MasterData/UserManagementTest.php` or existing user management tests

**Interfaces:**
- Produces: `DELETE admin/master-data/users/{user}` soft-deletes users.
- Produces: `POST admin/master-data/users/{user}/restore` restores trashed users.
- Produces: create/update accepts optional unique `phone`.
- Produces: show/edit/update/destroy can bind trashed users where needed.

- [ ] **Step 1: Write failing phone create/update tests**

Add tests proving an admin can create/update user phone and duplicate phone is rejected.

Run affected test file with `php artisan test --compact ... --filter=phone`.

Expected: FAIL before request/action/resource changes.

- [ ] **Step 2: Write failing soft-delete/restore tests**

Add tests proving delete soft-deletes and restore restores.

Run affected test file with `php artisan test --compact ... --filter='delete|restore'`.

Expected: FAIL because routes/actions do not exist.

- [ ] **Step 3: Implement phone flow**

Add `phone` to profile validation rules or request-specific user rules, matching starter uniqueness behavior while preserving helpdesk branch/department rules.

Include `phone` in `userData()`, create/update actions, `UserResource`, and frontend user type fields.

- [ ] **Step 4: Implement delete/restore flow**

Mirror starter action structure but keep helpdesk policy names and flash messages.

Update routes:

```php
Route::resource('users', UserController::class)->only([
    'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
])->withTrashed(['show', 'edit', 'update', 'destroy']);

Route::post('users/{user}/restore', [UserController::class, 'restore'])
    ->withTrashed()
    ->name('users.restore');
```

Add controller `destroy()` and `restore()` methods.

- [ ] **Step 5: Verify GREEN**

Run affected user management tests.

Expected: PASS.

---

### Task 5: Profile Update Action

**Files:**
- Create: `app/Actions/Settings/UpdateProfile.php`
- Modify: `app/Http/Controllers/Settings/ProfileController.php`
- Test: existing or new `tests/Feature/Settings/ProfileTest.php`

**Interfaces:**
- Produces: `UpdateProfile::handle(User $user, array $data, ?string $avatarUploadId, bool $avatarRemove): void`
- Consumes: `TemporaryUpload` and media library avatar collection.

- [ ] **Step 1: Write/update profile test**

Add or update a test proving profile update persists basic fields and avatar behavior still works.

Run: `php artisan test --compact tests/Feature/Settings/ProfileTest.php`

Expected: existing inline controller behavior passes before refactor; if so, keep it as characterization coverage.

- [ ] **Step 2: Extract Action**

Move the transaction/media logic from `ProfileController::update()` into `UpdateProfile`.

Keep controller thin:

```php
$updateProfile->handle(
    $request->user(),
    $request->validated(),
    $request->string('avatar_upload_id')->toString() ?: null,
    $request->boolean('avatar_remove'),
);
```

- [ ] **Step 3: Verify GREEN**

Run profile tests.

Expected: PASS.

---

### Task 6: Users Export

**Files:**
- Create: `app/Exports/UsersExport.php`
- Create: `app/Http/Requests/Admin/MasterData/ExportUserRequest.php`
- Modify: `app/Http/Controllers/Admin/MasterData/UserController.php`
- Modify: `routes/admin.php`
- Modify: frontend user index page if it already has action button pattern
- Test: `tests/Feature/Admin/MasterData/UserExportTest.php`

**Interfaces:**
- Produces: `GET admin/master-data/users/export` named `admin.master-data.users.export`.
- Produces: Excel download with helpdesk user fields: name, email, phone, role, status, branch, department, last login.

- [ ] **Step 1: Write failing export test**

Create test asserting authorized admin receives a successful XLSX download from `admin.master-data.users.export`.

Run: `php artisan test --compact tests/Feature/Admin/MasterData/UserExportTest.php`

Expected: FAIL because route/controller/export does not exist.

- [ ] **Step 2: Implement export**

Adapt starter `UsersExport` but include helpdesk relationships (`branch`, `department`) and new user fields.

Add `ExportUserRequest` authorization with `viewAny`.

Add route before resource route:

```php
Route::get('users/export', [UserController::class, 'export'])->name('users.export');
```

- [ ] **Step 3: Verify GREEN**

Run export test.

Expected: PASS.

---

### Task 7: TemporaryUpload Factory

**Files:**
- Create: `database/factories/TemporaryUploadFactory.php`
- Modify: `app/Models/TemporaryUpload.php`
- Test: existing temporary upload tests or new `tests/Feature/TemporaryUploadFactoryTest.php`

**Interfaces:**
- Produces: `TemporaryUpload::factory()` with valid `user_id`, disk, path, original name, mime type, and size.

- [ ] **Step 1: Write failing factory test**

Create a test that calls `TemporaryUpload::factory()->create()` and asserts the model exists.

Run: `php artisan test --compact tests/Feature/TemporaryUploadFactoryTest.php`

Expected: FAIL because the dedicated factory does not exist.

- [ ] **Step 2: Implement factory**

Copy/adapt starter `TemporaryUploadFactory`, then update `TemporaryUpload` docblock to `@use HasFactory<TemporaryUploadFactory>`.

- [ ] **Step 3: Verify GREEN**

Run factory test.

Expected: PASS.

---

### Task 8: Frontend Types and Selected UI Baseline

**Files:**
- Modify: `resources/js/types/auth.ts`
- Modify: `resources/js/types/user.ts`
- Modify: `resources/js/types/index.ts`
- Modify: user create/edit/show/index pages only where backend fields/routes require it
- Modify: selected `resources/js/components/ui/**` only after comparing against starter and preserving helpdesk usage
- Test: `pnpm run types:check`

**Interfaces:**
- Produces: frontend `User` types include `phone`, `last_login_at`, and soft-delete metadata if resource exposes it.
- Produces: user pages can submit/display `phone` and expose export/delete/restore controls where backend routes exist.

- [ ] **Step 1: Update types from backend contract**

After backend resources are updated, adjust TS types to match `UserResource`.

- [ ] **Step 2: Update user pages minimally**

Add `phone` field/display and export/delete/restore controls only where existing page patterns make the action discoverable.

- [ ] **Step 3: Audit shadcn UI baseline**

Compare starter `resources/js/components/ui/**` with helpdesk. Adopt only generic component fixes that do not regress helpdesk pages. Do not overwrite domain styling.

- [ ] **Step 4: Verify frontend**

Run: `pnpm run types:check`

Expected: PASS.

---

### Task 9: Foundation Tests and Docs Recipes

**Files:**
- Create/copy selected foundation tests from starter, adapted to helpdesk:
  - active user middleware
  - translation parity
  - settings behavior if still relevant
  - console command behavior
- Create: `docs/architecture.md`
- Create: `docs/recipe-branding.md`
- Create: `docs/recipe-crud.md`
- Create: `docs/recipe-i18n.md`
- Create: `docs/recipe-rbac.md`

**Interfaces:**
- Produces: helpdesk keeps useful starter foundation documentation and coverage.

- [ ] **Step 1: Copy docs recipes**

Copy starter docs recipes verbatim only if they are generic. If a recipe mentions starter-specific names, adapt names to `nova-helpdesk` or add a short note that it is inherited from the starter baseline.

- [ ] **Step 2: Add foundation tests**

Port starter-only foundation tests that still match helpdesk architecture. Skip 2FA-specific tests because decision is KEEP.

- [ ] **Step 3: Verify tests**

Run the newly added/adapted test files.

Expected: PASS.

---

### Task 10: Generated Files, Formatting, and Completion Gate

**Files:**
- Generated: `resources/js/actions/**`
- Generated: `resources/js/routes/**`
- Generated: `resources/js/wayfinder/**`
- Potentially generated: `lang/*.json`

**Interfaces:**
- Produces: frontend route/action helpers match backend route changes.
- Produces: formatted PHP and frontend resources.

- [ ] **Step 1: Regenerate Wayfinder**

Run: `php artisan wayfinder:generate --with-form --no-interaction`

Expected: generated files update for user export/delete/restore routes.

- [ ] **Step 2: Export lang if translations changed**

Run: `php artisan lang:export` only if lang files changed.

- [ ] **Step 3: Run format/lint**

Run:

```bash
vendor/bin/pint --dirty --format agent
pnpm run lint
pnpm run format
```

- [ ] **Step 4: Run focused backend/frontend checks**

Run:

```bash
php artisan test --compact tests/Feature/Foundation tests/Feature/Auth tests/Feature/Admin/MasterData tests/Feature/Settings
pnpm run types:check
```

- [ ] **Step 5: Run outer gate**

Run:

```bash
composer ci:check
```

Expected: GREEN.

- [ ] **Step 6: Memory decision**

Write a Serena memory only if the RBAC config seam or foundation sync workflow becomes a durable convention not already captured.

---

## Self-Review

- Spec coverage: all approved audit decisions are represented. 2FA, env files, generated/cache files, release metadata, and helpdesk-only domain code are intentionally excluded.
- Placeholder scan: no `TBD`/`TODO` placeholders remain; each task has concrete files, commands, and expected outcomes.
- Type consistency: RBAC keeps `AdminPermission`/`UserRole`; user fields are `phone`, `last_login_at`, `deleted_at`; Wayfinder regeneration is included after route/controller changes.
