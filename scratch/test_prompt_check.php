<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$scraper = app(\App\Services\GeminiOrganisationScraperService::class);
$targetOrg = \App\Models\Organisation::where('name', 'like', '%Vistas%')->first();
$targetCampus = $targetOrg ? \App\Models\Campus::where('organisation_id', $targetOrg->id)->first() : null;

$content = $scraper->buildExtractionPrompt(
    'https://vistas.ac.in/program-offered',
    'University',
    1,
    [],
    $targetOrg,
    '',
    'department',
    $targetCampus,
    null,
    true
);

echo "Prompt Length: " . strlen($content) . "\n";
echo "Does prompt contain 'School of Basic Sciences'? " . (str_contains($content, 'School of Basic Sciences') ? 'YES' : 'NO') . "\n";
echo "Does prompt contain 'School of Pharmaceutical Sciences'? " . (str_contains($content, 'School of Pharmaceutical Sciences') ? 'YES' : 'NO') . "\n";
echo "Does prompt contain 'School of Engineering'? " . (str_contains($content, 'School of Engineering') ? 'YES' : 'NO') . "\n";
