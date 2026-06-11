<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Validation\ValidationException;

class TransitionTicketStatus
{
    public function __construct(
        private readonly RecordTicketActivity $recordActivity,
    ) {}

    /**
     * Move a ticket to a new status, enforcing the state machine, stamping lifecycle
     * timestamps, recording the activity, and notifying the counterpart party.
     *
     * @throws ValidationException when the transition is not allowed.
     */
    public function handle(Ticket $ticket, TicketStatus $to, User $actor): void
    {
        $from = $ticket->status;

        if ($from === $to) {
            return;
        }

        if (! $from->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "Cannot change ticket status from {$from->label()} to {$to->label()}.",
            ]);
        }

        $ticket->update($this->lifecycleChanges($ticket, $to));

        $this->recordActivity->handle($ticket, 'status_changed', $actor, [
            'from' => $from->value,
            'to' => $to->value,
        ]);

        $this->notifyCounterpart($ticket, $actor, $to);
    }

    /**
     * @return array<string, mixed>
     */
    private function lifecycleChanges(Ticket $ticket, TicketStatus $to): array
    {
        $changes = ['status' => $to];

        if ($to === TicketStatus::Resolved && $ticket->resolved_at === null) {
            $changes['resolved_at'] = now();
        }

        if ($to === TicketStatus::Closed && $ticket->closed_at === null) {
            $changes['closed_at'] = now();
        }

        if ($to === TicketStatus::Reopened) {
            $changes['resolved_at'] = null;
            $changes['closed_at'] = null;
        }

        return $changes;
    }

    /**
     * The actor never notifies themselves: a requester-driven change notifies the assigned
     * agent, while an agent-driven change notifies the requester.
     */
    private function notifyCounterpart(Ticket $ticket, User $actor, TicketStatus $to): void
    {
        $message = "Ticket {$ticket->ticket_number} status changed to {$to->label()}.";

        if ($actor->id === $ticket->requester_id) {
            $agent = $ticket->assigned_to !== null ? User::query()->find($ticket->assigned_to) : null;
            $agent?->notify(new TicketNotification($ticket, 'status_changed', $message));

            return;
        }

        $ticket->requester->notify(new TicketNotification($ticket, 'status_changed', $message));
    }
}
