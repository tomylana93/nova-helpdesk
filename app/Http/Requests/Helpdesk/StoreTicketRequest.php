<?php

namespace App\Http\Requests\Helpdesk;

use App\Enums\GeneralStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
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
            'type' => ['required', Rule::enum(TicketType::class)],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'category_id' => [
                'required',
                Rule::exists('ticket_categories', 'id')->where('status', GeneralStatus::Active->value),
            ],
            'attachment_upload_ids' => ['nullable', 'array'],
            'attachment_upload_ids.*' => [
                'required',
                'uuid',
                Rule::exists('temporary_uploads', 'id')->where('user_id', $this->user()?->id),
            ],
            'asset_ids' => ['nullable', 'array'],
            'asset_ids.*' => [
                'required',
                'uuid',
                Rule::exists('assets', 'id')->where('user_id', $this->user()?->id),
            ],
        ];
    }
}
