<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$key = config('services.gemini.api_key');
echo "Testing Key: " . substr($key, 0, 8) . "...\n";

// 1. Test v1beta models list
$res = \Illuminate\Support\Facades\Http::get('https://generativelanguage.googleapis.com/v1beta/models?key=' . $key);
echo "v1beta models status: " . $res->status() . "\n";
echo "v1beta body: " . $res->body() . "\n\n";

// 2. Test v1 models list
$resV1 = \Illuminate\Support\Facades\Http::get('https://generativelanguage.googleapis.com/v1/models?key=' . $key);
echo "v1 models status: " . $resV1->status() . "\n";
echo "v1 body: " . $resV1->body() . "\n";
