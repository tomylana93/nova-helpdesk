<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Settings\PasswordSettings;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('admin users can be created with active status by default', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::ItAgent->value, 'web');
    $branch = Branch::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->post(route('admin.master-data.users.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.test',
            'role' => UserRole::ItAgent->value,
            'branch_id' => $branch->id,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.users.index'));

    $createdUser = User::query()
        ->where('email', 'new-user@example.test')
        ->first();

    expect($createdUser)->not->toBeNull();
    expect($createdUser?->status)->toBe(UserStatus::Active);
    expect(Hash::check(app(PasswordSettings::class)->default_user_password, (string) $createdUser?->password))->toBeTrue();
    expect($createdUser?->getRoleNames()->all())->toBe([UserRole::ItAgent->value]);
    expect($createdUser?->branch_id)->toBe($branch->id);
});

test('admin users can be updated with unchanged email for the edited user', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::ItAgent->value, 'web');
    Role::findOrCreate(UserRole::Requester->value, 'web');
    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);

    $targetUser = User::factory()->create([
        'email' => 'target-user@example.test',
        'status' => UserStatus::Active,
        'branch_id' => $branch->id,
    ]);
    $targetUser->assignRole(UserRole::ItAgent->value);

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.users.edit', $targetUser))
        ->put(route('admin.master-data.users.update', $targetUser), [
            'name' => 'Updated Target',
            'email' => 'target-user@example.test',
            'status' => UserStatus::Suspend->value,
            'role' => UserRole::Requester->value,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.users.index'));

    $targetUser->refresh();

    expect($targetUser->name)->toBe('Updated Target');
    expect($targetUser->status)->toBe(UserStatus::Suspend);
    expect($targetUser->getRoleNames()->all())->toBe([UserRole::Requester->value]);
});

test('status is required when updating admin users', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch = Branch::factory()->create();
    $targetUser = User::factory()->create([
        'branch_id' => $branch->id,
    ]);
    Role::findOrCreate(UserRole::ItAgent->value, 'web');

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.users.edit', $targetUser))
        ->put(route('admin.master-data.users.update', $targetUser), [
            'name' => 'Updated Target',
            'email' => $targetUser->email,
            'role' => UserRole::ItAgent->value,
            'branch_id' => $branch->id,
        ]);

    $response
        ->assertSessionHasErrors('status')
        ->assertRedirect(route('admin.master-data.users.edit', $targetUser));
});

test('role is required when creating admin users', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch = Branch::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.users.create'))
        ->post(route('admin.master-data.users.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.test',
            'branch_id' => $branch->id,
        ]);

    $response
        ->assertSessionHasErrors('role')
        ->assertRedirect(route('admin.master-data.users.create'));
});

test('role is required when updating admin users', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch = Branch::factory()->create();
    $targetUser = User::factory()->create([
        'branch_id' => $branch->id,
    ]);

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.users.edit', $targetUser))
        ->put(route('admin.master-data.users.update', $targetUser), [
            'name' => 'Updated Target',
            'email' => $targetUser->email,
            'status' => UserStatus::Active->value,
            'branch_id' => $branch->id,
        ]);

    $response
        ->assertSessionHasErrors('role')
        ->assertRedirect(route('admin.master-data.users.edit', $targetUser));
});

test('admin users create page exposes role options', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());

    $this->actingAs($actor)
        ->get(route('admin.master-data.users.create'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/master-data/users/Create')
            ->has('userRoleOptions', count(UserRole::cases()))
            ->where('userRoleOptions.0.value', UserRole::SuperAdmin->value)
        );
});

test('admin users edit page exposes role options and the active role', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::ItAgent->value, 'web');
    $targetUser = User::factory()->create();
    $targetUser->assignRole(UserRole::ItAgent->value);

    $this->actingAs($actor)
        ->get(route('admin.master-data.users.edit', $targetUser))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/master-data/users/Edit')
            ->where('user.role', UserRole::ItAgent->value)
            ->has('userRoleOptions', count(UserRole::cases()))
        );
});

test('admin users index exposes a single deferred table payload contract', function (): void {
    $actor = grantAdminPermissions(User::factory()->create([
        'created_at' => Date::parse('2026-05-26 09:00:00'),
    ]));
    Role::findOrCreate(UserRole::ItAgent->value, 'web');
    Role::findOrCreate(UserRole::Requester->value, 'web');
    $alice = User::factory()->create([
        'name' => 'Alice Example',
        'email' => 'alice@example.test',
        'status' => UserStatus::Active,
        'created_at' => Date::parse('2026-05-26 10:00:00'),
    ]);
    $alice->assignRole(UserRole::ItAgent->value);

    $bob = User::factory()->create([
        'name' => 'Bob Example',
        'email' => 'bob@example.test',
        'status' => UserStatus::Suspend,
        'created_at' => Date::parse('2026-05-26 11:00:00'),
    ]);
    $bob->assignRole(UserRole::Requester->value);

    $response = $this
        ->actingAs($actor)
        ->get(route('admin.master-data.users.index'));

    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('admin/master-data/users/Index')
        ->missing('table')
        ->loadDeferredProps(fn (Assert $reload): Assert => $reload
            ->has('table.rows', 3)
            ->where('table.rows.0.role', UserRole::Requester->value)
            ->where('table.rows.0.roleLabel', UserRole::Requester->label())
            ->where('table.state.filters.search', null)
            ->where('table.state.filters.status', null)
            ->where('table.state.sort', '-created_at')
            ->where('table.schema.filters.0.key', 'search')
            ->where('table.schema.filters.0.type', 'search')
            ->where('table.schema.filters.1.key', 'role')
            ->where('table.schema.filters.1.type', 'select')
            ->where('table.schema.filters.1.options.0.value', UserRole::SuperAdmin->value)
            ->where('table.schema.filters.2.key', 'status')
            ->where('table.schema.filters.2.type', 'select')
            ->where('table.schema.filters.2.options.0.value', 'active')
        ));
});

test('admin users index filters by role through the deferred table payload', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::ItAgent->value, 'web');
    Role::findOrCreate(UserRole::Requester->value, 'web');

    $zed = User::factory()->create([
        'name' => 'Zed Example',
        'email' => 'zed@example.test',
    ]);
    $zed->assignRole(UserRole::Requester->value);

    $alice = User::factory()->create([
        'name' => 'Alice Example',
        'email' => 'alice@example.test',
    ]);
    $alice->assignRole(UserRole::ItAgent->value);

    $response = $this
        ->actingAs($actor)
        ->get(route('admin.master-data.users.index', [
            'filter' => [
                'role' => UserRole::ItAgent->value,
            ],
        ]));

    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('admin/master-data/users/Index')
        ->missing('table')
        ->loadDeferredProps(fn (Assert $reload): Assert => $reload
            ->has('table.rows', 1)
            ->where('table.rows.0.name', 'Alice Example')
            ->where('table.rows.0.role', UserRole::ItAgent->value)
            ->where('table.state.filters.role', UserRole::ItAgent->value)
        ));
});

test('admin users index applies query builder filters and state through the deferred table payload', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::ItAgent->value, 'web');
    Role::findOrCreate(UserRole::Requester->value, 'web');
    $zed = User::factory()->create([
        'name' => 'Zed Example',
        'email' => 'zed@example.test',
        'status' => UserStatus::Suspend,
        'created_at' => Date::parse('2026-05-26 10:00:00'),
    ]);
    $zed->assignRole(UserRole::Requester->value);

    $alice = User::factory()->create([
        'name' => 'Alice Example',
        'email' => 'alice@example.test',
        'status' => UserStatus::Active,
        'created_at' => Date::parse('2026-05-26 11:00:00'),
    ]);
    $alice->assignRole(UserRole::ItAgent->value);

    $response = $this
        ->actingAs($actor)
        ->get(route('admin.master-data.users.index', [
            'filter' => [
                'search' => 'Alice',
                'status' => UserStatus::Active->value,
            ],
            'sort' => 'name',
            'per_page' => 25,
        ]));

    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('admin/master-data/users/Index')
        ->missing('table')
        ->loadDeferredProps(fn (Assert $reload): Assert => $reload
            ->has('table.rows', 1)
            ->where('table.rows.0.name', 'Alice Example')
            ->where('table.rows.0.role', UserRole::ItAgent->value)
            ->where('table.rows.0.roleLabel', UserRole::ItAgent->label())
            ->where('table.rows.0.status', UserStatus::Active->value)
            ->where('table.state.filters.search', 'Alice')
            ->where('table.state.filters.status', UserStatus::Active->value)
            ->where('table.state.sort', 'name')
            ->where('table.state.perPage', 25)
        ));
});

test('a requester user requires branch and department', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::Requester->value, 'web');

    $this
        ->actingAs($actor)
        ->post(route('admin.master-data.users.store'), [
            'name' => 'Req User',
            'email' => 'req-user@example.test',
            'role' => UserRole::Requester->value,
        ])
        ->assertSessionHasErrors(['branch_id', 'department_id']);
});

test('a staff user does not require branch or department', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::ItAgent->value, 'web');

    $this
        ->actingAs($actor)
        ->post(route('admin.master-data.users.store'), [
            'name' => 'Agent User',
            'email' => 'agent-user@example.test',
            'role' => UserRole::ItAgent->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.users.index'));

    expect(User::query()->where('email', 'agent-user@example.test')->exists())->toBeTrue();
});
