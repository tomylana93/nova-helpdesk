<?php

use App\Models\User;

test('users without admin permissions cannot access admin settings', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.settings.general.edit'))
        ->assertForbidden();
});

test('users without admin permissions cannot access master data users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.master-data.users.index'))
        ->assertForbidden();
});

test('auditors cannot access admin settings or master data', function (): void {
    $user = createAuditorUser();

    $this->actingAs($user)
        ->get(route('admin.settings.general.edit'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.master-data.users.index'))
        ->assertForbidden();
});

test('admin users can access current admin surfaces', function (): void {
    $user = grantAdminPermissions(User::factory()->create());

    $this->actingAs($user)
        ->get(route('admin.settings.general.edit'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('admin.master-data.users.index'))
        ->assertOk();
});

test('super admins bypass current admin policy checks', function (): void {
    $user = grantSuperAdmin(User::factory()->create());

    $this->actingAs($user)
        ->get(route('admin.settings.general.edit'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('admin.master-data.users.create'))
        ->assertOk();
});
