<?php

declare(strict_types=1);

namespace App\Tables;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @template TModel of Model
 */
abstract class AbstractTable
{
    public function __construct(
        protected readonly Request $request,
    ) {}

    /**
     * @return Builder<TModel>
     */
    abstract protected function query(): Builder;

    /**
     * @param  LengthAwarePaginator<int, TModel>  $paginator
     * @return list<array<string, mixed>>
     */
    abstract protected function rows(LengthAwarePaginator $paginator): array;

    /**
     * @return list<array{key: string, filter: AllowedFilter|string, definition: array<string, mixed>}>
     */
    protected function filterConfigurations(): array
    {
        return [];
    }

    /**
     * @return list<AllowedSort|string>
     */
    protected function allowedSorts(): array
    {
        return [];
    }

    protected function defaultSort(): string|AllowedSort|null
    {
        return null;
    }

    /**
     * @return list<int>
     */
    public function perPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function defaultPerPage(): int
    {
        return 10;
    }

    protected function maxPerPage(): int
    {
        return 100;
    }

    /**
     * @return QueryBuilder<TModel>
     */
    public function builder(): QueryBuilder
    {
        $builder = QueryBuilder::for($this->query())
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(...$this->allowedSorts());

        $defaultSort = $this->defaultSort();

        if ($defaultSort !== null) {
            $builder->defaultSort($defaultSort);
        }

        return $builder;
    }

    /**
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(): LengthAwarePaginator
    {
        return $this->builder()
            ->paginate($this->perPage())
            ->withQueryString();
    }

    public function perPage(): int
    {
        $requested = max(1, (int) $this->request->integer('per_page', $this->defaultPerPage()));

        return min($requested, $this->maxPerPage());
    }

    public function sort(): ?string
    {
        $sort = trim((string) $this->request->query('sort', ''));

        if ($sort !== '') {
            return $sort;
        }

        $defaultSort = $this->defaultSort();

        return is_string($defaultSort) ? $defaultSort : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $filters = $this->request->query('filter', []);
        $normalizedFilters = $this->defaultFilters();

        if (! is_array($filters)) {
            return $normalizedFilters;
        }

        foreach ($this->filterDefinitions() as $definition) {
            $key = Arr::get($definition, 'key');

            if (! is_string($key)) {
                continue;
            }

            if (! array_key_exists($key, $filters)) {
                continue;
            }

            $normalizedFilters[$key] = $filters[$key];
        }

        return $normalizedFilters;
    }

    /**
     * @return array{
     *     rows: list<array<string, mixed>>,
     *     meta: array<string, int|null>,
     *     state: array<string, mixed>,
     *     schema: array{filters: list<array<string, mixed>>}
     * }
     */
    public function toPayload(): array
    {
        $paginator = $this->paginate();

        return [
            'rows' => $this->rows($paginator),
            'meta' => $this->meta($paginator),
            'state' => [
                'filters' => $this->filters(),
                'sort' => $this->sort(),
                'perPage' => $paginator->perPage(),
                'perPageOptions' => $this->perPageOptions(),
            ],
            'schema' => [
                'filters' => $this->filterDefinitions(),
            ],
        ];
    }

    /**
     * @return list<AllowedFilter|string>
     */
    protected function allowedFilters(): array
    {
        return array_map(
            static fn (array $configuration): AllowedFilter|string => $configuration['filter'],
            $this->filterConfigurations(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function filterDefinitions(): array
    {
        return array_map(
            static fn (array $configuration): array => $configuration['definition'],
            $this->filterConfigurations(),
        );
    }

    /**
     * @return array<string, null>
     */
    protected function defaultFilters(): array
    {
        $filters = [];

        foreach ($this->filterDefinitions() as $definition) {
            $key = Arr::get($definition, 'key');

            if (is_string($key)) {
                $filters[$key] = null;
            }
        }

        return $filters;
    }

    /**
     * @return array{key: string, filter: AllowedFilter|string, definition: array<string, mixed>}
     */
    protected function searchFilter(
        string $key,
        AllowedFilter|string $filter,
        ?string $placeholder = null,
        int $minimumSearchLength = 3,
    ): array {
        return [
            'key' => $key,
            'filter' => $filter,
            'definition' => array_filter([
                'key' => $key,
                'type' => 'search',
                'placeholder' => $placeholder,
                'minimumSearchLength' => $minimumSearchLength,
            ], static fn (mixed $value): bool => $value !== null),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $options
     * @return array{key: string, filter: AllowedFilter|string, definition: array<string, mixed>}
     */
    protected function selectFilter(
        string $key,
        AllowedFilter|string $filter,
        string $label,
        array $options,
        ?string $allLabel = null,
        ?string $placeholder = null,
    ): array {
        return [
            'key' => $key,
            'filter' => $filter,
            'definition' => array_filter([
                'key' => $key,
                'type' => 'select',
                'label' => $label,
                'options' => $options,
                'allLabel' => $allLabel,
                'placeholder' => $placeholder,
            ], static fn (mixed $value): bool => $value !== null),
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, TModel>  $paginator
     * @return array<string, int|null>
     */
    private function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];
    }
}
