<?php

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('it syncs all application roles from the enum', function (): void {
    $this->artisan('permission:sync-roles')
        ->expectsOutputToContain(UserRole::SuperAdmin->value)
        ->expectsOutputToContain(UserRole::ItAgent->value)
        ->expectsOutputToContain(UserRole::Auditor->value)
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

    expect(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ViewTickets->value))->toBeTrue()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::CreateTickets->value))->toBeTrue()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::UpdateTickets->value))->toBeFalse()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ManageApprovals->value))->toBeFalse()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ViewReports->value))->toBeTrue()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ManageSettings->value))->toBeFalse()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ViewUsers->value))->toBeFalse()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ManageBranches->value))->toBeFalse()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ManageDepartments->value))->toBeFalse()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ManageCategories->value))->toBeFalse()
        ->and(Role::findByName(UserRole::Auditor->value, 'web')->hasPermissionTo(AdminPermission::ManageSlaPolicies->value))->toBeFalse();

    expect(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ViewTickets->value))->toBeTrue()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::CreateTickets->value))->toBeTrue()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::UpdateTickets->value))->toBeTrue()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ManageApprovals->value))->toBeTrue()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ViewReports->value))->toBeTrue()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ManageAssets->value))->toBeFalse()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ManageSettings->value))->toBeFalse()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ViewUsers->value))->toBeFalse()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ManageBranches->value))->toBeFalse()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ManageDepartments->value))->toBeFalse()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ManageCategories->value))->toBeFalse()
        ->and(Role::findByName(UserRole::ItAgent->value, 'web')->hasPermissionTo(AdminPermission::ManageSlaPolicies->value))->toBeFalse();
});

test('it is safe to rerun the sync roles command', function (): void {
    $this->artisan('permission:sync-roles')->assertSuccessful();

    $this->artisan('permission:sync-roles')
        ->expectsOutputToContain('already exists')
        ->assertSuccessful();

    expect(Role::query()->where('guard_name', 'web')->count())
        ->toBe(count(UserRole::cases()));
});

test('it syncs role permissions from the roles config seam', function (): void {
    config()->set('roles.permissions', [
        UserRole::ItAgent->value => [
            AdminPermission::ViewTickets->value,
        ],
    ]);

    $this->artisan('permission:sync-roles')->assertSuccessful();

    expect(Role::findByName(UserRole::ItAgent->value, 'web')->getPermissionNames()->all())
        ->toBe([AdminPermission::ViewTickets->value]);
});

test('user role labels translate correctly for indonesian locale', function (): void {
    app()->setLocale('id');

    expect(UserRole::ItAgent->label())->toBe('Agen IT');
    expect(UserRole::Auditor->label())->toBe('Auditor');
    expect(UserRole::Requester->label())->toBe('Pemohon');
});
