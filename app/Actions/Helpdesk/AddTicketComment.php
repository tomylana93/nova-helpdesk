<?php

namespace App\Actions\Helpdesk;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketNotification;

class AddTicketComment
{
    public function handle(Ticket $ticket, User $user, string $body, string $visibility = 'public'): TicketComment
    {
        $comment = TicketComment::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $body,
            'visibility' => $visibility,
        ]);

        // If commenter is the requester, notify the assigned agent
        if ($user->id === $ticket->requester_id) {
            if ($ticket->assignee) {
                $ticket->assignee->notify(new TicketNotification(
                    $ticket,
                    'comment',
                    "Requester {$user->name} commented on ticket {$ticket->ticket_number}."
                ));
            }
        } elseif ($visibility === 'public') {
            // Commenter is an agent/admin, notify the requester (if comment is public)
            $ticket->requester->notify(new TicketNotification(
                $ticket,
                'comment',
                "Agent {$user->name} commented on your ticket {$ticket->ticket_number}."
            ));
        }

        return $comment;
    }
}
