<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules(),
            'role' => [
                'required',
                'string',
                Rule::enum(UserRole::class),
                Rule::exists(config('permission.table_names.roles'), 'name')
                    ->where('guard_name', 'web'),
            ],
            'branch_id' => [Rule::requiredIf($this->requiresOrganisation()), 'nullable', 'exists:branches,id'],
            'department_id' => [Rule::requiredIf($this->requiresOrganisation()), 'nullable', 'exists:departments,id'],
        ];
    }

    /**
     * Requester and auditor accounts must belong to a branch and department: tickets they open
     * inherit their organisation context. Agent and super_admin accounts need neither.
     */
    private function requiresOrganisation(): bool
    {
        return in_array($this->input('role'), [UserRole::Requester->value, UserRole::Auditor->value], true);
    }
}
