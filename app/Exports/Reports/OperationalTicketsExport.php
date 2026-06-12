<?php

namespace App\Exports\Reports;

use App\Actions\Reports\Support\ReportFilters;
use App\Actions\Reports\Support\ReportTicketQuery;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<Ticket>
 */
class OperationalTicketsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly ReportFilters $filters,
        private readonly ReportTicketQuery $ticketQuery,
    ) {}

    /**
     * @return Builder<Ticket>
     */
    public function query(): Builder
    {
        return $this->ticketQuery
            ->createdInPeriod($this->user, $this->filters)
            ->latest()
            ->orderBy('id');
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Ticket Number',
            'Subject',
            'Type',
            'Status',
            'Priority',
            'Requester',
            'Assignee',
            'Branch',
            'Department',
            'Category',
            'Submitted At',
            'Resolved At',
            'Resolution Due At',
            'SLA State',
        ];
    }

    /**
     * @return list<string|null>
     */
    public function map(mixed $row): array
    {
        /** @var Ticket $ticket */
        $ticket = $row;
        /** @var TicketType $type */
        $type = $ticket->type;
        /** @var TicketStatus $status */
        $status = $ticket->status;
        /** @var TicketPriority $priority */
        $priority = $ticket->priority;

        return [
            $ticket->ticket_number,
            $ticket->subject,
            $type->label(),
            $status->label(),
            $priority->label(),
            $ticket->requester->name,
            $ticket->assignee?->name,
            $ticket->branch?->name,
            $ticket->department?->name,
            $ticket->category?->name,
            $ticket->submitted_at->toDateTimeString(),
            $ticket->resolved_at?->toDateTimeString(),
            $ticket->resolution_due_at?->toDateTimeString(),
            $this->slaState($ticket),
        ];
    }

    private function slaState(Ticket $ticket): string
    {
        if ($ticket->resolved_at !== null && $ticket->resolution_due_at !== null) {
            return $ticket->resolved_at->lessThanOrEqualTo($ticket->resolution_due_at)
                ? 'Resolved within SLA'
                : 'Resolved after SLA';
        }

        if ($ticket->resolution_due_at !== null && $ticket->resolution_due_at->isPast() && $ticket->status->isOpen()) {
            return 'Overdue';
        }

        return 'On track';
    }
}
