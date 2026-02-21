<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated user can open create user page', function () {
    $authenticatedUser = User::factory()->active()->create();

    $this->actingAs($authenticatedUser)
        ->get(route('master.users.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('master/users/Create')
        );
});

test('authenticated user can store user and redirected to users index', function () {
    $authenticatedUser = User::factory()->active()->create();

    $response = $this->actingAs($authenticatedUser)
        ->post(route('master.users.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('master.users.index'));

    $storedUser = User::query()
        ->where('email', 'new-user@example.com')
        ->first();

    expect($storedUser)->not->toBeNull();
    expect($storedUser?->status)->toBe(UserStatusEnum::ACTIVE);
    expect(Hash::check('Password123!', (string) $storedUser?->password))->toBeTrue();
});

test('store user validation fails for required fields', function () {
    $authenticatedUser = User::factory()->active()->create();

    $this->actingAs($authenticatedUser)
        ->from(route('master.users.create'))
        ->post(route('master.users.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password'])
        ->assertRedirect(route('master.users.create'));
});

test('store user validation fails for duplicate email and password confirmation mismatch', function () {
    $authenticatedUser = User::factory()->active()->create();
    User::factory()->create([
        'email' => 'duplicate@example.com',
    ]);

    $response = $this->actingAs($authenticatedUser)
        ->from(route('master.users.create'))
        ->post(route('master.users.store'), [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!x',
        ]);

    $response
        ->assertSessionHasErrors(['email', 'password'])
        ->assertRedirect(route('master.users.create'));
});

test('authenticated user can open edit user page', function () {
    $authenticatedUser = User::factory()->active()->create();
    $targetUser = User::factory()->disabled()->create();

    $this->actingAs($authenticatedUser)
        ->get(route('master.users.edit', $targetUser))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('master/users/Edit')
            ->where('user.id', $targetUser->id)
            ->where('user.name', $targetUser->name)
            ->where('user.email', $targetUser->email)
            ->where('user.status', UserStatusEnum::DISABLED->value)
            ->has('statusOptions', 2)
        );
});

test('authenticated user can update name email and status', function () {
    $authenticatedUser = User::factory()->active()->create();
    $targetUser = User::factory()->disabled()->create([
        'name' => 'Old Name',
        'email' => 'old-email@example.com',
    ]);

    $response = $this->actingAs($authenticatedUser)
        ->put(route('master.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => 'updated-email@example.com',
            'status' => UserStatusEnum::ACTIVE->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('master.users.index'));

    $targetUser->refresh();

    expect($targetUser->name)->toBe('Updated Name');
    expect($targetUser->email)->toBe('updated-email@example.com');
    expect($targetUser->status)->toBe(UserStatusEnum::ACTIVE);
});

test('update validation fails for duplicate email but ignores current user email', function () {
    $authenticatedUser = User::factory()->active()->create();
    $targetUser = User::factory()->active()->create([
        'email' => 'target@example.com',
    ]);
    User::factory()->active()->create([
        'email' => 'duplicate@example.com',
    ]);

    $this->actingAs($authenticatedUser)
        ->from(route('master.users.edit', $targetUser))
        ->put(route('master.users.update', $targetUser), [
            'name' => 'Target User',
            'email' => 'duplicate@example.com',
            'status' => UserStatusEnum::ACTIVE->value,
        ])
        ->assertSessionHasErrors(['email'])
        ->assertRedirect(route('master.users.edit', $targetUser));

    $this->actingAs($authenticatedUser)
        ->from(route('master.users.edit', $targetUser))
        ->put(route('master.users.update', $targetUser), [
            'name' => 'Target User Updated',
            'email' => 'target@example.com',
            'status' => UserStatusEnum::DISABLED->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('master.users.index'));
});

test('update does not change password', function () {
    $authenticatedUser = User::factory()->active()->create();
    $targetUser = User::factory()->active()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);
    $originalPassword = $targetUser->password;

    $this->actingAs($authenticatedUser)
        ->put(route('master.users.update', $targetUser), [
            'name' => 'Password Safe',
            'email' => 'password-safe@example.com',
            'status' => UserStatusEnum::ACTIVE->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('master.users.index'));

    $targetUser->refresh();

    expect($targetUser->password)->toBe($originalPassword);
});

test('update does not reset email_verified_at when email changes', function () {
    $authenticatedUser = User::factory()->active()->create();
    $targetUser = User::factory()->active()->create([
        'email' => 'before-change@example.com',
        'email_verified_at' => now(),
    ]);
    $verifiedAt = $targetUser->email_verified_at;

    $this->actingAs($authenticatedUser)
        ->put(route('master.users.update', $targetUser), [
            'name' => 'Verified User',
            'email' => 'after-change@example.com',
            'status' => UserStatusEnum::ACTIVE->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('master.users.index'));

    $targetUser->refresh();

    expect($targetUser->email_verified_at)->not->toBeNull();
    expect($targetUser->email_verified_at?->equalTo($verifiedAt))->toBeTrue();
});
