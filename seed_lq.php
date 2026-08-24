<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\LeadQuality::insert([
    ['name' => 'Hot', 'organization_id' => 1, 'status' => 1],
    ['name' => 'Warm', 'organization_id' => 1, 'status' => 1],
    ['name' => 'Cold', 'organization_id' => 1, 'status' => 1]
]);
echo "Seeded lead qualities\n";
