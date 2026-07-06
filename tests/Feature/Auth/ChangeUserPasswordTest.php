<?php

use App\Actions\Auth\ChangeUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates a user password and clears the forced change flag', function (): void {
    $user = User::factory()->mustChangePassword()->create();

    app(ChangeUserPassword::class)->handle($user, 'NewStr0ng!Pass');

    $user->refresh();

    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('NewStr0ng!Pass', $user->password))->toBeTrue();
});
