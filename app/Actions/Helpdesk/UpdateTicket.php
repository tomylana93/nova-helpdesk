<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class UpdateTicket
{
    public function __construct(
        private readonly RecordTicketActivity $recordActivity,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Ticket $ticket, array $data, User $actor): void
    {
        $oldStatus = $ticket->status->value;
        $oldAssignee = $ticket->assigned_to;

        if (isset($data['status']) && $data['status'] === TicketStatus::Resolved->value && $ticket->resolved_at === null) {
            $data['resolved_at'] = now();
        }

        if (isset($data['status']) && $data['status'] === TicketStatus::Closed->value && $ticket->closed_at === null) {
            $data['closed_at'] = now();
        }

        $ticket->update($data);

        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $this->recordActivity->handle($ticket, 'status_changed', $actor, [
                'from' => $oldStatus,
                'to' => $data['status'],
            ]);
        }

        if (isset($data['assigned_to']) && $data['assigned_to'] !== $oldAssignee) {
            $this->recordActivity->handle($ticket, 'assigned', $actor, [
                'assigned_to' => $data['assigned_to'],
            ]);
        }
    }
}
