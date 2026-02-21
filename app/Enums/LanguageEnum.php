<?php

namespace App\Enums;

enum LanguageEnum: string
{
    case EN = 'en';
    case ID = 'id';

    public function label(): string
    {
        return match ($this) {
            self::EN => __('settings.general.enums.language.en'),
            self::ID => __('settings.general.enums.language.id'),
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases(),
        );
    }
}
