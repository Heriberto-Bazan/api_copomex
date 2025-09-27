<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'copomex' => [
        'token' => env('COPOMEX_TOKEN', 'ddaf6671-5e28-4d81-bfbb-5e6bdc8b25ba'),
        'base_url' => env('COPOMEX_BASE_URL', 'https://api.copomex.com/query'),
        'timeout' => (int) env('COPOMEX_TIMEOUT', 30),
        'cache_ttl' => (int) env('COPOMEX_CACHE_TTL', 3600), // 1 hora
        'retry_attempts' => (int) env('COPOMEX_RETRY_ATTEMPTS', 3),
        'retry_delay' => (int) env('COPOMEX_RETRY_DELAY', 2000), // 2 segundos
    ],

];
