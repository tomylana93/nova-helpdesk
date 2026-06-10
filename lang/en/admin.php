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
                'branch' => 'Branch',
                'department' => 'Department',
            ],
            'placeholder' => [
                'name' => 'Enter full name',
                'email' => 'Enter email address',
                'status' => 'Select status',
                'role' => 'Select role',
                'branch' => 'Select branch',
                'department' => 'Select department',
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
        'branch' => [
            'index' => [
                'title' => 'Branches',
                'heading' => 'Branch Management',
                'description' => 'Manage company branches.',
            ],
            'create' => [
                'title' => 'Create Branch',
                'heading' => 'Create New Branch',
                'description' => 'Add a new branch to the system.',
            ],
            'show' => [
                'title' => 'Branch Detail',
                'heading' => 'Branch Detail',
                'description' => 'View branch information.',
            ],
            'edit' => [
                'title' => 'Edit Branch',
                'heading' => 'Edit Branch',
                'description' => 'Update branch information.',
            ],
            'label' => [
                'code' => 'Code',
                'name' => 'Name',
                'status' => 'Status',
            ],
            'placeholder' => [
                'code' => 'Enter branch code (e.g. BR-HO)',
                'name' => 'Enter branch name',
                'status' => 'Select status',
            ],
            'action' => [
                'create' => 'Create Branch',
                'update' => 'Update Branch',
                'back' => 'Back to Branches',
            ],
            'message' => [
                'created' => [
                    'success' => 'Branch created successfully.',
                ],
                'updated' => [
                    'success' => 'Branch updated successfully.',
                ],
            ],
        ],
        'department' => [
            'index' => [
                'title' => 'Departments',
                'heading' => 'Department Management',
                'description' => 'Manage branch departments.',
            ],
            'create' => [
                'title' => 'Create Department',
                'heading' => 'Create New Department',
                'description' => 'Add a new department to the system.',
            ],
            'show' => [
                'title' => 'Department Detail',
                'heading' => 'Department Detail',
                'description' => 'View department information.',
            ],
            'edit' => [
                'title' => 'Edit Department',
                'heading' => 'Edit Department',
                'description' => 'Update department information.',
            ],
            'label' => [
                'branch' => 'Branch',
                'code' => 'Code',
                'name' => 'Name',
                'status' => 'Status',
            ],
            'placeholder' => [
                'branch' => 'Select branch',
                'code' => 'Enter department code (e.g. DEPT-IT)',
                'name' => 'Enter department name',
                'status' => 'Select status',
            ],
            'action' => [
                'create' => 'Create Department',
                'update' => 'Update Department',
                'back' => 'Back to Departments',
            ],
            'message' => [
                'created' => [
                    'success' => 'Department created successfully.',
                ],
                'updated' => [
                    'success' => 'Department updated successfully.',
                ],
            ],
        ],
        'queue' => [
            'index' => [
                'title' => 'Queues',
                'heading' => 'Queue Management',
                'description' => 'Manage ticket queues.',
            ],
            'create' => [
                'title' => 'Create Queue',
                'heading' => 'Create New Queue',
                'description' => 'Add a new ticket queue.',
            ],
            'show' => [
                'title' => 'Queue Detail',
                'heading' => 'Queue Detail',
                'description' => 'View queue details.',
            ],
            'edit' => [
                'title' => 'Edit Queue',
                'heading' => 'Edit Queue',
                'description' => 'Update queue details.',
            ],
            'label' => [
                'name' => 'Name',
                'description' => 'Description',
                'status' => 'Status',
            ],
            'placeholder' => [
                'name' => 'Enter queue name',
                'description' => 'Enter queue description',
                'status' => 'Select status',
            ],
            'action' => [
                'create' => 'Create Queue',
                'update' => 'Update Queue',
                'back' => 'Back to Queues',
            ],
            'message' => [
                'created' => [
                    'success' => 'Queue created successfully.',
                ],
                'updated' => [
                    'success' => 'Queue updated successfully.',
                ],
            ],
        ],
        'ticket_category' => [
            'index' => [
                'title' => 'Ticket Categories',
                'heading' => 'Ticket Category Management',
                'description' => 'Manage ticket categories and subcategories.',
            ],
            'create' => [
                'title' => 'Create Category',
                'heading' => 'Create New Category',
                'description' => 'Add a new ticket category or subcategory.',
            ],
            'show' => [
                'title' => 'Category Detail',
                'heading' => 'Category Detail',
                'description' => 'View category details.',
            ],
            'edit' => [
                'title' => 'Edit Category',
                'heading' => 'Edit Category',
                'description' => 'Update category details.',
            ],
            'label' => [
                'parent' => 'Parent Category',
                'name' => 'Name',
                'description' => 'Description',
                'status' => 'Status',
            ],
            'placeholder' => [
                'parent' => 'Select parent category (optional)',
                'name' => 'Enter category name',
                'description' => 'Enter category description',
                'status' => 'Select status',
            ],
            'action' => [
                'create' => 'Create Category',
                'update' => 'Update Category',
                'back' => 'Back to Categories',
            ],
            'message' => [
                'created' => [
                    'success' => 'Category created successfully.',
                ],
                'updated' => [
                    'success' => 'Category updated successfully.',
                ],
            ],
        ],
    ],
];
