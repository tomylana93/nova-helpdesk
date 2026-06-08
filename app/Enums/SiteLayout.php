<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum SiteLayout: string
{
    use HasOptions;

    case Sidebar = 'sidebar';
    case Header = 'header';

    /**
     * Get the human-readable label for the layout.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sidebar => __('admin.settings.layout.sidebar'),
            self::Header => __('admin.settings.layout.header'),
        };
    }
}
