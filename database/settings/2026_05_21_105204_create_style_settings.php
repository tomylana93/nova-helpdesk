<?php

use App\Enums\SiteAuthLayout;
use App\Enums\SiteFont;
use App\Enums\SiteLayout;
use App\Enums\SiteLogoStyle;
use App\Enums\SiteTheme;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('style', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('site_logo_style', SiteLogoStyle::Icon->value);
            $blueprint->add('site_auth_layout', SiteAuthLayout::Simple->value);
            $blueprint->add('site_layout', SiteLayout::Sidebar->value);
            $blueprint->add('site_theme', SiteTheme::Zinc->value);
            $blueprint->add('site_font', SiteFont::Inter->value);
            $blueprint->add('site_icon_path', '');
            $blueprint->add('site_icon_alt_path', '');
            $blueprint->add('site_logo_path', '');
            $blueprint->add('site_logo_alt_path', '');
            $blueprint->add('site_favicon_path', '');
        });
    }
};
