<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$actions = ["browse", "read", "edit", "add", "delete"];
foreach($actions as $action) {
    $perm = Spatie\Permission\Models\Permission::firstOrCreate(["name" => "exam-categories-".$action, "guard_name" => "admin"]);
    $perm->module_title = "Exam Category";
    $perm->save();
}
echo "Permissions added successfully.";

