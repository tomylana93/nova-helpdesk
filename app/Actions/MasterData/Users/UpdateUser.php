<?php

namespace App\Actions\MasterData\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUser
{
    /**
     * @param  array{name?: string, email?: string, phone?: string|null, status?: string, role: string, branch_id?: string|null, department_id?: string|null}  $data
     */
    public function handle(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->fill([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone' => array_key_exists('phone', $data) ? $data['phone'] : $user->phone,
                'status' => $data['status'] ?? $user->status,
                'branch_id' => $data['branch_id'] ?? $user->branch_id,
                'department_id' => array_key_exists('department_id', $data) ? $data['department_id'] : $user->department_id,
            ])->save();

            $user->syncRoles([$data['role']]);

            return $user;
        });
    }
}
