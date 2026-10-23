<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$permissions = ['lead-quality-browse','lead-quality-add','lead-quality-edit','lead-quality-delete'];
foreach($permissions as $p) {
    if (!\Spatie\Permission\Models\Permission::where('name', $p)->exists()) {
        \Spatie\Permission\Models\Permission::create(['name' => $p, 'guard_name' => 'admin']);
    }
}
echo "Permissions added\n";
