<?php

namespace App\Console\Commands;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Signature('permission:sync-roles')]
#[Description('Sync application roles, permissions, and role permissions')]
class SyncRolesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PermissionRegistrar $permissionRegistrar): int
    {
        $permissionRegistrar->forgetCachedPermissions();

        foreach (UserRole::cases() as $role) {
            $existed = Role::query()
                ->where('name', $role->value)
                ->where('guard_name', 'web')
                ->exists();

            Role::findOrCreate($role->value, 'web');

            $this->line($existed
                ? "Role [{$role->value}] already exists."
                : "Role [{$role->value}] created.");
        }

        foreach (AdminPermission::cases() as $permission) {
            $existed = Permission::query()
                ->where('name', $permission->value)
                ->where('guard_name', 'web')
                ->exists();

            Permission::findOrCreate($permission->value, 'web');

            $this->line($existed
                ? "Permission [{$permission->value}] already exists."
                : "Permission [{$permission->value}] created.");
        }

        $this->syncRolePermissions();

        $permissionRegistrar->forgetCachedPermissions();

        return self::SUCCESS;
    }

    private function syncRolePermissions(): void
    {
        $adminPermissions = array_map(
            static fn (AdminPermission $permission): string => $permission->value,
            AdminPermission::cases(),
        );

        Role::findOrCreate(UserRole::Admin->value, 'web')
            ->syncPermissions($adminPermissions);

        foreach (UserRole::cases() as $role) {
            if (in_array($role, [UserRole::SuperAdmin, UserRole::Admin], true)) {
                continue;
            }

            $roleModel = Role::findOrCreate($role->value, 'web');

            foreach ($adminPermissions as $permission) {
                if ($roleModel->hasPermissionTo($permission)) {
                    $roleModel->revokePermissionTo($permission);
                }
            }
        }
    }
}
