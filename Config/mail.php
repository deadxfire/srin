<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mail Transfer Configuration
    |--------------------------------------------------------------------------
    | Driver options:
    |   - 'smtp' : Send using SMTP server credentials (via PHPMailer)
    |   - 'mail' : Send using native PHP mail() function
    |   - 'log'  : Write emails to a log file (useful for local development/testing)
    */
    'driver' => env('MAIL_DRIVER', 'log'),

    'smtp' => [
        'host'       => env('MAIL_HOST', 'smtp.gmail.com'),
        'port'       => (int) env('MAIL_PORT', 587),
        'username'   => env('MAIL_USERNAME', ''),
        'password'   => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'), // 'tls', 'ssl', or null
        'timeout'    => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Sender & Admin Recipient
    |--------------------------------------------------------------------------
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@mysite.com'),
        'name'    => env('MAIL_FROM_NAME', 'Srin Engine'),
    ],

    // Default recipient where contact form submissions are delivered
    'admin_email' => env('MAIL_ADMIN_ADDRESS', 'admin@mysite.com'),
];
