<?php

use App\Enums\SiteLocale;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('site_name', 'Nova Core');
            $blueprint->add('site_description', 'A All-in-one logistics management system');
            $blueprint->add('site_locale', SiteLocale::English->value);

        });
    }
};
