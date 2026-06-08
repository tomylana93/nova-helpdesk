<?php

namespace App\Settings;

use App\Enums\SiteLocale;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name = 'Nova Core';

    public string $site_description = 'A All-in-one logistics management system';

    public string $site_locale = SiteLocale::English->value;

    public static function group(): string
    {
        return 'general';
    }
}
