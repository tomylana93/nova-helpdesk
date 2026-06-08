<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum SiteAuthLayout: string
{
    use HasOptions;

    case Simple = 'simple';
    case Split = 'split';
    case Card = 'card';

    /**
     * Get the human-readable label for the auth layout.
     */
    public function label(): string
    {
        return match ($this) {
            self::Simple => __('admin.settings.auth_layout.simple'),
            self::Split => __('admin.settings.auth_layout.split'),
            self::Card => __('admin.settings.auth_layout.card'),
        };
    }
}
