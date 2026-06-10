<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\GeneralStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<Department>
 */
class DepartmentTable extends AbstractTable
{
    /**
     * @return Builder<Department>
     */
    protected function query(): Builder
    {
        return Department::query()
            ->with('branch:id,name')
            ->select([
                'id',
                'branch_id',
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
        $branchOptions = Branch::query()
            ->select(['id as value', 'name as label'])
            ->where('status', GeneralStatus::Active->value)
            ->orderBy('name')
            ->get()
            ->toArray();

        return [
            $this->searchFilter(
                'search',
                AllowedFilter::custom('search', new GlobalSearchFilter(['code', 'name'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'branch_id',
                AllowedFilter::exact('branch_id'),
                __('admin.master_data.department.label.branch'),
                $branchOptions,
                __('datatable.label.all_branches'),
                __('admin.master_data.department.label.branch'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status'),
                __('admin.master_data.department.label.status'),
                GeneralStatus::options(),
                __('datatable.label.all_statuses'),
                __('admin.master_data.department.label.status'),
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
     * @param  LengthAwarePaginator<int, Department>  $paginator
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
    public function row(Department $department): array
    {
        /** @var GeneralStatus $status */
        $status = $department->status;

        return [
            'id' => $department->getKey(),
            'branchId' => $department->branch_id,
            'branchName' => $department->branch?->name,
            'code' => $department->code,
            'name' => $department->name,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'createdAt' => $department->created_at?->toJSON(),
        ];
    }
}
