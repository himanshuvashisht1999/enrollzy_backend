<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$admin = \App\Models\Admin::where('name', 'Vinay Jeet Singh')->first();
echo 'is_admin: ' . (isset($admin->is_admin) ? $admin->is_admin : 'not set') . PHP_EOL;
echo 'role: ' . $admin->role . PHP_EOL;
