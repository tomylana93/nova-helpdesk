<?php

namespace App\Http\Requests;

use App\Models\TemporaryUpload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreTemporaryUploadRequest extends FormRequest
{
    private const int MaxActiveUploadsPerUser = 20;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:image/jpeg,image/png,image/webp,image/x-icon,image/vnd.microsoft.icon',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $userId = $this->user()?->getAuthIdentifier();

                if ($userId === null) {
                    return;
                }

                $activeUploadCount = TemporaryUpload::query()
                    ->where('user_id', $userId)
                    ->count();

                if ($activeUploadCount >= self::MaxActiveUploadsPerUser) {
                    $validator->errors()->add('file', __('validation.max.file', [
                        'attribute' => __('validation.attributes.file'),
                        'max' => self::MaxActiveUploadsPerUser,
                    ]));
                }
            },
        ];
    }
}
