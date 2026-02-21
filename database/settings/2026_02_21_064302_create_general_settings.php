<?php

use App\Enums\HeaderStyleEnum;
use App\Enums\LanguageEnum;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.app_name', 'NOVA Helpdesk');
        $this->migrator->add('general.app_logo', null);
        $this->migrator->add('general.app_icon', null);
        $this->migrator->add('general.app_favicon', null);
        $this->migrator->add('general.header_style', HeaderStyleEnum::ICON);
        $this->migrator->add('general.language', LanguageEnum::EN);
    }
};
