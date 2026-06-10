<?php

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('it syncs all application roles from the enum', function (): void {
    $this->artisan('permission:sync-roles')
        ->expectsOutputToContain(UserRole::SuperAdmin->value)
        ->expectsOutputToContain(UserRole::ItAgent->value)
        ->expectsOutputToContain(UserRole::Requester->value)
        ->expectsOutputToContain(AdminPermission::ManageSettings->value)
        ->assertSuccessful();

    expect(Role::query()->where('guard_name', 'web')->count())
        ->toBe(count(UserRole::cases()))
        ->and(Permission::query()->where('guard_name', 'web')->count())
        ->toBe(count(AdminPermission::cases()))
        ->and(Role::findByName(UserRole::SuperAdmin->value, 'web')->getPermissionNames()->all())
        ->toBe(array_map(
            static fn (AdminPermission $permission): string => $permission->value,
            AdminPermission::cases(),
        ));
});

test('it is safe to rerun the sync roles command', function (): void {
    $this->artisan('permission:sync-roles')->assertSuccessful();

    $this->artisan('permission:sync-roles')
        ->expectsOutputToContain('already exists')
        ->assertSuccessful();

    expect(Role::query()->where('guard_name', 'web')->count())
        ->toBe(count(UserRole::cases()));
});

test('user role labels translate correctly for indonesian locale', function (): void {
    app()->setLocale('id');

    expect(UserRole::ItAgent->label())->toBe('Agen IT');
    expect(UserRole::Requester->label())->toBe('Pemohon');
});
