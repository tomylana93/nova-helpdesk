<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Enums\GeneralStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')->ignore($this->route('branch')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(GeneralStatus::class)],
        ];
    }
}
