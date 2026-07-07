<?php

namespace App\Actions\MasterData\Users;

use App\Models\User;

class RestoreUser
{
    public function handle(User $user): void
    {
        $user->restore();
    }
}
