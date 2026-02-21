<?php

namespace App\Concerns;

trait ImageValidationRules
{
    /**
     * Get the validation rules used to validate image uploads.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function imageRules(): array
    {
        return ['required', 'file', 'image', 'max:2048'];
    }
}
