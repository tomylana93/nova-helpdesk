<?php

namespace App\Actions\MasterData\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUser
{
    public function handle(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->fill([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'status' => $data['status'] ?? $user->status,
            ])->save();

            $user->syncRoles([$data['role']]);

            return $user;
        });
    }
}
