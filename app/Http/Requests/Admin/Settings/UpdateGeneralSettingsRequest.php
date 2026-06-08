<?php

namespace App\Http\Requests\Admin\Settings;

use App\Enums\SiteLocale;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', GeneralSettings::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:1000'],
            'site_locale' => ['required', Rule::enum(SiteLocale::class)],
        ];
    }
}
