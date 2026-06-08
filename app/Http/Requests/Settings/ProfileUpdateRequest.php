<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\TemporaryUpload;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            'avatar_upload_id' => $this->temporaryImageRules(),
            'avatar_remove' => ['sometimes', 'boolean'],
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
