<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE calling_histories 
        ADD current_university_id BIGINT UNSIGNED NULL AFTER session,
        ADD current_university_text VARCHAR(255) NULL AFTER current_university_id,
        ADD current_course_id BIGINT UNSIGNED NULL AFTER current_university_text,
        ADD current_course_text VARCHAR(255) NULL AFTER current_course_id,
        ADD current_course_type VARCHAR(255) NULL AFTER current_course_text,
        ADD current_session VARCHAR(255) NULL AFTER current_course_type;
    ");
    echo "Columns added successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
