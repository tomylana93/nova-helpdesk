<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Ticket;
use App\Models\User;

class AssignTicketToAgent
{
    /**
     * Resolve the agent a new ticket should be assigned to.
     *
     * Rules: a single active agent takes it; with several, the one with the fewest open
     * tickets wins (tie-broken by oldest account); with none, the ticket stays unassigned.
     */
    public function handle(Ticket $ticket): ?User
    {
        $agents = User::query()
            ->where('status', UserStatus::Active)
            ->whereHas('roles', function ($query): void {
                $query->where('name', UserRole::ItAgent->value);
            })->oldest()
            ->get();

        if ($agents->isEmpty()) {
            return null;
        }

        if ($agents->count() === 1) {
            return $agents->first();
        }

        return $agents
            ->sortBy(fn (User $agent): int => $this->openTicketLoad($agent))
            ->first();
    }

    private function openTicketLoad(User $agent): int
    {
        return Ticket::query()
            ->where('assigned_to', $agent->id)
            ->whereIn('status', $this->openStatusValues())
            ->count();
    }

    /**
     * @return list<string>
     */
    private function openStatusValues(): array
    {
        return array_values(array_map(
            fn (TicketStatus $status): string => $status->value,
            array_filter(TicketStatus::cases(), fn (TicketStatus $status): bool => $status->isOpen()),
        ));
    }
}
