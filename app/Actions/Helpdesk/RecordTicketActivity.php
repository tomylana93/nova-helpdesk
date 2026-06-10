<?php

namespace App\Actions\Helpdesk;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;

class RecordTicketActivity
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(Ticket $ticket, string $event, ?User $actor = null, array $metadata = []): void
    {
        TicketActivity::query()->create([
            'ticket_id' => $ticket->id,
            'actor_id' => $actor?->id,
            'event' => $event,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
