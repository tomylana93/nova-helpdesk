<?php

namespace App\Settings;

use App\Enums\SiteLocale;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name = 'Nova Helpdesk';

    public string $site_description = 'Nova Helpdesk is a modern, single-company internal IT Helpdesk system designed to manage and resolve support tickets (incidents and service requests) across multiple company branches.';

    public string $site_locale = SiteLocale::English->value;

    public static function group(): string
    {
        return 'general';
    }
}
