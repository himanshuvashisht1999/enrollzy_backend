<?php
require __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $sql = file_get_contents(__DIR__ . '/../database/work_management_schema.sql');
    DB::unprepared($sql);
    echo "✓ SQL SCRIPT EXECUTED WITH ZERO ERRORS.\n";
} catch (\Exception $e) {
    echo "❌ SQL ERROR: " . $e->getMessage() . "\n";
}
