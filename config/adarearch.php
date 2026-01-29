<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AdaReach API Credentials
    |--------------------------------------------------------------------------
    |
    | Your AdaReach API credentials. You can obtain these from your
    | AdaReach account dashboard.
    |
    */

    'username' => env('ADAREARCH_USERNAME'),
    'password' => env('ADAREARCH_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | AdaReach API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the AdaReach API. Usually you don't need to change this
    | unless you're using a different environment.
    |
    */

    'base_url' => env('ADAREARCH_BASE_URL', 'https://api.mobireach.com.bd'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender ID
    |--------------------------------------------------------------------------
    |
    | The default sender ID to use when sending SMS. This can be overridden
    | when creating individual messages.
    |
    */

    'default_sender' => env('ADAREARCH_DEFAULT_SENDER'),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the SMS Dashboard (similar to Telescope/Horizon)
    |
    */

    'dashboard' => [
        'enabled' => env('ADAREARCH_DASHBOARD_ENABLED', true),
        'port' => env('ADAREARCH_DASHBOARD_PORT', 8090),
        'path' => env('ADAREARCH_DASHBOARD_PATH', 'sms-dashboard'),
        'middleware' => ['web'],
        'socket_io_port' => env('SOCKET_IO_SERVER_PORT', 3000),
        
        // Authentication settings
        'auth_enabled' => env('ADAREARCH_AUTH_ENABLED', true),
        'username' => env('ADAREARCH_DASHBOARD_USERNAME', 'admin'),
        'password' => env('ADAREARCH_DASHBOARD_PASSWORD'), // Hashed password
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Redis settings for storing SMS logs and analytics data
    |
    */

    'redis' => [
        'connection' => env('ADAREARCH_REDIS_CONNECTION', 'default'),
        'prefix' => env('ADAREARCH_REDIS_PREFIX', 'adarearch:'),
        'retention_days' => env('ADAREARCH_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure what gets logged to the dashboard
    |
    */

    'logging' => [
        'enabled' => env('ADAREARCH_LOGGING_ENABLED', true),
        'log_successful' => true,
        'log_failed' => true,
        'log_queries' => true,
    ],

];

