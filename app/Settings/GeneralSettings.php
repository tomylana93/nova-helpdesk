<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $app_name = 'Nova Cargo';

    public ?string $app_logo;

    public ?string $app_icon;

    public ?string $app_favicon;

    public string $header_style = 'icon';

    public string $language = 'en';

    public static function group(): string
    {
        return 'general';
    }
}
