<?php

namespace App\Http\Requests\Reports;

use App\Actions\Reports\Support\ReportFilters;
use App\Enums\AdminPermission;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(AdminPermission::ViewReports->value) === true;
    }

    /**
     * Report filters are intentionally tolerant; invalid enum/period values are ignored
     * or clamped by ReportFilters/DashboardPeriod instead of turning reports into 422s.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function toFilters(): ReportFilters
    {
        return ReportFilters::fromRequest($this);
    }
}
