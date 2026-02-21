<?php

namespace App\Http\Requests\Settings\General;

use App\Enums\HeaderStyleEnum;
use App\Enums\LanguageEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateGeneralSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'app_logo' => ['nullable', 'string', 'max:255'],
            'app_icon' => ['nullable', 'string', 'max:255'],
            'app_favicon' => ['nullable', 'string', 'max:255'],
            'header_style' => ['required', new Enum(HeaderStyleEnum::class)],
            'language' => ['required', new Enum(LanguageEnum::class)],
        ];
    }
}
