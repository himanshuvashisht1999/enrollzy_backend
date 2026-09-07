<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Imports\CustomerImport;
use Maatwebsite\Excel\Facades\Excel;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('import:students {file : The absolute path to the Excel (.xlsx/.xls) or CSV file} {--org= : The organization ID (default: null)}', function ($file) {
    if (!file_exists($file)) {
        $this->error("Error: File not found at '{$file}'");
        return 1;
    }

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    \Illuminate\Support\Facades\DB::disableQueryLog();

    $orgOption = $this->option('org');
    $orgId = ($orgOption !== null && $orgOption !== '') ? (int)$orgOption : null;
    $this->info("==========================================");
    $this->info("Starting Student Data Import");
    $this->info("File: {$file}");
    $this->info("Organization ID: " . var_export($orgId, true));
    $this->info("==========================================");

    $startTime = microtime(true);

    $import = new CustomerImport($orgId);
    Excel::import($import, $file);

    $duration = round(microtime(true) - $startTime, 2);
    $imported = $import->getImportedCount();
    $skipped = $import->getSkippedCount();

    $this->newLine();
    $this->info("==========================================");
    $this->info("  IMPORT COMPLETED IN {$duration}s");
    $this->info("  Total Records Imported: {$imported}");
    $this->comment("  Duplicates/Invalid Skipped: {$skipped}");
    $this->info("==========================================");

    return 0;
})->purpose('Import students directly from an Excel or CSV file');
