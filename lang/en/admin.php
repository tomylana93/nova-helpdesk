<?php

return [
    'settings' => [
        'title' => 'Settings',
        'heading' => 'Settings',
        'description' => 'Manage your settings here.',
        'action' => [
            'open' => 'Open settings',
        ],
        'general' => [
            'title' => 'General Settings',
            'heading' => 'General Settings',
            'description' => 'Configure site name, description, and locale.',
            'action' => [
                'submit' => 'Save changes',
            ],
            'status' => [
                'saved' => 'Saved.',
            ],
            'label' => [
                'site_name' => 'Site Name',
                'site_description' => 'Site Description',
                'locale' => 'Locale',
            ],
            'placeholder' => [
                'site_name' => 'Enter site name',
                'site_description' => 'Enter site description',
                'locale' => 'Select site locale',
            ],
        ],
        'style' => [
            'title' => 'Style Settings',
            'heading' => 'Style Settings',
            'description' => 'Customize the appearance of admin panel.',
            'branding' => [
                'heading' => 'Branding Assets',
                'description' => 'Upload custom icon, logo, and favicon assets for the application.',
            ],
            'label' => [
                'icon' => 'Icon',
                'icon_alt' => 'Icon Alt',
                'logo' => 'Logo',
                'logo_alt' => 'Logo Alt',
                'favicon' => 'Favicon',
                'logo_style' => 'Logo Style',
                'auth_layout' => 'Authentication Layout',
                'layout' => 'Admin Panel Layout',
                'theme' => 'Color Theme',
                'font' => 'Font Family',
            ],
            'action' => [
                'submit' => 'Save changes',
            ],
            'status' => [
                'saved' => 'Saved.',
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
            'placeholder' => [
                'theme' => 'Select color theme',
                'font' => 'Select font family',
            ],
            'option' => [
                'logo_style' => [
                    'icon' => 'Display the icon with the configured site name.',
                    'logo' => 'Display the full logo asset.',
                ],
                'auth_layout' => [
                    'simple' => 'Minimal auth form without a feature panel.',
                    'split' => 'Form with a branded supporting panel.',
                    'card' => 'Centered card layout for compact auth screens.',
                ],
                'layout' => [
                    'sidebar' => 'Persistent sidebar navigation for admin pages.',
                    'header' => 'Top navigation layout with more horizontal space.',
                ],
            ],
        ],
        'password' => [
            'title' => 'Password Settings',
            'heading' => 'Password Settings',
            'description' => 'Manage the default password used for newly created users.',
            'action' => [
                'submit' => 'Save changes',
            ],
            'status' => [
                'saved' => 'Saved.',
            ],
            'label' => [
                'default_user_password' => 'Default User Password',
                'default_user_password_confirmation' => 'Confirm Default User Password',
            ],
            'placeholder' => [
                'default_user_password' => 'Enter default user password',
                'default_user_password_confirmation' => 'Re-enter default user password',
            ],
        ],
        'locale' => [
            'english' => 'English',
            'indonesian' => 'Indonesian',
        ],
        'logo_style' => [
            'icon' => 'Icon',
            'logo' => 'Logo',
        ],
        'auth_layout' => [
            'simple' => 'Simple',
            'split' => 'Split',
            'card' => 'Card',
        ],
        'layout' => [
            'sidebar' => 'Sidebar',
            'header' => 'Header',
        ],
        'theme' => [
            'zinc' => 'Zinc',
            'slate' => 'Slate',
            'rose' => 'Rose',
            'emerald' => 'Emerald',
            'indigo' => 'Indigo',
            'violet' => 'Violet',
            'cyan' => 'Cyan',
            'orange' => 'Orange',
            'teal' => 'Teal',
            'fuchsia' => 'Fuchsia',
        ],
        'font' => [
            'inter' => 'Inter — Clean & Versatile',
            'sora_inter' => 'Sora + Inter — Modern Tech',
            'plus_jakarta_dm_sans' => 'Plus Jakarta Sans + DM Sans — Professional',
            'space_grotesk_inter' => 'Space Grotesk + Inter — Bold & Geometric',
            'nunito_plus_jakarta' => 'Nunito + Plus Jakarta Sans — Friendly',
        ],
    ],
    'master_data' => [
        'title' => 'Master Data',
        'heading' => 'Master Data',
        'description' => 'Manage your master data here.',
        'action' => [
            'open' => 'Open data',
        ],

        'user' => [
            'index' => [
                'title' => 'Users',
                'heading' => 'User Management',
                'description' => 'Manage application users, roles, and permissions.',
            ],
            'create' => [
                'title' => 'Create User',
                'heading' => 'Create New User',
                'description' => 'Add a new user to the system.',

            ],
            'show' => [
                'title' => 'User Detail',
                'heading' => 'User Detail',
                'description' => 'View user information.',
            ],
            'edit' => [
                'title' => 'Edit User',
                'heading' => 'Edit User',
                'description' => 'Update user information.',
            ],
            'label' => [
                'name' => 'Name',
                'email' => 'Email',
                'status' => 'Status',
                'role' => 'Role',
            ],
            'placeholder' => [
                'name' => 'Enter full name',
                'email' => 'Enter email address',
                'status' => 'Select status',
                'role' => 'Select role',
            ],
            'action' => [
                'create' => 'Create User',
                'update' => 'Update User',
                'reset' => 'Reset',
                'back' => 'Back to Users',
                'view' => 'View',
            ],
            'message' => [
                'created' => [
                    'success' => 'User created successfully.',
                    'error' => 'Failed to create user. Please try again.',
                ],
                'updated' => [
                    'success' => 'User updated successfully.',
                    'error' => 'Failed to update user. Please try again.',
                ],
            ],
        ],
    ],
];
