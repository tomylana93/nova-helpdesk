<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;
use Carbon\CarbonInterface;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('helpdesk:check-sla')]
#[Description('Check open tickets for SLA warnings and breaches and trigger notifications')]
class CheckSlaEscalation extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $warningWindow = $now->copy()->addMinutes(30);

        // 1. Process warnings (within 30 minutes of SLA due but not yet warning-notified)
        $this->processSlaWarnings($now, $warningWindow);

        // 2. Process breaches (past due but not yet breach-notified)
        $this->processSlaBreaches($now);

        return self::SUCCESS;
    }

    private function processSlaWarnings(CarbonInterface $now, CarbonInterface $warningWindow): void
    {
        $openTickets = Ticket::query()
            ->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
            ->where(function ($q) use ($now, $warningWindow): void {
                $q->where(function ($sub) use ($now, $warningWindow): void {
                    $sub->whereNotNull('first_response_due_at')
                        ->where('first_response_due_at', '>', $now)
                        ->where('first_response_due_at', '<=', $warningWindow);
                })->orWhere(function ($sub) use ($now, $warningWindow): void {
                    $sub->whereNotNull('resolution_due_at')
                        ->where('resolution_due_at', '>', $now)
                        ->where('resolution_due_at', '<=', $warningWindow);
                });
            })
            ->get();

        foreach ($openTickets as $ticket) {
            // Check first response SLA warning
            if ($ticket->first_response_due_at && $ticket->first_response_due_at->isBetween($now, $warningWindow)) {
                $alreadySent = $ticket->activities()
                    ->where('event', 'sla_first_response_warning_sent')
                    ->exists();

                if (! $alreadySent) {
                    $this->triggerSlaWarning($ticket, 'first_response', "Ticket {$ticket->ticket_number} first response SLA is due in less than 30 minutes.");
                    $ticket->activities()->create([
                        'event' => 'sla_first_response_warning_sent',
                        'occurred_at' => now(),
                    ]);
                }
            }

            // Check resolution SLA warning
            if ($ticket->resolution_due_at && $ticket->resolution_due_at->isBetween($now, $warningWindow)) {
                $alreadySent = $ticket->activities()
                    ->where('event', 'sla_resolution_warning_sent')
                    ->exists();

                if (! $alreadySent) {
                    $this->triggerSlaWarning($ticket, 'resolution', "Ticket {$ticket->ticket_number} resolution SLA is due in less than 30 minutes.");
                    $ticket->activities()->create([
                        'event' => 'sla_resolution_warning_sent',
                        'occurred_at' => now(),
                    ]);
                }
            }
        }
    }

    private function processSlaBreaches(CarbonInterface $now): void
    {
        $breachedTickets = Ticket::query()
            ->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
            ->where(function ($q) use ($now): void {
                $q->where(function ($sub) use ($now): void {
                    $sub->whereNotNull('first_response_due_at')
                        ->where('first_response_due_at', '<=', $now);
                })->orWhere(function ($sub) use ($now): void {
                    $sub->whereNotNull('resolution_due_at')
                        ->where('resolution_due_at', '<=', $now);
                });
            })
            ->get();

        foreach ($breachedTickets as $ticket) {
            // Check first response SLA breach
            if ($ticket->first_response_due_at && $ticket->first_response_due_at->isBefore($now)) {
                $alreadySent = $ticket->activities()
                    ->where('event', 'sla_first_response_breached')
                    ->exists();

                if (! $alreadySent) {
                    $this->triggerSlaBreach($ticket, 'first_response', "Ticket {$ticket->ticket_number} first response SLA has been breached!");
                    $ticket->activities()->create([
                        'event' => 'sla_first_response_breached',
                        'occurred_at' => now(),
                    ]);
                }
            }

            // Check resolution SLA breach
            if ($ticket->resolution_due_at && $ticket->resolution_due_at->isBefore($now)) {
                $alreadySent = $ticket->activities()
                    ->where('event', 'sla_resolution_breached')
                    ->exists();

                if (! $alreadySent) {
                    $this->triggerSlaBreach($ticket, 'resolution', "Ticket {$ticket->ticket_number} resolution SLA has been breached!");
                    $ticket->activities()->create([
                        'event' => 'sla_resolution_breached',
                        'occurred_at' => now(),
                    ]);
                }
            }
        }
    }

    private function triggerSlaWarning(Ticket $ticket, string $slaType, string $message): void
    {
        $this->notifyAssignee($ticket, 'sla_warning', $message, $slaType);
    }

    private function triggerSlaBreach(Ticket $ticket, string $slaType, string $message): void
    {
        $this->notifyAssignee($ticket, 'sla_breached', $message, $slaType);
    }

    /**
     * Notify the assigned agent only. Unassigned tickets have no SLA target — there is no role
     * fan-out and super admins are never notified; they surface via the agent's Overdue inbox filter.
     */
    private function notifyAssignee(Ticket $ticket, string $type, string $message, string $slaType): void
    {
        if (empty($ticket->assigned_to)) {
            return;
        }

        $assignee = User::query()->find($ticket->assigned_to);
        $assignee?->notify(new TicketNotification($ticket, $type, $message, ['sla_type' => $slaType]));
    }
}
