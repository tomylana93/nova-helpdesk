<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'array'],
            'id.*' => ['string', 'exists:users,id'],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string'],
        ];
    }

    /**
     * @return list<string>|null
     */
    public function ids(): ?array
    {
        /** @var list<string>|null $ids */
        $ids = $this->validated('id');

        return $ids;
    }

    /**
     * @return list<string>|null
     */
    public function exportColumns(): ?array
    {
        /** @var list<string>|null $columns */
        $columns = $this->validated('columns');

        return $columns;
    }
}
