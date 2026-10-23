<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Schema::table('calling_histories', function ($table) {
        if (!Illuminate\Support\Facades\Schema::hasColumn('calling_histories', 'program_level_id')) {
            $table->unsignedBigInteger('program_level_id')->nullable();
        }
        if (!Illuminate\Support\Facades\Schema::hasColumn('calling_histories', 'session')) {
            $table->string('session')->nullable();
        }
    });
    echo "Columns added successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
