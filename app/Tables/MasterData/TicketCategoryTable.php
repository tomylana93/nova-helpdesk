<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\GeneralStatus;
use App\Http\Resources\TicketCategoryOptionResource;
use App\Models\TicketCategory;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<TicketCategory>
 */
class TicketCategoryTable extends AbstractTable
{
    /**
     * @return Builder<TicketCategory>
     */
    protected function query(): Builder
    {
        return TicketCategory::query()
            ->with('parent:id,name')
            ->select([
                'id',
                'parent_id',
                'name',
                'description',
                'status',
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
        $parentCategoryOptions = TicketCategory::query()
            ->whereNull('parent_id')
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(TicketCategoryOptionResource::class)
            ->map->resolve()
            ->all();

        return [
            $this->searchFilter(
                'search',
                AllowedFilter::custom('search', new GlobalSearchFilter(['name'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'parent_id',
                AllowedFilter::exact('parent_id'),
                __('admin.master_data.ticket_category.label.parent'),
                $parentCategoryOptions,
                __('datatable.label.all_parents'),
                __('admin.master_data.ticket_category.label.parent'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status'),
                __('admin.master_data.ticket_category.label.status'),
                GeneralStatus::options(),
                __('datatable.label.all_statuses'),
                __('admin.master_data.ticket_category.label.status'),
            ),
        ];
    }

    /**
     * @return list<AllowedSort|string>
     */
    protected function allowedSorts(): array
    {
        return [
            'name',
            'status',
            'created_at',
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, TicketCategory>  $paginator
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
    public function row(TicketCategory $ticketCategory): array
    {
        /** @var GeneralStatus $status */
        $status = $ticketCategory->status;

        return [
            'id' => $ticketCategory->getKey(),
            'parentId' => $ticketCategory->parent_id,
            'parentName' => $ticketCategory->parent?->name,
            'name' => $ticketCategory->name,
            'description' => $ticketCategory->description,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'createdAt' => $ticketCategory->created_at?->toJSON(),
        ];
    }
}
