<?php

namespace App\Http\Requests\Settings\General;

use App\Concerns\ImageValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreGeneralSettingImageRequest extends FormRequest
{
    use ImageValidationRules;

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
            'file' => $this->imageRules(),
        ];
    }
}
