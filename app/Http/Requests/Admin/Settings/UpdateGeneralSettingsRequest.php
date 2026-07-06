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

    /**
     * @return array{site_name: string, site_description?: string|null, site_locale: string}
     */
    public function settingsData(): array
    {
        $validated = $this->validated();

        return [
            'site_name' => (string) $validated['site_name'],
            'site_description' => isset($validated['site_description']) ? (string) $validated['site_description'] : null,
            'site_locale' => (string) $validated['site_locale'],
        ];
    }
}
