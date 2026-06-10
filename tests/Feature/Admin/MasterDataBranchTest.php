<?php

use App\Enums\GeneralStatus;
use App\Models\Branch;
use App\Models\User;

test('branches can be created by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($actor)
        ->post(route('admin.master-data.branches.store'), [
            'code' => 'BR-TEST',
            'name' => 'Test Branch',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.branches.index'));

    $createdBranch = Branch::query()
        ->where('code', 'BR-TEST')
        ->first();

    expect($createdBranch)->not->toBeNull();
    expect($createdBranch?->name)->toBe('Test Branch');
    expect($createdBranch?->status)->toBe(GeneralStatus::Active);
});

test('branches can be updated by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch = Branch::factory()->create([
        'code' => 'BR-OLD',
        'name' => 'Old Name',
        'status' => GeneralStatus::Active,
    ]);

    $response = $this
        ->actingAs($actor)
        ->put(route('admin.master-data.branches.update', $branch), [
            'code' => 'BR-NEW',
            'name' => 'New Name',
            'status' => GeneralStatus::Inactive->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.branches.index'));

    $branch->refresh();

    expect($branch->code)->toBe('BR-NEW');
    expect($branch->name)->toBe('New Name');
    expect($branch->status)->toBe(GeneralStatus::Inactive);
});

test('branch code must be unique', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Branch::factory()->create(['code' => 'BR-DUPLICATE']);

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.branches.create'))
        ->post(route('admin.master-data.branches.store'), [
            'code' => 'BR-DUPLICATE',
            'name' => 'Another Branch',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasErrors('code')
        ->assertRedirect(route('admin.master-data.branches.create'));
});
