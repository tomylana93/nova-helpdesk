<?php

namespace App\Actions\Helpdesk;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;

class AddTicketComment
{
    public function handle(Ticket $ticket, User $user, string $body, string $visibility = 'public'): TicketComment
    {
        return TicketComment::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $body,
            'visibility' => $visibility,
        ]);
    }
}
