<?php

use App\Enums\SiteLocale;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('site_name', 'Nova Helpdesk');
            $blueprint->add('site_description', 'Nova Helpdesk is a modern, single-company internal IT Helpdesk system designed to manage and resolve support tickets (incidents and service requests) across multiple company branches.');
            $blueprint->add('site_locale', SiteLocale::English->value);

        });
    }
};
