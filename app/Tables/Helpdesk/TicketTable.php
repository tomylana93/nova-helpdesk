<?php

declare(strict_types=1);

namespace App\Tables\Helpdesk;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<Ticket>
 */
class TicketTable extends AbstractTable
{
    /**
     * @return Builder<Ticket>
     */
    protected function query(): Builder
    {
        $user = $this->request->user();

        $query = Ticket::query()
            ->with([
                'requester:id,name',
                'assignee:id,name',
                'branch:id,name',
            ])
            ->select([
                'id',
                'ticket_number',
                'type',
                'subject',
                'status',
                'priority',
                'requester_id',
                'assigned_to',
                'branch_id',
                'submitted_at',
                'first_response_due_at',
                'resolution_due_at',
                'created_at',
            ]);

        if ($user && $user->hasRole(UserRole::Requester)) {
            $query->where('requester_id', $user->id);
        }

        return $query;
    }

    protected function defaultSort(): string|AllowedSort|null
    {
        return '-created_at';
    }

    protected function filterConfigurations(): array
    {
        $filters = [
            $this->searchFilter(
                'search',
                AllowedFilter::custom('search', new GlobalSearchFilter(['subject', 'ticket_number'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status'),
                __('helpdesk.ticket.label.status'),
                TicketStatus::options(),
                __('datatable.label.all_statuses'),
                __('helpdesk.ticket.label.status'),
            ),
            $this->selectFilter(
                'type',
                AllowedFilter::exact('type'),
                __('helpdesk.ticket.label.type'),
                TicketType::options(),
                __('helpdesk.ticket.label.all_types'),
                __('helpdesk.ticket.label.type'),
            ),
            $this->selectFilter(
                'priority',
                AllowedFilter::exact('priority'),
                __('helpdesk.ticket.label.priority'),
                TicketPriority::options(),
                __('helpdesk.ticket.label.all_priorities'),
                __('helpdesk.ticket.label.priority'),
            ),
        ];

        // Agent inbox quick-filters (requesters only ever see their own tickets).
        $user = $this->request->user();
        if ($user && ($user->hasRole(UserRole::ItAgent) || $user->hasRole(UserRole::SuperAdmin))) {
            $filters[] = $this->selectFilter(
                'view',
                $this->viewFilter($user->id),
                __('helpdesk.ticket.label.view'),
                [
                    ['value' => 'mine', 'label' => __('helpdesk.ticket.view.mine')],
                    ['value' => 'unassigned', 'label' => __('helpdesk.ticket.view.unassigned')],
                    ['value' => 'overdue', 'label' => __('helpdesk.ticket.view.overdue')],
                ],
                __('helpdesk.ticket.view.all'),
                __('helpdesk.ticket.label.view'),
            );
        }

        return $filters;
    }

    /**
     * Inbox scope filter: assigned to me / unassigned / overdue (past SLA and still open).
     */
    private function viewFilter(string $userId): AllowedFilter
    {
        return AllowedFilter::callback('view', function (Builder $query, mixed $value) use ($userId): void {
            match ($value) {
                'mine' => $query->where('assigned_to', $userId),
                'unassigned' => $query->whereNull('assigned_to'),
                'overdue' => $query
                    ->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                    ->whereNotNull('resolution_due_at')
                    ->where('resolution_due_at', '<', now()),
                default => null,
            };
        });
    }

    /**
     * @return list<AllowedSort|string>
     */
    protected function allowedSorts(): array
    {
        return [
            'ticket_number',
            'subject',
            'status',
            'priority',
            'created_at',
            'submitted_at',
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, Ticket>  $paginator
     * @return list<array<string, mixed>>
     */
    protected function rows(LengthAwarePaginator $paginator): array
    {
        $rows = [];

        foreach ($paginator->items() as $item) {
            $rows[] = $this->row($item);
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function row(Ticket $ticket): array
    {
        /** @var TicketStatus $status */
        $status = $ticket->status;

        /** @var TicketPriority $priority */
        $priority = $ticket->priority;

        /** @var TicketType $type */
        $type = $ticket->type;

        return [
            'id' => $ticket->getKey(),
            'ticketNumber' => $ticket->ticket_number,
            'type' => $type->value,
            'typeLabel' => $type->label(),
            'subject' => $ticket->subject,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'statusVariant' => $status->variant(),
            'priority' => $priority->value,
            'priorityLabel' => $priority->label(),
            'priorityVariant' => $priority->variant(),
            'requesterName' => $ticket->requester->name,
            'assigneeName' => $ticket->assignee?->name,
            'branchName' => $ticket->branch?->name,
            'sla' => [
                'firstResponse' => $this->slaTarget(
                    __('helpdesk.ticket.sla.first_response'),
                    $ticket->first_response_due_at,
                    $status,
                ),
                'resolution' => $this->slaTarget(
                    __('helpdesk.ticket.sla.resolution'),
                    $ticket->resolution_due_at,
                    $status,
                ),
            ],
            'submittedAt' => $ticket->submitted_at->toJSON(),
            'createdAt' => $ticket->created_at?->toJSON(),
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
    private function slaTarget(string $label, ?CarbonInterface $dueAt, TicketStatus $status): array
    {
        if (! $dueAt instanceof CarbonInterface) {
            return [
                'label' => $label,
                'statusLabel' => __('helpdesk.ticket.sla.no_sla'),
                'dueAt' => null,
                'remainingSeconds' => null,
                'state' => 'no_sla',
            ];
        }

        $remainingSeconds = $dueAt->getTimestamp() - now()->getTimestamp();

        if (! $status->isOpen()) {
            return [
                'label' => $label,
                'statusLabel' => __('helpdesk.ticket.sla.completed'),
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
