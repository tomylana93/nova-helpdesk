<?php

namespace App\Actions\Helpdesk;

use App\Models\SlaPolicy;
use App\Models\Ticket;

class AssignSlaPolicy
{
    public function handle(Ticket $ticket): void
    {
        $policy = $this->findPolicy($ticket);

        if (! $policy instanceof SlaPolicy) {
            return;
        }

        $ticket->update([
            'first_response_due_at' => $ticket->submitted_at->addMinutes($policy->first_response_target_minutes),
            'resolution_due_at' => $ticket->submitted_at->addMinutes($policy->resolution_target_minutes),
        ]);
    }

    private function findPolicy(Ticket $ticket): ?SlaPolicy
    {
        // Most specific match: same type + priority + queue
        if ($ticket->queue_id !== null) {
            $policy = SlaPolicy::query()
                ->where('is_active', true)
                ->where('priority', $ticket->priority)
                ->where('ticket_type', $ticket->type)
                ->where('queue_id', $ticket->queue_id)
                ->first();

            if ($policy !== null) {
                return $policy;
            }
        }

        // Match: same type + priority, no queue restriction
        $policy = SlaPolicy::query()
            ->where('is_active', true)
            ->where('priority', $ticket->priority)
            ->where('ticket_type', $ticket->type)
            ->whereNull('queue_id')
            ->first();

        if ($policy !== null) {
            return $policy;
        }

        // Fallback: any-type policy for this priority
        return SlaPolicy::query()
            ->where('is_active', true)
            ->where('priority', $ticket->priority)
            ->whereNull('ticket_type')
            ->whereNull('queue_id')
            ->first();
    }
}
