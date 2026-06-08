<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('settings/Profile')
            ->has('avatarFile', 0),
        );
});

test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});

test('profile avatar can be uploaded', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $uploadResponse = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('avatar.png', 128, 128),
        ]);

    $uploadResponse->assertCreated();

    $temporaryUploadId = $uploadResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_upload_id' => $temporaryUploadId,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->getFirstMedia('avatar'))->not->toBeNull();
    expect($user->avatar)->not->toBeNull();
});

test('profile avatar can be removed', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $uploadResponse = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('avatar.png', 128, 128),
        ]);

    $temporaryUploadId = $uploadResponse->json('id');

    $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_upload_id' => $temporaryUploadId,
        ]);

    $user->refresh();

    expect($user->getFirstMedia('avatar'))->not->toBeNull();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_remove' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->getFirstMedia('avatar'))->toBeNull();
    expect($user->avatar)->toBeNull();
});

test('profile avatar replacement keeps the new avatar when remove and upload are submitted together', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $initialUploadId = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('avatar-old.png', 128, 128),
        ])
        ->json('id');

    $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_upload_id' => $initialUploadId,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    $initialAvatarId = $user->getFirstMedia('avatar')?->id;

    expect($initialAvatarId)->not->toBeNull();

    $replacementUploadId = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('avatar-new.png', 128, 128),
        ])
        ->json('id');

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_upload_id' => $replacementUploadId,
            'avatar_remove' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    $avatarMedia = $user->getFirstMedia('avatar');

    expect($avatarMedia)->not->toBeNull();
    expect($avatarMedia?->id)->not->toBe($initialAvatarId);
});
