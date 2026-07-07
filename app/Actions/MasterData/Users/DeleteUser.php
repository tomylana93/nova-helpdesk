<?php

namespace App\Actions\MasterData\Users;

use App\Models\User;

class DeleteUser
{
    public function handle(User $user): void
    {
        $user->delete();
    }
}
