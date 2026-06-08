<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PasswordSettings extends Settings
{
    public string $default_user_password = 'password';

    public static function group(): string
    {
        return 'password';
    }

    public static function encrypted(): array
    {
        return [
            'default_user_password',
        ];
    }
}
