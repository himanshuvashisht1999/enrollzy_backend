<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$scraper = app(\App\Services\GeminiOrganisationScraperService::class);

$targetOrg = \App\Models\Organisation::where('name', 'like', '%Vistas%')->first();
$targetCampus = $targetOrg ? \App\Models\Campus::where('organisation_id', $targetOrg->id)->first() : null;

echo "=== TESTING WITH SEARCH GOOGLE = FALSE ===\n";
$result = $scraper->extractFromUrl(
    'https://vistas.ac.in/program-offered',
    'University',
    1,
    [],
    $targetOrg,
    null,
    'department',
    $targetCampus,
    null,
    false // Search Google is FALSE
);

echo "Total Extracted Departments (Google Search = false): " . count($result['departments'] ?? []) . "\n";
foreach ($result['departments'] as $idx => $d) {
    echo ($idx + 1) . ". " . ($d['department_name'] ?? 'Unknown') . " [" . ($d['discipline_area'] ?? '') . "]\n";
}
