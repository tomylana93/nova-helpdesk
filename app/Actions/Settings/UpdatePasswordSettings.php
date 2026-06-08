<?php

namespace App\Actions\Settings;

use App\Settings\PasswordSettings;

class UpdatePasswordSettings
{
    /**
     * @param  array{default_user_password: string}  $data
     */
    public function handle(PasswordSettings $passwordSettings, array $data): void
    {
        $passwordSettings->default_user_password = $data['default_user_password'];
        $passwordSettings->save();
    }
}
