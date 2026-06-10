<?php

use App\Enums\GeneralStatus;
use App\Models\Queue;
use App\Models\User;

test('queues can be created by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());

    $response = $this
        ->actingAs($actor)
        ->post(route('admin.master-data.queues.store'), [
            'name' => 'Network Queue',
            'description' => 'Queue for network-related issues',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.queues.index'));

    $createdQueue = Queue::query()
        ->where('name', 'Network Queue')
        ->first();

    expect($createdQueue)->not->toBeNull();
    expect($createdQueue?->description)->toBe('Queue for network-related issues');
    expect($createdQueue?->status)->toBe(GeneralStatus::Active);
});

test('queues can be updated by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $queue = Queue::factory()->create([
        'name' => 'Old Name',
        'description' => 'Old Description',
        'status' => GeneralStatus::Active,
    ]);

    $response = $this
        ->actingAs($actor)
        ->put(route('admin.master-data.queues.update', $queue), [
            'name' => 'New Name',
            'description' => 'New Description',
            'status' => GeneralStatus::Inactive->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.queues.index'));

    $queue->refresh();

    expect($queue->name)->toBe('New Name');
    expect($queue->description)->toBe('New Description');
    expect($queue->status)->toBe(GeneralStatus::Inactive);
});

test('queue name must be unique', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    Queue::factory()->create(['name' => 'Duplicate Queue']);

    $response = $this
        ->actingAs($actor)
        ->from(route('admin.master-data.queues.create'))
        ->post(route('admin.master-data.queues.store'), [
            'name' => 'Duplicate Queue',
            'description' => 'Some description',
            'status' => GeneralStatus::Active->value,
        ]);

    $response
        ->assertSessionHasErrors('name')
        ->assertRedirect(route('admin.master-data.queues.create'));
});

test('unauthorized users cannot manage queues', function (): void {
    $actor = User::factory()->create(); // standard requester user
    $queue = Queue::factory()->create();

    // Try to view index
    $this->actingAs($actor)
        ->get(route('admin.master-data.queues.index'))
        ->assertForbidden();

    // Try to store
    $this->actingAs($actor)
        ->post(route('admin.master-data.queues.store'), [
            'name' => 'Unauthorized Queue',
            'status' => GeneralStatus::Active->value,
        ])
        ->assertForbidden();

    // Try to update
    $this->actingAs($actor)
        ->put(route('admin.master-data.queues.update', $queue), [
            'name' => 'Unauthorized Edit',
            'status' => GeneralStatus::Active->value,
        ])
        ->assertForbidden();
});
