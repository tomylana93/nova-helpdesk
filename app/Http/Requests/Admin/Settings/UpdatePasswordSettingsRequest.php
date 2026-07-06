<?php

namespace App\Http\Requests\Admin\Settings;

use App\Concerns\PasswordValidationRules;
use App\Settings\PasswordSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordSettingsRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('update', PasswordSettings::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_user_password' => $this->passwordRules(),
        ];
    }

    /**
     * @return array{default_user_password: string}
     */
    public function settingsData(): array
    {
        $validated = $this->validated();

        return [
            'default_user_password' => (string) $validated['default_user_password'],
        ];
    }
}
