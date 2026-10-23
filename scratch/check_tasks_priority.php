<?php
require __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PROJECTS ===\n";
foreach (DB::select('DESCRIBE projects') as $col) {
    if (in_array($col->Field, ['status', 'health_status', 'priority'])) {
        echo "{$col->Field}: {$col->Type}\n";
    }
}

echo "=== MILESTONES ===\n";
foreach (DB::select('DESCRIBE milestones') as $col) {
    if (in_array($col->Field, ['status', 'priority'])) {
        echo "{$col->Field}: {$col->Type}\n";
    }
}
