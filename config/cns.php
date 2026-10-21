<?php

return [
    // Where the student website lives: used for canonical URLs and links inside alerts.
    'frontend_url' => rtrim(env('FRONTEND_URL', env('APP_URL', 'http://localhost')), '/'),

    // Shared secret the student website sends (X-Site-Key) so its server-side calls are not rate limited. Empty = no bypass.
    'site_key' => env('CNS_SITE_KEY', ''),

    // Public API responses are cached this many seconds (admin edits show up within a minute).
    'cache_ttl' => (int) env('CNS_CACHE_TTL', 60),

    'alerts' => [
        // Events older than this are never announced (stops a backlog flood the first time the scheduler runs).
        'max_age_days' => 3,
        'closing_thresholds_days' => [3, 1],
        'max_deliveries_per_run' => 500,
    ],

    // WhatsApp delivery. Driver: "none" (default, nothing is sent), "log" (writes to the log, for testing), "cloud" (Meta WhatsApp Cloud API).
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'none'),
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v19.0'),
        'language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'en'),
        // Pre-approved template names that must exist in the WhatsApp Business account.
        'templates' => ['otp' => env('WHATSAPP_TEMPLATE_OTP', 'cns_verify_code'), 'alert' => env('WHATSAPP_TEMPLATE_ALERT', 'cns_alert')],
    ],
];
