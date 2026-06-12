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
        private readonly TransitionTicketStatus $transition,
        private readonly AttachUploadedFiles $attachFiles,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Ticket $ticket, array $data, User $actor): void
    {
        $attachmentUploadIds = $data['attachment_upload_ids'] ?? [];
        unset($data['attachment_upload_ids']);

        $oldAssignee = $ticket->assigned_to;

        // Status is owned by the state machine, not a blind column write.
        $newStatus = isset($data['status']) ? TicketStatus::from($data['status']) : null;
        unset($data['status']);

        $ticket->update($data);

        // Promote and attach files
        $this->attachFiles->handle($ticket, $attachmentUploadIds);

        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $oldAssignee) {
            $this->recordActivity->handle($ticket, 'assigned', $actor, [
                'assigned_to' => $data['assigned_to'],
            ]);

            if (! empty($data['assigned_to'])) {
                $newAssignee = User::query()->find($data['assigned_to']);
                $newAssignee?->notify(new TicketNotification(
                    $ticket,
                    'assigned',
                    "Ticket {$ticket->ticket_number} has been assigned to you."
                ));
            }
        }

        if ($newStatus !== null) {
            $this->transition->handle($ticket, $newStatus, $actor);
        }
    }
}
