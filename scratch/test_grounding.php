<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$key = config('services.gemini.api_key');
$model = 'gemini-2.5-flash';

$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

$payload = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => 'What is the NAAC accreditation grade and NIRF ranking of Thapar Institute of Engineering and Technology Patiala? Give answer in JSON format {"name": "...", "naac_grade": "...", "nirf_rank": "..."}']
            ]
        ]
    ],
    'tools' => [
        ['google_search' => (object)[]]
    ]
];

$res = \Illuminate\Support\Facades\Http::timeout(30)->post($endpoint, $payload);
echo "Status: " . $res->status() . "\n";
echo "Response: " . $res->body() . "\n";
