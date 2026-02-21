<?php

namespace App\Actions\Master\Users;

use App\Enums\UserStatusEnum;
use App\Models\User;

class CreateUserAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): User
    {
        return User::query()->create([
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => (string) $data['password'],
            'status' => UserStatusEnum::ACTIVE,
        ]);
    }
}
