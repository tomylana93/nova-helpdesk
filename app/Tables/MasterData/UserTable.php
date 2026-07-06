<?php

declare(strict_types=1);

namespace App\Tables\MasterData;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Resources\BranchOptionResource;
use App\Http\Resources\DepartmentOptionResource;
use App\Models\Branch;
use App\Models\Department;
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
            ->with(['roles:id,name', 'branch:id,name', 'department:id,name'])
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.status',
                'users.created_at',
                'users.branch_id',
                'users.department_id',
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

        $departmentOptions = array_values(Department::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(DepartmentOptionResource::class)
            ->map->resolve()
            ->all());

        return [
            $this->searchFilter(
                'search',
                AllowedFilter::custom('search', new GlobalSearchFilter(['users.name', 'users.email'])),
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
                'branch_id',
                AllowedFilter::exact('branch_id', 'users.branch_id'),
                __('admin.master_data.user.label.branch'),
                $branchOptions,
                __('datatable.label.all_branches'),
                __('admin.master_data.user.label.branch'),
            ),
            $this->selectFilter(
                'department_id',
                AllowedFilter::exact('department_id', 'users.department_id'),
                __('admin.master_data.user.label.department'),
                $departmentOptions,
                __('datatable.label.all_departments'),
                __('admin.master_data.user.label.department'),
            ),
            $this->selectFilter(
                'status',
                AllowedFilter::exact('status', 'users.status'),
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
            AllowedSort::field('name', 'users.name'),
            AllowedSort::field('email', 'users.email'),
            AllowedSort::field('status', 'users.status'),
            AllowedSort::field('created_at', 'users.created_at'),
            AllowedSort::callback('branch', function (Builder $query, bool $descending): void {
                $query->orderBy(
                    Branch::query()
                        ->select('name')
                        ->whereColumn('branches.id', 'users.branch_id'),
                    $descending ? 'desc' : 'asc',
                );
            }),
            AllowedSort::callback('department', function (Builder $query, bool $descending): void {
                $query->orderBy(
                    Department::query()
                        ->select('name')
                        ->whereColumn('departments.id', 'users.department_id'),
                    $descending ? 'desc' : 'asc',
                );
            }),
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
            'branchName' => $user->branch?->name,
            'departmentName' => $user->department?->name,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'createdAt' => $user->created_at?->toJSON(),
        ];
    }
}
