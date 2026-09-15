<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scraper = app(\App\Services\GeminiOrganisationScraperService::class);
echo "Extracting Thapar University...\n";

try {
    $data = $scraper->extractFromUrl('https://www.thapar.edu', 'University');
    echo "SUCCESS!\n";
    echo "Organisation Name: " . ($data['organisation']['name'] ?? 'N/A') . "\n";
    echo "NAAC Grade: " . ($data['organisation']['naac_grade'] ?? 'N/A') . "\n";
    echo "NIRF Rank: " . ($data['organisation']['nirf_rank_overall'] ?? 'N/A') . "\n";
    echo "Campuses Count: " . count($data['campuses'] ?? []) . "\n";
    echo "Departments Count: " . count($data['departments'] ?? []) . "\n";
    echo "Courses Count: " . count($data['courses'] ?? []) . "\n";
    echo "\nSample Course 1: " . json_encode($data['courses'][0] ?? [], JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
