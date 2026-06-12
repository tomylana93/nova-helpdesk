<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum AssetStatus: string
{
    use HasOptions;

    case InUse = 'in_use';
    case InStorage = 'in_storage';
    case UnderRepair = 'under_repair';
    case Retired = 'retired';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::InUse => __('admin.asset.status.in_use'),
            self::InStorage => __('admin.asset.status.in_storage'),
            self::UnderRepair => __('admin.asset.status.under_repair'),
            self::Retired => __('admin.asset.status.retired'),
        };
    }

    /**
     * Get the badge variant.
     */
    public function variant(): string
    {
        return match ($this) {
            self::InUse => 'default',
            self::InStorage => 'secondary',
            self::UnderRepair => 'warning',
            self::Retired => 'destructive',
        };
    }
}
