<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum SiteLocale: string
{
    use HasOptions;

    case English = 'en';
    case Indonesian = 'id';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::English => __('admin.settings.locale.english'),
            self::Indonesian => __('admin.settings.locale.indonesian'),
        };
    }

    /**
     * Get the flag key for the locale.
     */
    public function flag(): string
    {
        return match ($this) {
            self::English => 'us',
            self::Indonesian => 'id',
        };
    }
}
