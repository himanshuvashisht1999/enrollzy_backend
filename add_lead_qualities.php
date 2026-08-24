<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("
        CREATE TABLE IF NOT EXISTS lead_qualities (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            organization_id BIGINT UNSIGNED NULL,
            status TINYINT DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL
        )
    ");
    
    DB::statement("ALTER TABLE users ADD lead_quality_id BIGINT UNSIGNED NULL AFTER category_id");
    DB::statement("ALTER TABLE calling_histories ADD lead_quality_id BIGINT UNSIGNED NULL AFTER current_session");
    
    echo "Migration completed successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
