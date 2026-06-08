<?php

namespace App\Settings;

use App\Enums\SiteAuthLayout;
use App\Enums\SiteFont;
use App\Enums\SiteLayout;
use App\Enums\SiteLogoStyle;
use App\Enums\SiteTheme;
use Spatie\LaravelSettings\Settings;

class StyleSettings extends Settings
{
    public string $site_logo_style = SiteLogoStyle::Icon->value;

    public string $site_auth_layout = SiteAuthLayout::Simple->value;

    public string $site_layout = SiteLayout::Sidebar->value;

    public string $site_theme = SiteTheme::Zinc->value;

    public string $site_font = SiteFont::Inter->value;

    public string $site_icon_path = '';

    public string $site_icon_alt_path = '';

    public string $site_logo_path = '';

    public string $site_logo_alt_path = '';

    public string $site_favicon_path = '';

    public static function group(): string
    {
        return 'style';
    }
}
