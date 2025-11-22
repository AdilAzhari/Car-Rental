<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MACROKIOSK Bulk SMS API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MACROKIOSK BOLD. for SMS API
    | Documentation: https://www.macrokiosk.com
    | Support: +603 2163 2100 (24hours) / techsupport@macrokiosk.com
    |
    */

    'provider' => env('SMS_PROVIDER', 'macrokiosk'),

    'macrokiosk' => [
        // Authentication
        'username' => env('SMS_USERNAME'),
        'password' => env('SMS_PASSWORD'),
        'service_id' => env('SMS_SERVICE_ID'),

        // JWT Authentication (optional - more secure)
        'use_jwt' => env('SMS_USE_JWT', false),
        'api_key' => env('SMS_API_KEY'), // Required for JWT signature

        // API Endpoints
        'base_url' => env('SMS_BASE_URL', 'https://www.etracker.cc/bulksms'),
        'send_endpoint' => '/Send', // Web API endpoint
        'token_endpoint' => '/Authenticate', // JWT token endpoint

        // Default Settings
        'default_sender' => env('SMS_DEFAULT_SENDER', 'CarRental'),
        'default_type' => 0, // 0 = ASCII, 5 = Unicode (auto-detected if not set)

        // Rate Limiting
        'max_tps' => 30, // Transactions per second (as per doc)

        // Retry Settings
        'retry_attempts' => 3,
        'retry_delay' => 2, // seconds

        // Webhook URLs (for receiving MO and DN)
        'mo_webhook_url' => env('APP_URL').'/api/webhooks/sms/receive',
        'dn_webhook_url' => env('APP_URL').'/api/webhooks/sms/delivery',

        // Message Limits
        'ascii_max_length' => 1071,
        'unicode_max_length' => 1000,

        // Logging
        'log_requests' => env('SMS_LOG_REQUESTS', true),
        'log_responses' => env('SMS_LOG_RESPONSES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => env('SMS_QUEUE_ENABLED', true),
        'connection' => env('SMS_QUEUE_CONNECTION', 'database'),
        'queue' => env('SMS_QUEUE_NAME', 'sms'),
    ],
];
