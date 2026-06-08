<?php

return [
    'profile' => [
        'title' => 'Profile settings',
        'heading' => 'Profile information',
        'description' => 'Update your name, email address, and avatar',
        'label' => [
            'name' => 'Name',
            'email' => 'Email address',
            'avatar' => 'Avatar',
        ],
        'placeholder' => [
            'name' => 'Full name',
            'email' => 'Email address',
        ],
        'helper' => [
            'uploader_idle' => 'Drop an image here or browse',
        ],
        'message' => [
            'upload_invalid_type' => 'The selected file type is not allowed.',
            'upload_too_large' => 'The selected file is too large.',
            'upload_failed' => 'The file could not be uploaded.',
            'upload_remove_failed' => 'The file could not be removed.',
        ],
        'action' => [
            'submit' => 'Save',
        ],
    ],
    'security' => [
        'title' => 'Security settings',
        'heading' => 'Update password',
        'description' => 'Ensure your account is using a long, random password to stay secure',
        'label' => [
            'current_password' => 'Current password',
            'password' => 'New password',
            'password_confirmation' => 'Confirm password',
        ],
        'placeholder' => [
            'current_password' => 'Current password',
            'password' => 'New password',
            'password_confirmation' => 'Confirm password',
        ],
        'action' => [
            'submit' => 'Save password',
        ],
    ],
];
