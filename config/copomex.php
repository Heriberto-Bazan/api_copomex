<?php

return [
    /*
    |--------------------------------------------------------------------------
    | COPOMEX API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con la API de COPOMEX
    | para obtener información de estados y municipios de México.
    |
    */

    'api' => [
        'base_url' => env('COPOMEX_BASE_URL', 'https://api.copomex.com/query'),
        'token' => env('COPOMEX_TOKEN', 'pruebas'),
        'timeout' => (int) env('COPOMEX_TIMEOUT', 30),
        'verify_ssl' => env('COPOMEX_VERIFY_SSL', true),
    ],

    'cache' => [
        'enabled' => env('COPOMEX_CACHE_ENABLED', true),
        'ttl' => [
            'estados' => (int) env('COPOMEX_CACHE_ESTADOS_TTL', 3600), // 1 hora
            'municipios' => (int) env('COPOMEX_CACHE_MUNICIPIOS_TTL', 7200), // 2 horas
        ],
        'prefix' => env('COPOMEX_CACHE_PREFIX', 'copomex_'),
        'store' => env('COPOMEX_CACHE_STORE', null), // null = default
    ],

    'retry' => [
        'attempts' => (int) env('COPOMEX_RETRY_ATTEMPTS', 3),
        'delay' => (int) env('COPOMEX_RETRY_DELAY', 2000), // milliseconds
        'backoff_multiplier' => (float) env('COPOMEX_BACKOFF_MULTIPLIER', 1.5),
    ],

    'rate_limiting' => [
        'enabled' => env('COPOMEX_RATE_LIMIT_ENABLED', true),
        'max_requests_per_minute' => (int) env('COPOMEX_MAX_REQUESTS_PER_MINUTE', 60),
    ],

    'logging' => [
        'enabled' => env('COPOMEX_LOGGING_ENABLED', true),
        'level' => env('COPOMEX_LOG_LEVEL', 'info'), // debug, info, warning, error
        'log_requests' => env('COPOMEX_LOG_REQUESTS', false),
        'log_responses' => env('COPOMEX_LOG_RESPONSES', false),
    ],

    'endpoints' => [
        'estados' => 'get_estados',
        'municipios' => 'get_municipio_por_estado',
        'info' => 'info',
    ],

    'validation' => [
        'validate_estados' => env('COPOMEX_VALIDATE_ESTADOS', true),
        'estados_esperados' => 32, // Total de estados en México
        'min_municipios_por_estado' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de México (para validación)
    |--------------------------------------------------------------------------
    */
    'estados_mexico' => [
        'Aguascalientes',
        'Baja California',
        'Baja California Sur',
        'Campeche',
        'Chiapas',
        'Chihuahua',
        'Ciudad de México',
        'Coahuila',
        'Colima',
        'Durango',
        'Guanajuato',
        'Guerrero',
        'Hidalgo',
        'Jalisco',
        'México',
        'Michoacán',
        'Morelos',
        'Nayarit',
        'Nuevo León',
        'Oaxaca',
        'Puebla',
        'Querétaro',
        'Quintana Roo',
        'San Luis Potosí',
        'Sinaloa',
        'Sonora',
        'Tabasco',
        'Tamaulipas',
        'Tlaxcala',
        'Veracruz',
        'Yucatán',
        'Zacatecas',
    ],
];