<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Queue;
use App\Models\User;

class QueuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(AdminPermission::ManageQueues->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Queue $queue): bool
    {
        return $user->can(AdminPermission::ManageQueues->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(AdminPermission::ManageQueues->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Queue $queue): bool
    {
        return $user->can(AdminPermission::ManageQueues->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Queue $queue): bool
    {
        return $user->can(AdminPermission::ManageQueues->value);
    }
}
