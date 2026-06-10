<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Enums\GeneralStatus;
use App\Models\TicketCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketCategoryRequest extends FormRequest
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
            'parent_id' => [
                'nullable',
                'uuid',
                'exists:ticket_categories,id',
                function ($attribute, $value, $fail): void {
                    $category = $this->route('ticket_category');
                    $id = $category instanceof TicketCategory ? $category->id : $category;
                    if ($value === $id) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(GeneralStatus::class)],
        ];
    }
}
