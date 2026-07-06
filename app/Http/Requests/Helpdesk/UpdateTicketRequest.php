<?php

namespace App\Http\Requests\Helpdesk;

use App\Enums\GeneralStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => [
                'required',
                Rule::enum(TicketStatus::class),
                function (string $attribute, mixed $value, Closure $fail): void {
                    $ticket = $this->route('ticket');
                    if (! $ticket instanceof Ticket) {
                        return;
                    }

                    $target = TicketStatus::from($value);
                    if ($target !== $ticket->status && ! $ticket->status->canTransitionTo($target)) {
                        $fail("Cannot change ticket status from {$ticket->status->label()} to {$target->label()}.");
                    }
                },
            ],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && $value !== '') {
                        $user = User::query()->find($value);
                        if (! $user || ! $user->hasRole(UserRole::ItAgent->value)) {
                            $fail('The assigned user must be an IT Agent.');
                        }
                    }
                },
            ],
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where('status', GeneralStatus::Active->value),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where('status', GeneralStatus::Active->value),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value && $this->input('branch_id')) {
                        $exists = DB::table('departments')
                            ->where('id', $value)
                            ->where('branch_id', $this->input('branch_id'))
                            ->exists();
                        if (! $exists) {
                            $fail('The selected department does not belong to the selected branch.');
                        }
                    }
                },
            ],
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
                Rule::exists('assets', 'id')->where('user_id', $this->ticketRequesterId()),
            ],
        ];
    }

    private function ticketRequesterId(): ?string
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket ? $ticket->requester_id : null;
    }
}
