<?php

namespace App\Actions\Reports;

use App\Actions\Reports\Support\ReportFilters;
use App\Actions\Reports\Support\ReportTicketQuery;
use App\Enums\GeneralStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Http\Resources\BranchOptionResource;
use App\Http\Resources\DepartmentOptionResource;
use App\Http\Resources\TicketCategoryOptionResource;
use App\Http\Resources\UserOptionResource;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketCategory;
use App\Models\User;
use BackedEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetReportData
{
    public function __construct(
        private readonly ReportTicketQuery $ticketQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(User $user, ReportFilters $filters, Request $request): array
    {
        return [
            'filters' => $filters->toArray(),
            'options' => $this->options($user),
            'summary' => $this->summary($user, $filters),
            'breakdowns' => $this->breakdowns($user, $filters),
            'audit' => $this->audit($user, $filters),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(User $user, ReportFilters $filters): array
    {
        $resolved = $this->ticketQuery->resolvedInPeriod($user, $filters);
        $totalResolved = $resolved->clone()->count();
        $withinDue = $resolved->clone()
            ->whereNotNull('resolution_due_at')
            ->whereColumn('resolved_at', '<=', 'resolution_due_at')
            ->count();

        return [
            'created' => $this->ticketQuery->createdInPeriod($user, $filters)->count(),
            'resolved' => $totalResolved,
            'active' => $this->ticketQuery->active($user, $filters)->count(),
            'overdue' => $this->ticketQuery->overdue($user, $filters)->count(),
            'complianceRate' => $totalResolved > 0 ? (int) round(($withinDue / $totalResolved) * 100) : 100,
            'resolvedWithinDue' => $withinDue,
            'totalResolved' => $totalResolved,
        ];
    }

    /**
     * @return array<string, list<array{key: string, label: string, value: int}>>
     */
    private function breakdowns(User $user, ReportFilters $filters): array
    {
        $period = $this->ticketQuery->createdInPeriod($user, $filters);

        return [
            'status' => $this->enumBreakdown($period, 'status', TicketStatus::cases()),
            'priority' => $this->enumBreakdown($period, 'priority', TicketPriority::cases()),
            'type' => $this->enumBreakdown($period, 'type', TicketType::cases()),
            'branch' => $this->relationshipBreakdown($period, 'branch_id', Branch::class),
            'department' => $this->relationshipBreakdown($period, 'department_id', Department::class),
            'category' => $this->relationshipBreakdown($period, 'category_id', TicketCategory::class),
            'assignee' => $this->relationshipBreakdown($period, 'assigned_to', User::class),
        ];
    }

    /**
     * @param  Builder<Ticket>  $base
     * @param  list<BackedEnum>  $cases
     * @return list<array{key: string, label: string, value: int}>
     */
    private function enumBreakdown(Builder $base, string $column, array $cases): array
    {
        $counts = $base->clone()
            ->selectRaw("{$column}, count(*) as aggregate")
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->all();

        return array_map(
            static fn (BackedEnum $case): array => [
                'key' => (string) $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : (string) $case->value,
                'value' => (int) ($counts[$case->value] ?? 0),
            ],
            $cases,
        );
    }

    /**
     * @param  Builder<Ticket>  $base
     * @param  class-string<Branch|Department|TicketCategory|User>  $model
     * @return list<array{key: string, label: string, value: int}>
     */
    private function relationshipBreakdown(Builder $base, string $column, string $model): array
    {
        $counts = $base->clone()
            ->whereNotNull($column)
            ->selectRaw("{$column}, count(*) as aggregate")
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->all();

        if ($counts === []) {
            return [];
        }

        return $model::query()
            ->whereIn('id', array_keys($counts))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => [
                'key' => $item->id,
                'label' => $item->name,
                'value' => (int) $counts[$item->id],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function audit(User $user, ReportFilters $filters): array
    {
        $activities = TicketActivity::query()
            ->with([
                'actor:id,name',
                'ticket:id,ticket_number,subject,status,priority,type,branch_id,department_id,category_id,assigned_to,created_at',
                'ticket.branch:id,name',
                'ticket.department:id,name',
                'ticket.category:id,name',
                'ticket.assignee:id,name',
            ])
            ->whereBetween('occurred_at', [$filters->period->start(), $filters->period->end()])
            ->when($filters->event !== null, fn (Builder $query): Builder => $query->where('event', $filters->event))
            ->whereHas('ticket', fn (Builder $query): Builder => $this->ticketQuery->applyFilters($query, $user, $filters))
            ->latest('occurred_at')
            ->orderBy('id')
            ->paginate(15, pageName: 'audit_page')
            ->withQueryString();

        return [
            'rows' => $this->auditRows($activities),
            'meta' => [
                'currentPage' => $activities->currentPage(),
                'lastPage' => $activities->lastPage(),
                'perPage' => $activities->perPage(),
                'total' => $activities->total(),
            ],
            'events' => $this->events($user, $filters),
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, TicketActivity>  $activities
     * @return list<array<string, mixed>>
     */
    private function auditRows(LengthAwarePaginator $activities): array
    {
        return collect($activities->items())
            ->map(fn (TicketActivity $activity): array => [
                'id' => $activity->id,
                'occurredAt' => $activity->occurred_at->toJSON(),
                'event' => $activity->event,
                'actorName' => $activity->actor?->name,
                'ticketNumber' => $activity->ticket->ticket_number,
                'ticketSubject' => $activity->ticket->subject,
                'ticketStatus' => $activity->ticket->status->value,
                'branchName' => $activity->ticket->branch?->name,
                'departmentName' => $activity->ticket->department?->name,
                'metadata' => $activity->metadata,
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function events(User $user, ReportFilters $filters): array
    {
        return TicketActivity::query()
            ->whereHas('ticket', fn (Builder $query): Builder => $this->ticketQuery->applyFilters($query, $user, $filters))
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->map(fn (string $event): array => ['value' => $event, 'label' => str($event)->headline()->toString()])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function options(User $user): array
    {
        return [
            'branches' => Branch::query()
                ->where('status', GeneralStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapInto(BranchOptionResource::class)
                ->map->resolve()
                ->all(),
            'departments' => Department::query()
                ->where('status', GeneralStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name', 'branch_id'])
                ->mapInto(DepartmentOptionResource::class)
                ->map->resolve()
                ->all(),
            'categories' => TicketCategory::query()
                ->with('parent')
                ->where('status', GeneralStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id'])
                ->mapInto(TicketCategoryOptionResource::class)
                ->map->resolve()
                ->all(),
            'assignees' => $user->hasRole(UserRole::ItAgent->value)
                ? []
                : User::query()
                    ->role(UserRole::ItAgent->value)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->mapInto(UserOptionResource::class)
                    ->map->resolve()
                    ->all(),
            'statuses' => TicketStatus::options(),
            'priorities' => TicketPriority::options(),
            'types' => TicketType::options(),
        ];
    }
}
