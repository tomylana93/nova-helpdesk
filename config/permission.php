<?php

return array_replace_recursive(
    require base_path('vendor/spatie/laravel-permission/config/permission.php'),
    [
        'column_names' => [
            'model_morph_key' => 'model_uuid',
        ],
    ],
);
