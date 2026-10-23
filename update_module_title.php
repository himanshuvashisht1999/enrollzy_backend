<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::table('permissions')
    ->where('name', 'like', 'lead-quality-%')
    ->update(['module_title' => 'Lead Quality']);
    
echo "Updated module_title for Lead Quality permissions.\n";
