<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Enums\GeneralStatus;
use App\Http\Resources\BranchOptionResource;
use App\Models\Asset;
use App\Models\Branch;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<Asset>
 */
class AssetTable extends AbstractTable
{
    /**
     * @return Builder<Asset>
     */
    protected function query(): Builder
    {
        return Asset::query()
            ->with(['branch', 'user'])
            ->select([
                'id',
                'asset_tag',
                'name',
                'category',
                'status',
                'branch_id',
                'user_id',
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
        $branchOptions = array_values(Branch::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(BranchOptionResource::class)
            ->map->resolve()
            ->all());

        return [
            $this->searchFilter(
                'search',
                AllowedFilter::custom('search', new GlobalSearchFilter(['asset_tag', 'name'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'category',
                AllowedFilter::exact('category'),
                __('admin.master_data.asset.label.category'),
                AssetCategory::options(),
                __('datatable.label.all_categories'),
                __('admin.master_data.asset.label.category'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status'),
                __('admin.master_data.asset.label.status'),
                AssetStatus::options(),
                __('datatable.label.all_statuses'),
                __('admin.master_data.asset.label.status'),
            ),
            $this->selectFilter(
                'branch_id',
                AllowedFilter::exact('branch_id'),
                __('admin.master_data.asset.label.branch'),
                $branchOptions,
                __('datatable.label.all_branches'),
                __('admin.master_data.asset.label.branch'),
            ),
        ];
    }

    /**
     * @return list<AllowedSort|string>
     */
    protected function allowedSorts(): array
    {
        return [
            'asset_tag',
            'name',
            'category',
            'status',
            'created_at',
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, Asset>  $paginator
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
    public function row(Asset $asset): array
    {
        /** @var AssetStatus $status */
        $status = $asset->status;

        /** @var AssetCategory $category */
        $category = $asset->category;

        return [
            'id' => $asset->getKey(),
            'assetTag' => $asset->asset_tag,
            'name' => $asset->name,
            'category' => $category->value,
            'categoryLabel' => $category->label(),
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'statusVariant' => $status->variant(),
            'branchName' => $asset->branch?->name,
            'userName' => $asset->user?->name,
            'createdAt' => $asset->created_at?->toJSON(),
        ];
    }
}
