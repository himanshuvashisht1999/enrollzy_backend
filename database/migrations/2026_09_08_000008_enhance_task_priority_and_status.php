<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks')) {
            try {
                DB::statement("ALTER TABLE `tasks` MODIFY `priority` VARCHAR(50) NOT NULL DEFAULT 'medium'");
                DB::statement("ALTER TABLE `tasks` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'not_started'");
            } catch (\Exception $e) {
                // Ignore if already modified
            }
        }
    }

    public function down(): void
    {
    }
};
