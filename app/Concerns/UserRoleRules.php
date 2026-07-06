<?php

namespace App\Concerns;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait UserRoleRules
{
    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function baseRoleRules(): array
    {
        return [
            'required',
            'string',
            Rule::enum(UserRole::class),
            Rule::exists(config('permission.table_names.roles'), 'name')
                ->where('guard_name', 'web'),
        ];
    }

    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function preventSuperAdminRoleRule(): array
    {
        if (User::query()->role(UserRole::SuperAdmin->value)->exists()) {
            return [Rule::notIn([UserRole::SuperAdmin->value])];
        }

        return [];
    }
}
