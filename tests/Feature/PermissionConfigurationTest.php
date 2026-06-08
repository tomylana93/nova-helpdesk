<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('users can be assigned roles and permissions', function (): void {
    $user = User::factory()->create();
    $role = Role::query()->create([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
    $permission = Permission::query()->create([
        'name' => 'view reports',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->fresh()?->hasRole('admin'))->toBeTrue();
    expect($user->fresh()?->can('view reports'))->toBeTrue();
});

test('role middleware aliases protect routes', function (): void {
    Route::middleware(['web', 'auth', 'role:admin'])
        ->get('/test-admin-role', fn () => response()->noContent());

    $user = User::factory()->create();
    Role::query()->create([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/test-admin-role')
        ->assertNoContent();
});
