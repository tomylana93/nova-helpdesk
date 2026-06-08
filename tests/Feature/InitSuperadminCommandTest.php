<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Settings\PasswordSettings;
use Illuminate\Support\Facades\Hash;

test('it creates the superadmin user from configured defaults', function (): void {
    config()->set('nova.superadmin.name', 'Nova Core Admin');
    config()->set('nova.superadmin.email', 'superadmin@example.test');

    $passwordSettings = app(PasswordSettings::class);
    $passwordSettings->default_user_password = 'super-secret-password';
    $passwordSettings->save();

    $this->artisan('init:superadmin')
        ->expectsOutputToContain('superadmin@example.test')
        ->assertSuccessful();

    $createdUser = User::query()
        ->where('email', 'superadmin@example.test')
        ->first();

    expect($createdUser)->not->toBeNull();
    expect($createdUser?->name)->toBe('Nova Core Admin');
    expect($createdUser?->status)->toBe(UserStatus::Active);
    expect(Hash::check('super-secret-password', (string) $createdUser?->password))->toBeTrue();
    expect($createdUser?->getRoleNames()->all())->toBe([UserRole::SuperAdmin->value]);
});

test('it does not recreate the superadmin user when it already exists', function (): void {
    config()->set('nova.superadmin.name', 'Nova Core Admin');
    config()->set('nova.superadmin.email', 'superadmin@example.test');

    $existingUser = User::factory()->create([
        'name' => 'Existing Superadmin',
        'email' => 'superadmin@example.test',
    ]);

    $this->artisan('init:superadmin')
        ->expectsOutputToContain('already exists')
        ->assertSuccessful();

    expect(User::query()->where('email', 'superadmin@example.test')->count())->toBe(1);
    expect($existingUser->fresh()?->name)->toBe('Existing Superadmin');
});
