<?php
require __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PROJECTS COLUMNS ===\n";
echo implode(', ', array_map(fn($c) => $c->Field, DB::select('DESCRIBE projects'))) . "\n\n";

echo "=== MILESTONES COLUMNS ===\n";
echo implode(', ', array_map(fn($c) => $c->Field, DB::select('DESCRIBE milestones'))) . "\n\n";

echo "=== TASKS COLUMNS ===\n";
echo implode(', ', array_map(fn($c) => $c->Field, DB::select('DESCRIBE tasks'))) . "\n\n";
