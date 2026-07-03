<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure CORS settings for your application. The CORS
    | middleware will handle preflight requests and set appropriate headers.
    |
    */

    'allowed_origins' => env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://localhost:8000'),

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],

    'exposed_headers' => ['Content-Range', 'X-Content-Range'],

    'max_age' => 86400,

    'supports_credentials' => true,
];
