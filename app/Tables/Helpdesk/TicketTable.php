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

    /**
     * @return list<array{key: string, filter: AllowedFilter|string, definition: array<string, mixed>}>
     */
    protected function filterConfigurations(): array
    {
        return [
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
            'submittedAt' => $ticket->submitted_at->toJSON(),
            'createdAt' => $ticket->created_at?->toJSON(),
        ];
    }
}
