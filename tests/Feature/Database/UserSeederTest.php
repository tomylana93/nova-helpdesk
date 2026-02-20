<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Database\Seeders\UserSeeder;

test('user seeder creates three active, two disabled, and one dev login user', function () {
    $this->seed(UserSeeder::class);

    expect(User::query()->count())->toBe(6);
    expect(User::query()->where('status', UserStatusEnum::ACTIVE->value)->count())->toBe(4);
    expect(User::query()->where('status', UserStatusEnum::DISABLED->value)->count())->toBe(2);
    expect(User::query()->where('email', 'admin@nova.dev')->exists())->toBeTrue();
});
