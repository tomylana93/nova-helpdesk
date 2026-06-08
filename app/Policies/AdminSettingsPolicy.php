<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\User;

class AdminSettingsPolicy
{
    public function view(User $user): bool
    {
        return $user->can(AdminPermission::ManageSettings->value);
    }

    public function update(User $user): bool
    {
        return $user->can(AdminPermission::ManageSettings->value);
    }
}
