<?php

namespace App\Actions\Helpdesk;

use App\Models\Ticket;

class MarkFirstResponse
{
    public function handle(Ticket $ticket): void
    {
        if ($ticket->first_responded_at !== null) {
            return;
        }

        $ticket->forceFill([
            'first_responded_at' => now(),
        ])->save();
    }
}
