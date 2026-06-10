<?php

namespace App\Http\Requests\Helpdesk;

use App\Enums\GeneralStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
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
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value) {
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
            'queue_id' => [
                'nullable',
                Rule::exists('queues', 'id')->where('status', GeneralStatus::Active->value),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('ticket_categories', 'id')->where('status', GeneralStatus::Active->value),
            ],
        ];
    }
}
