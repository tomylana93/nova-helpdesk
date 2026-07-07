<?php

use Illuminate\Support\Arr;

/**
 * Flatten every PHP language file for a locale into dot-notation keys.
 *
 * @return array<string, string>
 */
function flattenLocaleMessages(string $locale): array
{
    $messages = [];

    foreach (glob(lang_path("{$locale}/*.php")) ?: [] as $file) {
        $namespace = pathinfo($file, PATHINFO_FILENAME);
        $contents = require $file;

        foreach (Arr::dot([$namespace => $contents]) as $key => $value) {
            $messages[$key] = $value;
        }
    }

    return $messages;
}

test('en and id language files share the exact same key structure', function (): void {
    $en = array_keys(flattenLocaleMessages('en'));
    $id = array_keys(flattenLocaleMessages('id'));

    sort($en);
    sort($id);

    expect(array_values(array_diff($en, $id)))
        ->toBe([], 'Keys present in en but missing in id: '.implode(', ', array_diff($en, $id)));

    expect(array_values(array_diff($id, $en)))
        ->toBe([], 'Keys present in id but missing in en: '.implode(', ', array_diff($id, $en)));
});

test('extracted UI strings resolve for both locales', function (string $key): void {
    foreach (['en', 'id'] as $locale) {
        expect(trans($key, [], $locale))
            ->not->toBe($key, "Missing [{$locale}] translation for [{$key}].")
            ->not->toBe('');
    }
})->with([
    // Application chrome
    'admin.master_data.title',
    'admin.settings.title',
    // Auth pages
    'auth.login.title',
    'auth.login.label.email',
    'auth.forgot_password.title',
    'auth.force_password.title',
    // Settings and master data
    'admin.master_data.user.label.phone',
    'admin.master_data.user.label.last_login_at',
    'admin.master_data.user.message.deleted.success',
    'admin.master_data.user.message.restored.success',
    // Generic chrome
    'general.status.active',
    'general.status.inactive',
    // Domain pages
    'dashboard.greeting',
    'helpdesk.ticket.index.title',
]);
