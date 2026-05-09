<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select('DESCRIBE settings');
    echo "Columns in 'settings' table:\n";
    foreach ($columns as $column) {
        echo $column->Field . " (" . $column->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
