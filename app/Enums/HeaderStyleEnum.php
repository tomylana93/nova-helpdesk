<?php

namespace App\Enums;

enum HeaderStyleEnum: string
{
    case ICON = 'icon';
    case LOGO = 'logo';

    public function label(): string
    {
        return match ($this) {
            self::ICON => __('settings.general.enums.header_style.icon'),
            self::LOGO => __('settings.general.enums.header_style.logo'),
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}
