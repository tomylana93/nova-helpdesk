<?php

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function grantAdminPermissions(User $user): User
{
    $role = Role::findOrCreate(UserRole::SuperAdmin->value, 'web');

    $permissions = array_map(
        static fn (AdminPermission $permission): string => $permission->value,
        AdminPermission::cases(),
    );

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role->syncPermissions($permissions);
    $user->syncRoles([$role->name]);

    return $user;
}

function grantSuperAdmin(User $user): User
{
    Role::findOrCreate(UserRole::SuperAdmin->value, 'web');
    $user->syncRoles([UserRole::SuperAdmin->value]);

    return $user;
}

function createAgentUser(): User
{
    $role = Role::findOrCreate(UserRole::ItAgent->value, 'web');
    $permissions = [
        AdminPermission::ViewTickets->value,
        AdminPermission::CreateTickets->value,
        AdminPermission::UpdateTickets->value,
        AdminPermission::ManageApprovals->value,
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role->syncPermissions($permissions);

    return tap(User::factory()->create(), fn ($u) => $u->syncRoles([UserRole::ItAgent->value]));
}

function createRequesterUser(): User
{
    $role = Role::findOrCreate(UserRole::Requester->value, 'web');
    $permissions = [
        AdminPermission::ViewTickets->value,
        AdminPermission::CreateTickets->value,
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role->syncPermissions($permissions);

    return tap(User::factory()->create(), fn ($u) => $u->syncRoles([UserRole::Requester->value]));
}
