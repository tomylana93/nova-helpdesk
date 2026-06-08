<?php

namespace App\Actions\MasterData\Users;

use App\Enums\UserStatus;
use App\Models\User;
use App\Settings\PasswordSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function __construct(
        private readonly PasswordSettings $passwordSettings,
    ) {}

    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($this->passwordSettings->default_user_password),
                'status' => UserStatus::Active,
            ]);

            $user->syncRoles([$data['role']]);

            return $user;
        });
    }
}
