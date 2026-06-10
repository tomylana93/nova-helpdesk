<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\GeneralStatus;
use App\Models\Branch;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<Branch>
 */
class BranchTable extends AbstractTable
{
    /**
     * @return Builder<Branch>
     */
    protected function query(): Builder
    {
        return Branch::query()
            ->select([
                'id',
                'code',
                'name',
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
        return [
            $this->searchFilter(
                'search',
                AllowedFilter::custom('search', new GlobalSearchFilter(['code', 'name'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status'),
                __('admin.master_data.branch.label.status'),
                GeneralStatus::options(),
                __('datatable.label.all_statuses'),
                __('admin.master_data.branch.label.status'),
            ),
        ];
    }

    /**
     * @return list<AllowedSort|string>
     */
    protected function allowedSorts(): array
    {
        return [
            'code',
            'name',
            'status',
            'created_at',
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, Branch>  $paginator
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
    public function row(Branch $branch): array
    {
        /** @var GeneralStatus $status */
        $status = $branch->status;

        return [
            'id' => $branch->getKey(),
            'code' => $branch->code,
            'name' => $branch->name,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'createdAt' => $branch->created_at?->toJSON(),
        ];
    }
}
