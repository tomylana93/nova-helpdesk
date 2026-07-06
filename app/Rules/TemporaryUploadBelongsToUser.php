<?php

namespace App\Rules;

use App\Models\TemporaryUpload;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TemporaryUploadBelongsToUser implements ValidationRule
{
    public function __construct(
        private readonly int|string $userId,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail(__('validation.string', ['attribute' => $attribute]));

            return;
        }

        $temporaryUpload = TemporaryUpload::query()
            ->where('id', $value)
            ->where('user_id', $this->userId)
            ->first();

        if ($temporaryUpload === null) {
            $fail(__('validation.image', ['attribute' => $attribute]));

            return;
        }

        if (! is_string($temporaryUpload->mime_type) || ! str_starts_with($temporaryUpload->mime_type, 'image/')) {
            $fail(__('validation.image', ['attribute' => $attribute]));
        }
    }
}
