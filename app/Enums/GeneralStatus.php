<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum GeneralStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Inactive = 'inactive';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => __('general.status.active'),
            self::Inactive => __('general.status.inactive'),
        };
    }

    /**
     * Get the badge variant.
     */
    public function variant(): string
    {
        return match ($this) {
            self::Active => 'default',
            self::Inactive => 'destructive',
        };
    }
}
