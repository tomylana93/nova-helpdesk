<?php

use App\Enums\UserRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin users can export users as an xlsx download', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Role::findOrCreate(UserRole::ItAgent->value, 'web');

    User::factory()->create([
        'name' => 'Exported User',
        'email' => 'exported-user@example.test',
        'phone' => '081222333444',
    ])->assignRole(UserRole::ItAgent->value);

    $this
        ->actingAs($actor)
        ->get(route('admin.master-data.users.export'))
        ->assertDownload('users.xlsx');
});
