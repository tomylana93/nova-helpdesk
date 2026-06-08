<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum SiteFont: string
{
    use HasOptions;

    case Inter = 'inter';
    case Sora_Inter = 'sora-inter';
    case Plus_Jakarta_Dm_Sans = 'plus-jakarta-dm-sans';
    case Space_Grotesk_Inter = 'space-grotesk-inter';
    case Nunito_Plus_Jakarta = 'nunito-plus-jakarta';

    /**
     * Get the human-readable label for the font.
     */
    public function label(): string
    {
        return match ($this) {
            self::Inter => __('admin.settings.font.inter'),
            self::Sora_Inter => __('admin.settings.font.sora_inter'),
            self::Plus_Jakarta_Dm_Sans => __('admin.settings.font.plus_jakarta_dm_sans'),
            self::Space_Grotesk_Inter => __('admin.settings.font.space_grotesk_inter'),
            self::Nunito_Plus_Jakarta => __('admin.settings.font.nunito_plus_jakarta'),
        };
    }

    /**
     * Get the human-readable heading for the font.
     */
    public function heading(): string
    {
        return match ($this) {
            self::Inter => 'Inter',
            self::Sora_Inter => 'Sora',
            self::Plus_Jakarta_Dm_Sans => 'Plus Jakarta Sans',
            self::Space_Grotesk_Inter => 'Space Grotesk',
            self::Nunito_Plus_Jakarta => 'Nunito',
        };
    }

    /**
     * Get the human-readable body text for the font.
     */
    public function body(): string
    {
        return match ($this) {
            self::Inter => 'Inter',
            self::Sora_Inter => 'Inter',
            self::Plus_Jakarta_Dm_Sans => 'DM Sans',
            self::Space_Grotesk_Inter => 'Inter',
            self::Nunito_Plus_Jakarta => 'Plus Jakarta Sans',
        };
    }

    /**
     * Google Fonts CDN URL via fonts.bunny.net (privacy-friendly mirror).
     */
    public function googleFontsUrl(): string
    {
        return match ($this) {
            self::Inter => 'https://fonts.bunny.net/css?family=inter:400,500,600,700',
            self::Sora_Inter => 'https://fonts.bunny.net/css?family=sora:400,600,700|inter:400,500',
            self::Plus_Jakarta_Dm_Sans => 'https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|dm-sans:400,500',
            self::Space_Grotesk_Inter => 'https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|inter:400,500',
            self::Nunito_Plus_Jakarta => 'https://fonts.bunny.net/css?family=nunito:400,600,700|plus-jakarta-sans:400,500',
        };
    }
}
