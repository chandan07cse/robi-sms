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

];
