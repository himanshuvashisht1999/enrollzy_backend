<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Schema::table('permissions', function ($table) {
        if (!Illuminate\Support\Facades\Schema::hasColumn('permissions', 'module_title')) {
            $table->string('module_title')->nullable()->after('name');
        }
    });
    echo "Column module_title added successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
