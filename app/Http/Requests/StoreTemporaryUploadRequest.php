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
                'mimetypes:image/jpeg,image/png,image/webp,image/x-icon,image/vnd.microsoft.icon,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain,application/zip,application/x-zip-compressed,application/x-rar-compressed,application/rar',
            ],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
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
