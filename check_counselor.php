<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\Admin::where('role', 'Counselor')->get(['id', 'name', 'is_admin', 'role']);
echo json_encode($users);
