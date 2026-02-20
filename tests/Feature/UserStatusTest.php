<?php

use App\Enums\UserStatusEnum;
use App\Models\User;

test('user status enum returns translated label', function () {
    expect(UserStatusEnum::ACTIVE->label())->toBe('Active');
    expect(UserStatusEnum::DISABLED->label())->toBe('Disabled');
});

test('user factory defaults to disabled status', function () {
    $user = User::factory()->create();

    expect($user->status)->toBe(UserStatusEnum::DISABLED);
});

test('user factory status states can be active or disabled', function () {
    $activeUser = User::factory()->active()->create();
    $disabledUser = User::factory()->disabled()->create();

    expect($activeUser->status)->toBe(UserStatusEnum::ACTIVE);
    expect($disabledUser->status)->toBe(UserStatusEnum::DISABLED);
});
