<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::statement("
    INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
    SELECT id, 1 FROM permissions WHERE name LIKE 'lead-quality-%'
");
echo "Permissions assigned to Role 1\n";
