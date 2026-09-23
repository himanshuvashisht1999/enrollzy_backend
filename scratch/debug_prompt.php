<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$scraper = app(\App\Services\GeminiOrganisationScraperService::class);
$targetOrg = \App\Models\Organisation::where('name', 'like', '%Vistas%')->first();
$targetCampus = $targetOrg ? \App\Models\Campus::where('organisation_id', $targetOrg->id)->first() : null;

$prompt = $scraper->buildExtractionPrompt(
    'https://vistas.ac.in/program-offered',
    'University',
    1,
    [],
    $targetOrg,
    '',
    'department',
    $targetCampus,
    null,
    false
);

file_put_contents(__DIR__ . '/debug_prompt.txt', $prompt);
echo "Prompt saved, size: " . strlen($prompt) . " bytes\n";
echo "First 1000 chars:\n" . substr($prompt, 0, 1000) . "\n...\n";
