<?php

namespace App\Exports\Reports;

use App\Actions\Reports\Support\ReportFilters;
use App\Actions\Reports\Support\ReportTicketQuery;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<TicketActivity>
 */
class AuditActivitiesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly ReportFilters $filters,
        private readonly ReportTicketQuery $ticketQuery,
    ) {}

    /**
     * @return Builder<TicketActivity>
     */
    public function query(): Builder
    {
        return TicketActivity::query()
            ->with([
                'actor:id,name',
                'ticket:id,ticket_number,subject,status,priority,type,branch_id,department_id,category_id,assigned_to',
                'ticket.branch:id,name',
                'ticket.department:id,name',
                'ticket.category:id,name',
                'ticket.assignee:id,name',
            ])
            ->whereBetween('occurred_at', [$this->filters->period->start(), $this->filters->period->end()])
            ->when($this->filters->event !== null, fn (Builder $query): Builder => $query->where('event', $this->filters->event))
            ->whereHas('ticket', fn (Builder $query): Builder => $this->ticketQuery->applyFilters($query, $this->user, $this->filters))
            ->latest('occurred_at')
            ->orderBy('id');
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Occurred At',
            'Event',
            'Actor',
            'Ticket Number',
            'Ticket Subject',
            'Branch',
            'Department',
            'Category',
            'Assignee',
            'Metadata',
        ];
    }

    /**
     * @return list<string|null>
     */
    public function map(mixed $row): array
    {
        /** @var TicketActivity $activity */
        $activity = $row;

        return [
            $activity->occurred_at->toDateTimeString(),
            str($activity->event)->headline()->toString(),
            $activity->actor?->name,
            $activity->ticket->ticket_number,
            $activity->ticket->subject,
            $activity->ticket->branch?->name,
            $activity->ticket->department?->name,
            $activity->ticket->category?->name,
            $activity->ticket->assignee?->name,
            $activity->metadata === null ? null : json_encode($activity->metadata, JSON_THROW_ON_ERROR),
        ];
    }
}
