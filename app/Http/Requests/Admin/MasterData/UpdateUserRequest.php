<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Concerns\ProfileValidationRules;
use App\Concerns\UserRoleRules;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    use ProfileValidationRules;
    use UserRoleRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        $userId = $user instanceof User ? $user->id : null;

        $roleRules = [...$this->baseRoleRules()];

        $isSuperAdmin = $user instanceof User && $user->hasRole(UserRole::SuperAdmin->value);

        $statusRules = [
            'required',
            'string',
            Rule::enum(UserStatus::class),
        ];

        if ($isSuperAdmin) {
            $roleRules[] = Rule::in([UserRole::SuperAdmin->value]);
            $statusRules[] = Rule::in([UserStatus::Active->value]);
        } else {
            $roleRules = [...$roleRules, ...$this->preventSuperAdminRoleRule()];
        }

        return [
            ...$this->profileRules($userId),
            'status' => $statusRules,
            'role' => $roleRules,
            'branch_id' => [Rule::requiredIf($this->requiresOrganisation()), 'nullable', 'exists:branches,id'],
            'department_id' => [Rule::requiredIf($this->requiresOrganisation()), 'nullable', 'exists:departments,id'],
        ];
    }

    /**
     * @return array{name: string, email: string, status: string, role: string, branch_id?: string|null, department_id?: string|null}
     */
    public function userData(): array
    {
        $validated = $this->validated();

        return [
            'name' => (string) $validated['name'],
            'email' => (string) $validated['email'],
            'status' => (string) $validated['status'],
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
