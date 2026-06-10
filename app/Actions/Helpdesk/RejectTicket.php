<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectTicket
{
    public function handle(Ticket $ticket, User $reviewer, ?string $note = null): void
    {
        DB::transaction(function () use ($ticket, $reviewer, $note): void {
            TicketApproval::query()->updateOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'reviewer_id' => $reviewer->id,
                    'status' => 'rejected',
                    'decided_at' => now(),
                    'decision_note' => $note,
                ],
            );

            $ticket->update(['status' => TicketStatus::Closed]);
        });
    }
}
