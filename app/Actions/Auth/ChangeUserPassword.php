<?php

namespace App\Actions\Auth;

use App\Models\User;

class ChangeUserPassword
{
    /**
     * Update the user's password and clear the forced-change flag.
     *
     * The plain password is hashed by the User model's `password => hashed`
     * cast on assignment; do not wrap it in Hash::make().
     */
    public function handle(User $user, string $plainPassword): void
    {
        $user->forceFill([
            'password' => $plainPassword,
            'must_change_password' => false,
        ])->save();
    }
}
