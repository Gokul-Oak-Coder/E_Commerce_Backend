<?php

return [
    'default' => env('REVERB_SERVER', 'reverb'),

    'servers' => [
        'reverb' => [
            'host' => env('REVERB_HOST', '0.0.0.0'),       // ✅ was REVERB_SERVER_HOST
            'port' => env('REVERB_PORT', 8080),             // ✅ was REVERB_SERVER_PORT
            'path' => env('REVERB_SERVER_PATH', ''),
            'hostname' => env('REVERB_HOST', 'localhost'),
            'options' => [
                'tls' => [],
            ],
            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'url' => env('REDIS_URL'),
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', '6379'),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                    'timeout' => env('REDIS_TIMEOUT', 60),
                ],
            ],
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
            'telescope_ingest_interval' => env('REVERB_TELESCOPE_INGEST_INTERVAL', 15),
        ],
    ],

    'apps' => [
        'provider' => 'config',
        'apps' => [
            [
                'key' => 'zgbdaxaz7j1w4il0iict',
                'secret' => 'vyggrsxo8zvpzyvd5t9u',
                'app_id' => '960291',
                'options' => [
                    'host' => '127.0.0.1',
                    'port' => 8080,      // ✅ was 443
                    'scheme' => 'http',  // ✅ was https
                    'useTLS' => false, // ✅ false
                ],
                'allowed_origins' => ['*'],
                'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
                'activity_timeout' => env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
                'max_connections' => env('REVERB_APP_MAX_CONNECTIONS'),
                'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10_000),
                'accept_client_events_from' => env('REVERB_APP_ACCEPT_CLIENT_EVENTS_FROM', 'members'),
            ],
        ],
    ],
];