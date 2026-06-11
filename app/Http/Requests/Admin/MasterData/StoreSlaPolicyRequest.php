<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Enums\TicketPriority;
use App\Enums\TicketType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSlaPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ticket_type' => ['nullable', Rule::enum(TicketType::class)],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'first_response_target_minutes' => ['required', 'integer', 'min:1'],
            'resolution_target_minutes' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }
}
