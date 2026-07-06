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
     * @param  array{
     *     subject?: string,
     *     description?: string,
     *     status?: string,
     *     priority?: string,
     *     assigned_to?: string|null,
     *     branch_id?: string|null,
     *     department_id?: string|null,
     *     category_id?: string,
     *     attachment_upload_ids?: list<string>,
     *     asset_ids?: list<string>
     * }  $data
     */
    public function handle(Ticket $ticket, array $data, User $actor): void
    {
        $attachmentUploadIds = $data['attachment_upload_ids'] ?? [];
        unset($data['attachment_upload_ids']);

        $assetIds = $data['asset_ids'] ?? null;
        unset($data['asset_ids']);

        $oldAssignee = $ticket->assigned_to;

        // Status is owned by the state machine, not a blind column write.
        $newStatus = isset($data['status']) ? TicketStatus::from($data['status']) : null;
        unset($data['status']);

        $ticket->update($data);

        if ($assetIds !== null) {
            $ticket->assets()->sync($assetIds);
        }

        // Promote and attach files
        $this->attachFiles->handle($ticket, $attachmentUploadIds);

        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $oldAssignee) {
            $this->recordActivity->handle($ticket, 'assigned', $actor, [
                'assigned_to' => $data['assigned_to'],
            ]);

            if (isset($data['assigned_to']) && $data['assigned_to'] !== '') {
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
