<?php

use App\Enums\GeneralStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;

test('departments can be created by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch = Branch::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->post(route('admin.master-data.departments.store'), [
            'branch_id' => $branch->id,
            'code' => 'DEPT-IT',
            'name' => 'IT Department',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.departments.index'));

    $createdDept = Department::query()
        ->where('code', 'DEPT-IT')
        ->first();

    expect($createdDept)->not->toBeNull();
    expect($createdDept?->branch_id)->toBe($branch->id);
    expect($createdDept?->name)->toBe('IT Department');
    expect($createdDept?->status)->toBe(GeneralStatus::Active);
});

test('departments can be updated by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch1 = Branch::factory()->create();
    $branch2 = Branch::factory()->create();
    $dept = Department::factory()->create([
        'branch_id' => $branch1->id,
        'code' => 'DEPT-OLD',
        'name' => 'Old Name',
        'status' => GeneralStatus::Active,
    ]);

    $response = $this
        ->actingAs($actor)
        ->put(route('admin.master-data.departments.update', $dept), [
            'branch_id' => $branch2->id,
            'code' => 'DEPT-NEW',
            'name' => 'New Name',
            'status' => GeneralStatus::Inactive->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.departments.index'));

    $dept->refresh();

    expect($dept->branch_id)->toBe($branch2->id);
    expect($dept->code)->toBe('DEPT-NEW');
    expect($dept->name)->toBe('New Name');
    expect($dept->status)->toBe(GeneralStatus::Inactive);
});

test('department code must be unique', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Department::factory()->create(['code' => 'DEPT-DUP']);
    $branch = Branch::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.departments.create'))
        ->post(route('admin.master-data.departments.store'), [
            'branch_id' => $branch->id,
            'code' => 'DEPT-DUP',
            'name' => 'Another Department',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasErrors('code')
        ->assertRedirect(route('admin.master-data.departments.create'));
});
