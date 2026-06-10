<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(AdminPermission::ViewTickets->value);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if (! $user->can(AdminPermission::ViewTickets->value)) {
            return false;
        }

        if ($user->hasRole(UserRole::ItAgent)) {
            return true;
        }

        if ($user->hasRole(UserRole::SuperAdmin)) {
            return true;
        }

        return $ticket->requester_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can(AdminPermission::CreateTickets->value);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->can(AdminPermission::UpdateTickets->value);
    }

    public function approve(User $user, Ticket $ticket): bool
    {
        return $user->can(AdminPermission::ManageApprovals->value);
    }
}
