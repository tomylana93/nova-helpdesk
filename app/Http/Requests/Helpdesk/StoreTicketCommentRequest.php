<?php

namespace App\Http\Requests\Helpdesk;

use App\Enums\UserRole;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketCommentRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:10000'],
            'visibility' => [
                'required',
                Rule::in(['public', 'internal']),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === 'internal') {
                        $user = $this->user();
                        if (! $user || ! $user->hasRole(UserRole::ItAgent->value) && ! $user->hasRole(UserRole::SuperAdmin->value)) {
                            $fail('Only agents can post internal comments.');
                        }
                    }
                },
            ],
        ];
    }
}
