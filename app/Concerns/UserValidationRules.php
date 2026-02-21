<?php

namespace App\Concerns;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait UserValidationRules
{
    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function userNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function userEmailRules(?int $ignoreUserId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $ignoreUserId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($ignoreUserId),
        ];
    }

    /**
     * Get the validation rules used to validate user statuses.
     *
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function userStatusRules(): array
    {
        return [
            'required',
            Rule::enum(UserStatusEnum::class),
        ];
    }

    /**
     * Get the validation rules used to validate create user passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function createUserPasswordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }

    /**
     * Get the validation rules used to validate create user requests.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function storeUserRules(): array
    {
        return [
            'name' => $this->userNameRules(),
            'email' => $this->userEmailRules(),
            'password' => $this->createUserPasswordRules(),
        ];
    }

    /**
     * Get the validation rules used to validate update user requests.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function updateUserRules(int $userId): array
    {
        return [
            'name' => $this->userNameRules(),
            'email' => $this->userEmailRules($userId),
            'status' => $this->userStatusRules(),
        ];
    }
}
