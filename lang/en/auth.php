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
        'header' => [
            'title' => 'Log in to your account',
            'description' => 'Enter your email and password below to log in',
        ],
        'label' => [
            'email' => 'Email address',
            'password' => 'Password',
        ],
        'placeholder' => [
            'email' => 'email@example.com',
            'password' => 'Password',
        ],
        'button' => [
            'submit' => 'Log in',
        ],
        'link' => [
            'forgot_password' => 'Forgot password?',
            'sign_up' => 'Sign up',
        ],
        'helper_text' => [
            'remember' => 'Remember me',
            'no_account' => "Don't have an account?",
        ],
        'disabled' => 'Your account is disabled. Please contact support.',
    ],

    'register' => [
        'title' => 'Register',
        'header' => [
            'title' => 'Create an account',
            'description' => 'Enter your details below to create your account',
        ],
        'label' => [
            'name' => 'Name',
            'email' => 'Email address',
            'password' => 'Password',
            'password_confirmation' => 'Confirm password',
        ],
        'placeholder' => [
            'name' => 'Full name',
            'email' => 'email@example.com',
            'password' => 'Password',
            'password_confirmation' => 'Confirm password',
        ],
        'button' => [
            'submit' => 'Create account',
        ],
        'link' => [
            'log_in' => 'Log in',
        ],
        'helper_text' => [
            'have_account' => 'Already have an account?',
        ],
    ],

];
