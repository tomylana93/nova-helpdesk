<?php

declare(strict_types=1);

namespace App\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * @implements Filter<Model>
 */
class GlobalSearchFilter implements Filter
{
    /**
     * @param  list<string>  $columns
     * @param  list<string>  $relationshipColumns
     */
    public function __construct(
        private readonly array $columns,
        private readonly array $relationshipColumns = [],
    ) {}

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        $search = trim($value);

        if ($search === '' || mb_strlen($search) < 3) {
            return;
        }

        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $searchValue = '%'.$search.'%';

        $query->where(function (Builder $builder) use ($operator, $searchValue): void {
            foreach ($this->columns as $column) {
                $builder->orWhere($column, $operator, $searchValue);
            }

            foreach ($this->relationshipColumns as $relationshipColumn) {
                $relation = Str::beforeLast($relationshipColumn, '.');
                $column = Str::afterLast($relationshipColumn, '.');
                if ($relation === '') {
                    continue;
                }

                if ($column === '') {
                    continue;
                }

                $builder->orWhereRelation($relation, $column, $operator, $searchValue);
            }
        });
    }
}
