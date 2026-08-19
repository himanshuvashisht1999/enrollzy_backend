<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$role = \Spatie\Permission\Models\Role::where('name', 'superadmin')->first();
if ($role) {
    echo "Superadmin permissions: " . json_encode($role->permissions->pluck('name'));
} else {
    echo "No superadmin role found.";
}
