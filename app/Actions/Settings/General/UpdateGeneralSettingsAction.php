<?php

namespace App\Actions\Settings\General;

use App\Settings\GeneralSettings;

class UpdateGeneralSettingsAction
{
    public function handle(array $data, GeneralSettings $settings): void
    {
        $settings->app_name = $data['app_name'];
        $settings->app_logo = $data['app_logo'];
        $settings->app_icon = $data['app_icon'];
        $settings->app_favicon = $data['app_favicon'];
        $settings->header_style = $data['header_style'];
        $settings->language = $data['language'];
        $settings->save();
    }
}
