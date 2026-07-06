<?php

use App\Enums\SiteLocale;

test('options preserve generic additional field names mapped to enum methods', function (): void {
    expect(SiteLocale::options(['flagIcon' => 'flag']))->toBe([
        [
            'label' => __('admin.settings.locale.english'),
            'value' => 'en',
            'flagIcon' => 'us',
        ],
        [
            'label' => __('admin.settings.locale.indonesian'),
            'value' => 'id',
            'flagIcon' => 'id',
        ],
    ]);
});
