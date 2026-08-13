<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Schema::table('calling_histories', function ($table) {
        if (!Illuminate\Support\Facades\Schema::hasColumn('calling_histories', 'program_level_text')) {
            $table->string('program_level_text')->nullable()->after('program_level_id');
        }
    });
    echo "Column program_level_text added successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
