<?php

return [
    'title' => 'Two-Factor Authentication',
    'sr_only_title' => 'Two-Factor Authentication Settings',
    'header' => [
        'title' => 'Two-Factor Authentication',
        'description' => 'Manage your two-factor authentication settings',
    ],
    'badge' => [
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
    ],
    'button' => [
        'continue_setup' => 'Continue Setup',
        'enable' => 'Enable 2FA',
        'disable' => 'Disable 2FA',
    ],
    'helper_text' => [
        'disabled_description' => 'When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.',
        'enabled_description' => 'With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.',
    ],
    'modal' => [
        'enabled' => [
            'title' => 'Two-Factor Authentication Enabled',
            'description' => 'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.',
            'button_text' => 'Close',
        ],
        'verify' => [
            'title' => 'Verify Authentication Code',
            'description' => 'Enter the 6-digit code from your authenticator app',
            'button_text' => 'Continue',
        ],
        'setup' => [
            'title' => 'Enable Two-Factor Authentication',
            'description' => 'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app',
            'button_text' => 'Continue',
        ],
        'manual_entry_or' => 'or, enter the code manually',
        'button' => [
            'back' => 'Back',
            'confirm' => 'Confirm',
        ],
    ],
    'recovery' => [
        'title' => '2FA Recovery Codes',
        'description' => 'Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.',
        'button' => [
            'view' => 'View',
            'hide' => 'Hide',
            'codes' => 'Recovery Codes',
            'regenerate' => 'Regenerate Codes',
        ],
        'helper_text' => 'Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.',
    ],
];
