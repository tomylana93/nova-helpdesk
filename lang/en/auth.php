<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'login' => [
        'title' => 'Log in',
        'card' => [
            'heading' => 'Welcome back!',
            'description' => 'Please enter your credentials to log in.',
        ],
        'label' => [
            'email' => 'Email',
            'password' => 'Password',
            'remember' => 'Remember me',
        ],
        'action' => [
            'submit' => 'Log in',
        ],
        'link' => [
            'forgot' => 'Forgot password',
        ],
        'message' => [
            'active' => 'Your account is active.',
            'disable' => 'Your account has been disabled. Please contact support.',
            'suspend' => 'Your account has been suspended. Please contact support.',
            'default_password_warning' => 'You are still using the default password. Please update it from Security settings.',
        ],
    ],

    'forgot_password' => [
        'title' => 'Forgot password',
        'card' => [
            'heading' => 'Reset your password',
            'description' => 'Enter your email address and we will send you a link to reset your password.',
        ],
        'label' => [
            'email' => 'Email',
        ],
        'action' => [
            'submit' => 'Send password reset link',
        ],
        'link' => [
            'login' => 'Back to login',
        ],
    ],

    'force_password' => [
        'title' => 'Change password',
        'label' => [
            'password' => 'New password',
            'password_confirmation' => 'Confirm new password',
        ],
        'action' => [
            'submit' => 'Change password',
        ],
        'message' => [
            'success' => 'Your password has been changed.',
        ],
    ],

];
