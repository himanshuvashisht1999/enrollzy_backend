<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$staff = \App\Models\Admin::where('status', 1)->get();
foreach($staff as $s) {
    echo $s->name . ' - Role: ' . $s->role . ' - Manager ID: ' . $s->manager_id . PHP_EOL;
}
