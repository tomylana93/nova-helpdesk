<?php

namespace App\Exports;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Spatie\Permission\Models\Role;

/**
 * @implements WithMapping<User>
 */
class UsersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Builder<User>  $query
     * @param  list<string>|null  $visibleColumns
     */
    public function __construct(
        private readonly Builder $query,
        private readonly ?array $visibleColumns = null,
    ) {}

    /**
     * @return Builder<User>
     */
    public function query(): Builder
    {
        return $this->query->with(['roles', 'branch', 'department']);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return array_map(
            fn (string $column): string => $this->columnHeadings()[$column],
            $this->resolveColumns(),
        );
    }

    /**
     * @return list<string|null>
     */
    public function map(mixed $row): array
    {
        /** @var User $user */
        $user = $row;

        return array_map(
            fn (string $column): ?string => $this->valueForColumn($user, $column),
            $this->resolveColumns(),
        );
    }

    /**
     * @return list<string>
     */
    private function resolveColumns(): array
    {
        return $this->visibleColumns ?? [
            'name',
            'email',
            'phone',
            'role',
            'status',
            'branch',
            'department',
            'last_login_at',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function columnHeadings(): array
    {
        return [
            'name' => (string) __('admin.master_data.user.label.name'),
            'email' => (string) __('admin.master_data.user.label.email'),
            'phone' => (string) __('admin.master_data.user.label.phone'),
            'role' => (string) __('admin.master_data.user.label.role'),
            'status' => (string) __('admin.master_data.user.label.status'),
            'branch' => (string) __('admin.master_data.branch.label.singular'),
            'department' => (string) __('admin.master_data.department.label.singular'),
            'last_login_at' => (string) __('admin.master_data.user.label.last_login_at'),
        ];
    }

    private function valueForColumn(User $user, string $column): ?string
    {
        return match ($column) {
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $this->roleLabel($user),
            'status' => $this->statusLabel($user),
            'branch' => $user->branch?->name,
            'department' => $user->department?->name,
            'last_login_at' => $this->formatDateTime($user->last_login_at),
            default => null,
        };
    }

    private function roleLabel(User $user): ?string
    {
        /** @var Role|null $role */
        $role = $user->roles->first();

        return $role instanceof Role ? (UserRole::tryFrom($role->name)?->label() ?? $role->name) : null;
    }

    private function statusLabel(User $user): string
    {
        /** @var UserStatus $status */
        $status = $user->status;

        return $status->label();
    }

    private function formatDateTime(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format('Y-m-d H:i:s') : null;
    }
}
