<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Tables\AbstractTable;
use App\Tables\Filters\GlobalSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @extends AbstractTable<User>
 */
class UserTable extends AbstractTable
{
    /**
     * @return Builder<User>
     */
    protected function query(): Builder
    {
        return User::query()
            ->with('roles:id,name')
            ->select([
                'id',
                'name',
                'email',
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
                AllowedFilter::custom('search', new GlobalSearchFilter(['name', 'email'])),
                __('datatable.placeholder.search'),
            ),
            $this->selectFilter(
                'role',
                AllowedFilter::callback('role', function (Builder $query, mixed $value): void {
                    $query->whereHas('roles', function (Builder $query) use ($value): void {
                        $query->where('name', $value);
                    });
                }),
                __('admin.master_data.user.label.role'),
                UserRole::options(),
                __('datatable.label.all_roles'),
                __('admin.master_data.user.label.role'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status'),
                __('admin.master_data.user.label.status'),
                UserStatus::options(),
                __('datatable.label.all_statuses'),
                __('admin.master_data.user.label.status'),
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
            'email',
            'status',
            'created_at',
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, User>  $paginator
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
    public function row(User $user): array
    {
        /** @var UserStatus $status */
        $status = $user->status;
        /** @var Role|null $role */
        $role = $user->roles->first();
        $roleName = $role?->name;
        $roleLabel = $roleName !== null ? UserRole::tryFrom($roleName)?->label() : null;

        return [
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roleName,
            'roleLabel' => $roleLabel,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'createdAt' => $user->created_at?->toJSON(),
        ];
    }
}
