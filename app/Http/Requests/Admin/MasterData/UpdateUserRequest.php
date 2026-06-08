<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    use ProfileValidationRules;

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

        return [
            ...$this->profileRules($userId),
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'role' => [
                'required',
                'string',
                Rule::enum(UserRole::class),
                Rule::exists(config('permission.table_names.roles'), 'name')
                    ->where('guard_name', 'web'),
            ],
        ];
    }
}
