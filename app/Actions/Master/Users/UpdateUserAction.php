<?php

namespace App\Actions\Master\Users;

use App\Models\User;

class UpdateUserAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        $user->update([
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'status' => (string) $data['status'],
        ]);

        return $user->refresh();
    }
}
