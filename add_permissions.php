<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$modules = ['calling-dashboard', 'lead-assign'];
$actions = ['browse', 'read', 'edit', 'add', 'delete'];
foreach($modules as $m) {
    foreach($actions as $a) {
        Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => $m . '-' . $a,
            'guard_name' => 'admin'
        ]);
    }
}
echo "Done\n";
