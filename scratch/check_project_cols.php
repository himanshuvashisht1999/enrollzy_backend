<?php
require __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PROJECTS TABLE ===\n";
foreach (DB::select('DESCRIBE projects') as $col) {
    if ($col->Null === 'NO' && $col->Default === null && $col->Extra !== 'auto_increment') {
        echo "REQUIRED: {$col->Field} ({$col->Type})\n";
    }
}

echo "\n=== TASKS TABLE ===\n";
foreach (DB::select('DESCRIBE tasks') as $col) {
    if ($col->Null === 'NO' && $col->Default === null && $col->Extra !== 'auto_increment') {
        echo "REQUIRED: {$col->Field} ({$col->Type})\n";
    }
}

echo "\n=== MILESTONES TABLE ===\n";
foreach (DB::select('DESCRIBE milestones') as $col) {
    if ($col->Null === 'NO' && $col->Default === null && $col->Extra !== 'auto_increment') {
        echo "REQUIRED: {$col->Field} ({$col->Type})\n";
    }
}
