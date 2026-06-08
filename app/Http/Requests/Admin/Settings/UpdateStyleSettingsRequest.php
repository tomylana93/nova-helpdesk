<?php

namespace App\Http\Requests\Admin\Settings;

use App\Enums\SiteAuthLayout;
use App\Enums\SiteFont;
use App\Enums\SiteLayout;
use App\Enums\SiteLogoStyle;
use App\Enums\SiteTheme;
use App\Models\TemporaryUpload;
use App\Settings\StyleSettings;
use Closure;
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
     * @return array<int, Closure|string|ValidationRule>
     */
    private function temporaryImageRules(): array
    {
        return [
            'nullable',
            'string',
            Rule::exists('temporary_uploads', 'id')->where(
                fn ($query) => $query->where('user_id', $this->user()?->id),
            ),
            function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $temporaryUpload = TemporaryUpload::query()->find($value);

                if ($temporaryUpload === null) {
                    return;
                }

                if (! is_string($temporaryUpload->mime_type) || ! str_starts_with($temporaryUpload->mime_type, 'image/')) {
                    $fail(__('validation.image', ['attribute' => $attribute]));
                }
            },
        ];
    }
}
