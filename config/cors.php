<?php

// Sanitize origins by stripping any trailing slashes '/'
$rawOrigins = env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', ''));
$parsedOrigins = array_filter(array_map(function ($url) {
    return rtrim(trim($url), '/');
}, explode(',', $rawOrigins)));

$defaultOrigins = [
    'https://admin-management-react.vercel.app',
    'http://localhost:5173',
    'http://localhost:3000',
];

$allowedOrigins = array_values(array_unique(array_merge($defaultOrigins, $parsedOrigins)));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#'
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
