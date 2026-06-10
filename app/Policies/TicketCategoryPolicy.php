<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\TicketCategory;
use App\Models\User;

class TicketCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(AdminPermission::ManageCategories->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketCategory $ticketCategory): bool
    {
        return $user->can(AdminPermission::ManageCategories->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(AdminPermission::ManageCategories->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketCategory $ticketCategory): bool
    {
        return $user->can(AdminPermission::ManageCategories->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketCategory $ticketCategory): bool
    {
        return $user->can(AdminPermission::ManageCategories->value);
    }
}
