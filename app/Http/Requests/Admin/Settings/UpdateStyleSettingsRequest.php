<?php

namespace App\Http\Requests\Admin\Settings;

use App\Enums\SiteAuthLayout;
use App\Enums\SiteFont;
use App\Enums\SiteLayout;
use App\Enums\SiteLogoStyle;
use App\Enums\SiteTheme;
use App\Rules\TemporaryUploadBelongsToUser;
use App\Settings\StyleSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStyleSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', StyleSettings::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_logo_style' => ['required', Rule::enum(SiteLogoStyle::class)],
            'site_auth_layout' => ['required', Rule::enum(SiteAuthLayout::class)],
            'site_layout' => ['required', Rule::enum(SiteLayout::class)],
            'site_theme' => ['required', Rule::enum(SiteTheme::class)],
            'site_font' => ['required', Rule::enum(SiteFont::class)],
            'site_icon_upload_id' => $this->temporaryImageRules(),
            'site_icon_alt_upload_id' => $this->temporaryImageRules(),
            'site_logo_upload_id' => $this->temporaryImageRules(),
            'site_logo_alt_upload_id' => $this->temporaryImageRules(),
            'site_favicon_upload_id' => $this->temporaryImageRules(),
            'site_icon_remove' => ['sometimes', 'boolean'],
            'site_icon_alt_remove' => ['sometimes', 'boolean'],
            'site_logo_remove' => ['sometimes', 'boolean'],
            'site_logo_alt_remove' => ['sometimes', 'boolean'],
            'site_favicon_remove' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{
     *     site_logo_style: string,
     *     site_auth_layout: string,
     *     site_layout: string,
     *     site_theme: string,
     *     site_font: string,
     *     site_icon_upload_id?: string|null,
     *     site_icon_alt_upload_id?: string|null,
     *     site_logo_upload_id?: string|null,
     *     site_logo_alt_upload_id?: string|null,
     *     site_favicon_upload_id?: string|null,
     *     site_icon_remove?: bool,
     *     site_icon_alt_remove?: bool,
     *     site_logo_remove?: bool,
     *     site_logo_alt_remove?: bool,
     *     site_favicon_remove?: bool
     * }
     */
    public function settingsData(): array
    {
        $validated = $this->validated();

        return [
            'site_logo_style' => (string) $validated['site_logo_style'],
            'site_auth_layout' => (string) $validated['site_auth_layout'],
            'site_layout' => (string) $validated['site_layout'],
            'site_theme' => (string) $validated['site_theme'],
            'site_font' => (string) $validated['site_font'],
            'site_icon_upload_id' => isset($validated['site_icon_upload_id']) ? (string) $validated['site_icon_upload_id'] : null,
            'site_icon_alt_upload_id' => isset($validated['site_icon_alt_upload_id']) ? (string) $validated['site_icon_alt_upload_id'] : null,
            'site_logo_upload_id' => isset($validated['site_logo_upload_id']) ? (string) $validated['site_logo_upload_id'] : null,
            'site_logo_alt_upload_id' => isset($validated['site_logo_alt_upload_id']) ? (string) $validated['site_logo_alt_upload_id'] : null,
            'site_favicon_upload_id' => isset($validated['site_favicon_upload_id']) ? (string) $validated['site_favicon_upload_id'] : null,
            'site_icon_remove' => (bool) ($validated['site_icon_remove'] ?? false),
            'site_icon_alt_remove' => (bool) ($validated['site_icon_alt_remove'] ?? false),
            'site_logo_remove' => (bool) ($validated['site_logo_remove'] ?? false),
            'site_logo_alt_remove' => (bool) ($validated['site_logo_alt_remove'] ?? false),
            'site_favicon_remove' => (bool) ($validated['site_favicon_remove'] ?? false),
        ];
    }

    /**
     * @return array<int, string|ValidationRule>
     */
    private function temporaryImageRules(): array
    {
        return [
            'nullable',
            'string',
            new TemporaryUploadBelongsToUser($this->user()->id),
        ];
    }
}
