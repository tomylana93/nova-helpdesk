<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Enums\TicketStatus;
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

        if ($user->hasRole(UserRole::Auditor)) {
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
        return $user->can(AdminPermission::ManageApprovals->value)
            && $user->hasRole(UserRole::ItAgent)
            && $ticket->assigned_to === $user->id
            && $ticket->status === TicketStatus::PendingApproval;
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        return $ticket->requester_id === $user->id
            && in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed], true);
    }

    public function confirmResolution(User $user, Ticket $ticket): bool
    {
        return $ticket->requester_id === $user->id
            && $ticket->status === TicketStatus::Resolved;
    }
}
