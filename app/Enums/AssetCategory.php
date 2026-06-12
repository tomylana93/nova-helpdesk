<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum AssetCategory: string
{
    use HasOptions;

    case Laptop = 'laptop';
    case Monitor = 'monitor';
    case Device = 'device';
    case License = 'license';
    case Other = 'other';

    /**
     * Get the human-readable label for the category.
     */
    public function label(): string
    {
        return match ($this) {
            self::Laptop => __('admin.asset.category.laptop'),
            self::Monitor => __('admin.asset.category.monitor'),
            self::Device => __('admin.asset.category.device'),
            self::License => __('admin.asset.category.license'),
            self::Other => __('admin.asset.category.other'),
        };
    }
}
