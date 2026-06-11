<?php

namespace App\Http\Requests;

use App\Actions\Dashboard\Support\DashboardPeriod;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * No strict rules: the dashboard must never 400 on a bad period query.
     * DashboardPeriod clamps invalid values to safe defaults instead.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function toPeriod(): DashboardPeriod
    {
        return DashboardPeriod::fromRequest(
            is_string($this->query('mode')) ? $this->query('mode') : null,
            $this->integerOrNull($this->query('month')),
            $this->integerOrNull($this->query('year')),
        );
    }

    private function integerOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
