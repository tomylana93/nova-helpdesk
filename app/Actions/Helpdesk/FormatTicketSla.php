<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Carbon\CarbonInterface;

class FormatTicketSla
{
    /**
     * @return array{
     *     firstResponse: array{
     *         label: string,
     *         statusLabel: string,
     *         dueAt: string|null,
     *         remainingSeconds: int|null,
     *         state: 'no_sla'|'completed'|'on_track'|'due_soon'|'overdue'
     *     },
     *     resolution: array{
     *         label: string,
     *         statusLabel: string,
     *         dueAt: string|null,
     *         remainingSeconds: int|null,
     *         state: 'no_sla'|'completed'|'on_track'|'due_soon'|'overdue'
     *     }
     * }
     */
    public function handle(Ticket $ticket): array
    {
        /** @var TicketStatus $status */
        $status = $ticket->status;

        return [
            'firstResponse' => $this->target(
                __('helpdesk.ticket.sla.first_response'),
                $ticket->first_response_due_at,
                $status,
                $ticket->first_responded_at,
                $ticket->submitted_at,
            ),
            'resolution' => $this->target(
                __('helpdesk.ticket.sla.resolution'),
                $ticket->resolution_due_at,
                $status,
                $ticket->resolved_at ?? $ticket->closed_at,
                $ticket->submitted_at,
            ),
        ];
    }

    /**
     * @return array{
     *     label: string,
     *     statusLabel: string,
     *     dueAt: string|null,
     *     remainingSeconds: int|null,
     *     state: 'no_sla'|'completed'|'on_track'|'due_soon'|'overdue'
     * }
     */
    private function target(
        string $label,
        ?CarbonInterface $dueAt,
        TicketStatus $status,
        ?CarbonInterface $completedAt = null,
        ?CarbonInterface $startedAt = null,
    ): array {
        if (! $dueAt instanceof CarbonInterface) {
            return [
                'label' => $label,
                'statusLabel' => __('helpdesk.ticket.sla.no_sla'),
                'dueAt' => null,
                'remainingSeconds' => null,
                'state' => 'no_sla',
            ];
        }

        $remainingSeconds = $dueAt->getTimestamp() - ($completedAt?->getTimestamp() ?? now()->getTimestamp());

        if ($completedAt instanceof CarbonInterface || ! $status->isOpen()) {
            return [
                'label' => $label,
                'statusLabel' => $this->completedLabel($startedAt, $completedAt),
                'dueAt' => $dueAt->toJSON(),
                'remainingSeconds' => $remainingSeconds,
                'state' => 'completed',
            ];
        }

        if ($remainingSeconds < 0) {
            return [
                'label' => $label,
                'statusLabel' => __('helpdesk.ticket.sla.overdue', [
                    'duration' => $this->durationLabel(abs($remainingSeconds)),
                ]),
                'dueAt' => $dueAt->toJSON(),
                'remainingSeconds' => $remainingSeconds,
                'state' => 'overdue',
            ];
        }

        return [
            'label' => $label,
            'statusLabel' => __('helpdesk.ticket.sla.remaining', [
                'duration' => $this->durationLabel($remainingSeconds),
            ]),
            'dueAt' => $dueAt->toJSON(),
            'remainingSeconds' => $remainingSeconds,
            'state' => $remainingSeconds <= 1800 ? 'due_soon' : 'on_track',
        ];
    }

    private function completedLabel(?CarbonInterface $startedAt, ?CarbonInterface $completedAt): string
    {
        if (! $startedAt instanceof CarbonInterface || ! $completedAt instanceof CarbonInterface) {
            return __('helpdesk.ticket.sla.completed');
        }

        return __('helpdesk.ticket.sla.completed_in', [
            'duration' => $this->durationLabel(max(0, $completedAt->getTimestamp() - $startedAt->getTimestamp())),
        ]);
    }

    private function durationLabel(int $seconds): string
    {
        $minutes = max(1, (int) ceil($seconds / 60));
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours === 0) {
            return trans_choice('helpdesk.ticket.sla.minutes', $minutes, ['count' => $minutes]);
        }

        if ($remainingMinutes === 0) {
            return trans_choice('helpdesk.ticket.sla.hours', $hours, ['count' => $hours]);
        }

        return __('helpdesk.ticket.sla.hours_minutes', [
            'hours' => trans_choice('helpdesk.ticket.sla.hours', $hours, ['count' => $hours]),
            'minutes' => trans_choice('helpdesk.ticket.sla.minutes', $remainingMinutes, ['count' => $remainingMinutes]),
        ]);
    }
}
