<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;

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

            // Notify requester of status change
            $ticket->requester->notify(new TicketNotification(
                $ticket,
                'status_changed',
                "Your ticket {$ticket->ticket_number} status has been changed to {$ticket->status->label()}."
            ));
        }

        if (isset($data['assigned_to']) && $data['assigned_to'] !== $oldAssignee) {
            $this->recordActivity->handle($ticket, 'assigned', $actor, [
                'assigned_to' => $data['assigned_to'],
            ]);

            // Notify newly assigned agent
            if (! empty($data['assigned_to'])) {
                $newAssignee = User::query()->find($data['assigned_to']);
                if ($newAssignee) {
                    $newAssignee->notify(new TicketNotification(
                        $ticket,
                        'assigned',
                        "Ticket {$ticket->ticket_number} has been assigned to you."
                    ));
                }
            }
        }
    }
}
