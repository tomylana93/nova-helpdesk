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

    'confirm_password' => [
        'title' => 'Confirm password',
        'header' => [
            'title' => 'Confirm your password',
            'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
        ],
        'label' => [
            'password' => 'Password',
        ],
        'button' => [
            'submit' => 'Confirm Password',
        ],
    ],

    'forgot_password' => [
        'title' => 'Forgot password',
        'header' => [
            'title' => 'Forgot password',
            'description' => 'Enter your email to receive a password reset link',
        ],
        'label' => [
            'email' => 'Email address',
        ],
        'placeholder' => [
            'email' => 'email@example.com',
        ],
        'button' => [
            'submit' => 'Email password reset link',
        ],
        'helper_text' => [
            'return_to' => 'Or, return to',
        ],
        'link' => [
            'log_in' => 'log in',
        ],
    ],

    'reset_password' => [
        'title' => 'Reset password',
        'header' => [
            'title' => 'Reset password',
            'description' => 'Please enter your new password below',
        ],
        'label' => [
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password',
        ],
        'placeholder' => [
            'password' => 'Password',
            'password_confirmation' => 'Confirm password',
        ],
        'button' => [
            'submit' => 'Reset password',
        ],
    ],

    'two_factor_challenge' => [
        'title' => 'Two-Factor Authentication',
        'authentication' => [
            'title' => 'Authentication Code',
            'description' => 'Enter the authentication code provided by your authenticator application.',
            'button_text' => 'login using a recovery code',
        ],
        'recovery' => [
            'title' => 'Recovery Code',
            'description' => 'Please confirm access to your account by entering one of your emergency recovery codes.',
            'button_text' => 'login using an authentication code',
            'placeholder' => 'Enter recovery code',
        ],
        'button' => [
            'continue' => 'Continue',
        ],
        'helper_text' => [
            'or_you_can' => 'or you can',
        ],
    ],

    'verify_email' => [
        'title' => 'Email verification',
        'header' => [
            'title' => 'Verify email',
            'description' => 'Please verify your email address by clicking on the link we just emailed to you.',
        ],
        'helper_text' => [
            'verification_sent' => 'A new verification link has been sent to the email address you provided during registration.',
        ],
        'button' => [
            'resend' => 'Resend verification email',
        ],
        'link' => [
            'logout' => 'Log out',
        ],
    ],

];
