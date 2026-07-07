<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Concerns\ProfileValidationRules;
use App\Concerns\UserRoleRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use ProfileValidationRules;
    use UserRoleRules;

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
                ...$this->baseRoleRules(),
                ...$this->preventSuperAdminRoleRule(),
            ],
            'branch_id' => [Rule::requiredIf($this->requiresOrganisation()), 'nullable', 'exists:branches,id'],
            'department_id' => [Rule::requiredIf($this->requiresOrganisation()), 'nullable', 'exists:departments,id'],
        ];
    }

    /**
     * @return array{name: string, email: string, phone?: string|null, role: string, branch_id?: string|null, department_id?: string|null}
     */
    public function userData(): array
    {
        $validated = $this->validated();

        return [
            'name' => (string) $validated['name'],
            'email' => (string) $validated['email'],
            'phone' => isset($validated['phone']) ? (string) $validated['phone'] : null,
            'role' => (string) $validated['role'],
            'branch_id' => isset($validated['branch_id']) ? (string) $validated['branch_id'] : null,
            'department_id' => isset($validated['department_id']) ? (string) $validated['department_id'] : null,
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
