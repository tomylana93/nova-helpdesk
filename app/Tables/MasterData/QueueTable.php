<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\GeneralStatus;
use App\Models\Queue;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<Queue>
 */
class QueueTable extends AbstractTable
{
    /**
     * @return Builder<Queue>
     */
    protected function query(): Builder
    {
        return Queue::query()
            ->select([
                'id',
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
        return [
            $this->searchFilter(
                'search',
                AllowedFilter::custom('search', new GlobalSearchFilter(['name', 'description'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status'),
                __('admin.master_data.queue.label.status'),
                GeneralStatus::options(),
                __('datatable.label.all_statuses'),
                __('admin.master_data.queue.label.status'),
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
     * @param  LengthAwarePaginator<int, Queue>  $paginator
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
    public function row(Queue $queue): array
    {
        /** @var GeneralStatus $status */
        $status = $queue->status;

        return [
            'id' => $queue->getKey(),
            'name' => $queue->name,
            'description' => $queue->description,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'createdAt' => $queue->created_at?->toJSON(),
        ];
    }
}
