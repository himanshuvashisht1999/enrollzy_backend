<?php

/*
| CORS for the public, read-only counselling API (api/v1/*).
| The data is already public, so the default allows any origin; set CORS_ALLOWED_ORIGINS
| (comma separated) to the website's origin(s) to lock it down. No cookies/credentials are used.
*/
return [
    'paths' => ['api/v1/*'],
    'allowed_methods' => ['GET', 'POST', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*'))))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Accept', 'X-Requested-With'],
    'exposed_headers' => ['ETag'],
    'max_age' => 3600,
    'supports_credentials' => false,
];
