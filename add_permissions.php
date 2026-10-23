<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$modules = ["course-types", "target-leads"];
$actions = ["browse", "read", "edit", "add", "delete"];
foreach($modules as $module) {
    foreach($actions as $action) {
        Spatie\Permission\Models\Permission::firstOrCreate(["name" => $module."-".$action, "guard_name" => "admin"]);
    }
}
echo "Permissions added successfully.";

