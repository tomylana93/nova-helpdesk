<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum TicketPriority: string
{
    use HasOptions;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Critical => 'Critical',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::Low => 'outline',
            self::Medium => 'secondary',
            self::High => 'warning',
            self::Critical => 'destructive',
        };
    }
}
