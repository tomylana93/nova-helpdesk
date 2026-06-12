<?php

namespace App\Actions\Helpdesk;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Support\SlaCalculator;

class AssignSlaPolicy
{
    public function __construct(
        private readonly SlaCalculator $slaCalculator
    ) {}

    public function handle(Ticket $ticket): void
    {
        $policy = $this->findPolicy($ticket);

        if (! $policy instanceof SlaPolicy) {
            return;
        }

        // These columns are system-computed, not part of the mass-assignable set,
        // so they are assigned directly rather than through update().
        $ticket->first_response_due_at = $this->slaCalculator->addWorkingMinutes($ticket->submitted_at, $policy->first_response_target_minutes)->toMutable();
        $ticket->resolution_due_at = $this->slaCalculator->addWorkingMinutes($ticket->submitted_at, $policy->resolution_target_minutes)->toMutable();
        $ticket->save();
    }

    private function findPolicy(Ticket $ticket): ?SlaPolicy
    {
        // Most specific match: same type + priority
        $policy = SlaPolicy::query()
            ->where('is_active', true)
            ->where('priority', $ticket->priority)
            ->where('ticket_type', $ticket->type)
            ->first();

        if ($policy !== null) {
            return $policy;
        }

        // Fallback: any-type policy for this priority
        return SlaPolicy::query()
            ->where('is_active', true)
            ->where('priority', $ticket->priority)
            ->whereNull('ticket_type')
            ->first();
    }
}
