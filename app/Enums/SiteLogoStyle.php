<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum SiteLogoStyle: string
{
    use HasOptions;

    case Icon = 'icon';
    case Logo = 'logo';

    /**
     * Get the human-readable label for the logo style.
     */
    public function label(): string
    {
        return match ($this) {
            self::Icon => __('admin.settings.logo_style.icon'),
            self::Logo => __('admin.settings.logo_style.logo'),
        };
    }
}
