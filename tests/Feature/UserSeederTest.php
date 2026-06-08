<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\UserSeeder;

test('user seeder creates example users with their roles', function (): void {
    $this->seed(UserSeeder::class);

    $activeUser = User::query()->where('email', 'active@example.com')->first();
    $disabledUser = User::query()->where('email', 'disabled@example.com')->first();
    $suspendedUser = User::query()->where('email', 'suspended@example.com')->first();

    expect($activeUser)->not->toBeNull();
    expect($disabledUser)->not->toBeNull();
    expect($suspendedUser)->not->toBeNull();

    expect($activeUser?->status)->toBe(UserStatus::Active);
    expect($activeUser?->getRoleNames()->all())->toBe([UserRole::Admin->value]);

    expect($disabledUser?->status)->toBe(UserStatus::Disable);
    expect($disabledUser?->getRoleNames()->all())->toBe([UserRole::Dispatcher->value]);

    expect($suspendedUser?->status)->toBe(UserStatus::Suspend);
    expect($suspendedUser?->getRoleNames()->all())->toBe([UserRole::ComplianceOfficer->value]);
});
