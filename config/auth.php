<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users', // web usa el provider 'users' estándar
        ],
        'api' => [
            'driver' => 'passport',
            'provider' => 'users_plain_api', // usa snake_case sin guiones
        ],
    ],

    'providers' => [
        // Provider estándar para web (usa Eloquent)
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // Provider custom para API con correo/clave plaintext
        'users_plain_api' => [
            'driver' => 'plain_api',              // Debe coincidir con Auth::provider('plain_api', ...)
            'model' => env('AUTH_MODEL', App\Models\User::class), // IMPORTANTE: la clave es 'model'
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
