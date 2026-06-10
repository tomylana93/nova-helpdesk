<?php

use App\Enums\GeneralStatus;
use App\Models\TicketCategory;
use App\Models\User;

test('ticket categories can be created by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());

    // Create parent category
    $response = $this
        ->actingAs($actor)
        ->post(route('admin.master-data.ticket-categories.store'), [
            'name' => 'Hardware',
            'description' => 'Hardware issues',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.ticket-categories.index'));

    $parentCategory = TicketCategory::query()
        ->where('name', 'Hardware')
        ->first();

    expect($parentCategory)->not->toBeNull();
    expect($parentCategory?->parent_id)->toBeNull();
    expect($parentCategory?->description)->toBe('Hardware issues');
    expect($parentCategory?->status)->toBe(GeneralStatus::Active);

    // Create subcategory under parent
    $response = $this
        ->actingAs($actor)
        ->post(route('admin.master-data.ticket-categories.store'), [
            'parent_id' => $parentCategory->id,
            'name' => 'Keyboard',
            'description' => 'Keyboard replacement',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasNoErrors();

    $subCategory = TicketCategory::query()
        ->where('name', 'Keyboard')
        ->first();

    expect($subCategory)->not->toBeNull();
    expect($subCategory?->parent_id)->toBe($parentCategory->id);
    expect($subCategory?->description)->toBe('Keyboard replacement');
});

test('ticket categories can be updated by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $parent = TicketCategory::factory()->create(['name' => 'Software']);
    $category = TicketCategory::factory()->create([
        'parent_id' => null,
        'name' => 'Old Name',
        'description' => 'Old Description',
        'status' => GeneralStatus::Active,
    ]);

    $response = $this
        ->actingAs($actor)
        ->put(route('admin.master-data.ticket-categories.update', $category), [
            'parent_id' => $parent->id,
            'name' => 'New Name',
            'description' => 'New Description',
            'status' => GeneralStatus::Inactive->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.ticket-categories.index'));

    $category->refresh();

    expect($category->parent_id)->toBe($parent->id);
    expect($category->name)->toBe('New Name');
    expect($category->description)->toBe('New Description');
    expect($category->status)->toBe(GeneralStatus::Inactive);
});

test('category cannot be its own parent', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $category = TicketCategory::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.ticket-categories.edit', $category))
        ->put(route('admin.master-data.ticket-categories.update', $category), [
            'parent_id' => $category->id,
            'name' => 'Self Parent',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasErrors('parent_id')
        ->assertRedirect(route('admin.master-data.ticket-categories.edit', $category));
});

test('unauthorized users cannot manage ticket categories', function (): void {
    $actor = User::factory()->create(); // standard requester user
    $category = TicketCategory::factory()->create();

    // Try to view index
    $this->actingAs($actor)
        ->get(route('admin.master-data.ticket-categories.index'))
        ->assertForbidden();

    // Try to store
    $this->actingAs($actor)
        ->post(route('admin.master-data.ticket-categories.store'), [
            'name' => 'Unauthorized Category',
            'status' => GeneralStatus::Active->value,
        ])
        ->assertForbidden();

    // Try to update
    $this->actingAs($actor)
        ->put(route('admin.master-data.ticket-categories.update', $category), [
            'name' => 'Unauthorized Edit',
            'status' => GeneralStatus::Active->value,
        ])
        ->assertForbidden();
});
