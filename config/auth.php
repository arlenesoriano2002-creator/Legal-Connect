<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'client_guard' => [
            'driver' => 'session',
            'provider' => 'clients',
        ],
        'user_guard' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'admin_guard' => [ // ✅ add this
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'clients' => [
            'driver' => 'eloquent',
            'model' => App\Models\Client::class,
        ],

        'admins' => [ // ✅ add this
            'driver' => 'eloquent',
            'model' => App\Models\AdminLogin::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        // you can also add for admins if needed
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
