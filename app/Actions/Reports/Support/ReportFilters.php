<?php

namespace App\Actions\Reports\Support;

use App\Actions\Dashboard\Support\DashboardPeriod;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use Illuminate\Http\Request;

class ReportFilters
{
    public function __construct(
        public readonly DashboardPeriod $period,
        public readonly ?string $branchId,
        public readonly ?string $departmentId,
        public readonly ?string $categoryId,
        public readonly ?string $assigneeId,
        public readonly ?string $status,
        public readonly ?string $priority,
        public readonly ?string $type,
        public readonly ?string $event,
        public readonly string $timezone = 'Asia/Jakarta',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $timezone = self::stringOrNull($request->query('timezone')) ?? 'Asia/Jakarta';
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = 'Asia/Jakarta';
        }

        return new self(
            DashboardPeriod::fromRequest(
                is_string($request->query('mode')) ? $request->query('mode') : null,
                self::integerOrNull($request->query('month')),
                self::integerOrNull($request->query('year')),
            ),
            self::stringOrNull($request->query('branch_id')),
            self::stringOrNull($request->query('department_id')),
            self::stringOrNull($request->query('category_id')),
            self::stringOrNull($request->query('assignee_id')),
            self::enumValueOrNull(TicketStatus::class, $request->query('status')),
            self::enumValueOrNull(TicketPriority::class, $request->query('priority')),
            self::enumValueOrNull(TicketType::class, $request->query('type')),
            self::stringOrNull($request->query('event')),
            $timezone,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->period->toArray(),
            'branch_id' => $this->branchId,
            'department_id' => $this->departmentId,
            'category_id' => $this->categoryId,
            'assignee_id' => $this->assigneeId,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'event' => $this->event,
            'timezone' => $this->timezone,
        ];
    }

    private static function integerOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @template TEnum of \BackedEnum
     *
     * @param  class-string<TEnum>  $enum
     */
    private static function enumValueOrNull(string $enum, mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $enumValue = $enum::tryFrom($value)?->value;

        return is_string($enumValue) ? $enumValue : null;
    }
}
