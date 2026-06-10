<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Enums\GeneralStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQueueRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('queues', 'name')->ignore($this->route('queue')),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(GeneralStatus::class)],
        ];
    }
}
