<?php

declare(strict_types=1);

namespace App\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

abstract class AbstractTable
{
    protected int $perPage = 10;

    protected int $maxPerPage = 100;

    /**
     * The resource used to transform table rows.
     *
     * @return class-string<JsonResource>
     */
    abstract public function resource(): string;

    /**
     * Define the base query or model class string.
     */
    abstract public function query(): Builder;

    /**
     * Define allowed columns to sort.
     */
    public function sorts(): array
    {
        return [];
    }

    /**
     * Define allowed columns to filter.
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Define columns for global search (shorthand).
     */
    public function searchColumns(): array
    {
        return [];
    }

    /**
     * Define default sort (Spatie QueryBuilder `sort` parameter).
     */
    public function defaultSort(): string|array|null
    {
        return '-created_at';
    }

    protected function build(Request $request): QueryBuilder
    {
        $filters = $this->allowedFilters();

        $builder = QueryBuilder::for($this->query(), $request)
            ->allowedSorts($this->sorts())
            ->allowedFilters($filters);

        return tap($builder, function (\Spatie\QueryBuilder\QueryBuilder $builder): void {
            $defaultSort = $this->defaultSort();

            if (filled($defaultSort)) {
                $builder->defaultSort($defaultSort);
            }
        });
    }

    final public function queryBuilder(Request $request): QueryBuilder
    {
        return $this->build($request);
    }

    /**
     * @return array<int, string|AllowedFilter>
     */
    protected function allowedFilters(): array
    {
        $filters = $this->filters();

        if ($this->shouldAddGlobalFilter($filters)) {
            $filters[] = AllowedFilter::callback('global', function (Builder $query, mixed $value): void {
                $this->applyGlobalFilter($query, (string) $value);
            });
        }

        return $filters;
    }

    protected function shouldAddGlobalFilter(array $filters): bool
    {
        if (blank($this->searchColumns())) {
            return false;
        }

        return (new \Illuminate\Support\Collection($filters))->doesntContain(function (string|AllowedFilter $filter): bool {
            if (is_string($filter)) {
                return $filter === 'global';
            }

            return $filter->getInternalName() === 'global';
        });
    }

    protected function applyGlobalFilter(Builder $query, string $value): void
    {
        $value = trim($value);

        if (blank($value)) {
            return;
        }

        $like = "%{$value}%";
        $columns = $this->searchColumns();

        $query->where(function (Builder $scopedQuery) use ($columns, $like): void {
            foreach ($columns as $column) {
                if (! Str::contains($column, '.')) {
                    $scopedQuery->orWhereLike($column, $like);

                    continue;
                }

                $relationPath = Str::beforeLast($column, '.');
                $field = Str::afterLast($column, '.');
                $firstRelation = Str::before($relationPath, '.');

                if (method_exists($scopedQuery->getModel(), $firstRelation)) {
                    $scopedQuery->orWhereHas($relationPath, function (Builder $relationQuery) use ($field, $like): void {
                        $relationQuery->whereLike($field, $like);
                    });

                    continue;
                }

                $scopedQuery->orWhereLike($column, $like);
            }
        });
    }

    final public function request(Request $request): array
    {
        $perPage = $request->integer('per_page', $this->perPage);
        $perPage = max(1, min($perPage, $this->maxPerPage));

        $paginator = $this->build($request)
            ->paginate($perPage)
            ->appends($request->query());

        $resourceClass = $this->resource();

        return [
            'data' => $resourceClass::collection($paginator->items())->resolve($request),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];
    }
}