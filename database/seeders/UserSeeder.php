<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeUser = User::factory()->create([
            'email' => 'active@example.com',
            'name' => 'Active User',
            'status' => UserStatus::Active,
        ]);
        $activeUser->syncRoles([
            Role::findOrCreate(UserRole::Admin->value, 'web')->name,
        ]);

        $disabledUser = User::factory()->create([
            'email' => 'disabled@example.com',
            'name' => 'Disabled User',
            'status' => UserStatus::Disable,
        ]);
        $disabledUser->syncRoles([
            Role::findOrCreate(UserRole::Dispatcher->value, 'web')->name,
        ]);

        $suspendedUser = User::factory()->create([
            'email' => 'suspended@example.com',
            'name' => 'Suspended User',
            'status' => UserStatus::Suspend,
        ]);
        $suspendedUser->syncRoles([
            Role::findOrCreate(UserRole::ComplianceOfficer->value, 'web')->name,
        ]);
    }
}
