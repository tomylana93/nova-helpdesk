<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;

class CreateTicket
{
    public function __construct(
        private readonly AssignSlaPolicy $assignSla,
        private readonly AssignTicketToAgent $assignAgent,
        private readonly RecordTicketActivity $recordActivity,
        private readonly AttachUploadedFiles $attachFiles,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, User $requester): Ticket
    {
        $attachmentUploadIds = $data['attachment_upload_ids'] ?? [];
        unset($data['attachment_upload_ids']);

        $assetIds = $data['asset_ids'] ?? [];
        unset($data['asset_ids']);

        $type = TicketType::from($data['type']);
        $initialStatus = $type === TicketType::ServiceRequest
            ? TicketStatus::PendingApproval
            : TicketStatus::Open;

        // Organisation context is always inherited from the requester's profile, never asked
        // on the form. Requester accounts are guaranteed to have branch + department.
        $ticket = Ticket::query()->create([
            ...$data,
            'branch_id' => $requester->branch_id,
            'department_id' => $requester->department_id,
            'requester_id' => $requester->id,
            'status' => $initialStatus,
        ]);

        if (! empty($assetIds)) {
            $ticket->assets()->sync($assetIds);
        }

        $this->assignSla->handle($ticket);
        $this->recordActivity->handle($ticket, 'created', $requester);

        // Promote and attach files
        $this->attachFiles->handle($ticket, $attachmentUploadIds);

        // Notify the requester of their submission.
        $requester->notify(new TicketNotification($ticket, 'created', "Your ticket {$ticket->ticket_number} has been created."));

        // Auto-assign to an active agent and notify only that agent. Super admins are never a
        // target. If no active agent exists the ticket stays unassigned (surfaced in the Unassigned list).
        $this->assignToAgent($ticket, $requester);

        return $ticket;
    }

    private function assignToAgent(Ticket $ticket, User $actor): void
    {
        $assignee = $this->assignAgent->handle($ticket);

        if (! $assignee instanceof User) {
            return;
        }

        $ticket->update(['assigned_to' => $assignee->id]);
        $this->recordActivity->handle($ticket, 'assigned', $actor, ['assigned_to' => $assignee->id]);

        if ($ticket->status === TicketStatus::PendingApproval) {
            $assignee->notify(new TicketNotification($ticket, 'approval_request', "Service request {$ticket->ticket_number} requires your approval."));

            return;
        }

        $assignee->notify(new TicketNotification($ticket, 'assigned', "New ticket {$ticket->ticket_number} has been assigned to you."));
    }
}
