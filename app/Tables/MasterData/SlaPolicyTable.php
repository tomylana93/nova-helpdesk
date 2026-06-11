<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\TicketPriority;
use App\Enums\TicketType;
use App\Models\SlaPolicy;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<SlaPolicy>
 */
class SlaPolicyTable extends AbstractTable
{
    /**
     * @return Builder<SlaPolicy>
     */
    protected function query(): Builder
    {
        return SlaPolicy::query()
            ->select([
                'id',
                'name',
                'ticket_type',
                'priority',
                'first_response_target_minutes',
                'resolution_target_minutes',
                'is_active',
                'created_at',
            ]);
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
                AllowedFilter::custom('search', new GlobalSearchFilter(['name'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'ticket_type',
                AllowedFilter::exact('ticket_type'),
                __('admin.master_data.sla_policy.label.ticket_type'),
                TicketType::options(),
                __('admin.master_data.sla_policy.label.all_types'),
                __('admin.master_data.sla_policy.label.ticket_type'),
            ),
            $this->selectFilter(
                'priority',
                AllowedFilter::exact('priority'),
                __('admin.master_data.sla_policy.label.priority'),
                TicketPriority::options(),
                __('datatable.label.all_priorities'),
                __('admin.master_data.sla_policy.label.priority'),
            ),
        ];
    }

    /**
     * @return list<AllowedSort|string>
     */
    protected function allowedSorts(): array
    {
        return ['name', 'priority', 'is_active', 'created_at'];
    }

    /**
     * @param  LengthAwarePaginator<int, SlaPolicy>  $paginator
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
    public function row(SlaPolicy $policy): array
    {
        return [
            'id' => $policy->getKey(),
            'name' => $policy->name,
            'ticketType' => $policy->ticket_type?->value,
            'ticketTypeLabel' => $policy->ticket_type?->label() ?? '—',
            'priority' => $policy->priority->value,
            'priorityLabel' => $policy->priority->label(),
            'firstResponseTargetMinutes' => $policy->first_response_target_minutes,
            'resolutionTargetMinutes' => $policy->resolution_target_minutes,
            'isActive' => $policy->is_active,
            'createdAt' => $policy->created_at?->toJSON(),
        ];
    }
}
