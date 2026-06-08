<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum SiteTheme: string
{
    use HasOptions;

    case Zinc = 'zinc';
    case Slate = 'slate';
    case Emerald = 'emerald';
    case Rose = 'rose';
    case Indigo = 'indigo';
    case Violet = 'violet';
    case Cyan = 'cyan';
    case Orange = 'orange';
    case Teal = 'teal';
    case Fuchsia = 'fuchsia';

    /**
     * Get the human-readable label for the theme.
     */
    public function label(): string
    {
        return match ($this) {
            self::Zinc => __('admin.settings.theme.zinc'),
            self::Slate => __('admin.settings.theme.slate'),
            self::Emerald => __('admin.settings.theme.emerald'),
            self::Rose => __('admin.settings.theme.rose'),
            self::Indigo => __('admin.settings.theme.indigo'),
            self::Violet => __('admin.settings.theme.violet'),
            self::Cyan => __('admin.settings.theme.cyan'),
            self::Orange => __('admin.settings.theme.orange'),
            self::Teal => __('admin.settings.theme.teal'),
            self::Fuchsia => __('admin.settings.theme.fuchsia'),
        };
    }
}
