<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            try {
                DB::statement('ALTER TABLE `projects` MODIFY `category_id` INT(11) NULL DEFAULT NULL');
            } catch (\Exception $e) {
                // Ignore if already nullable
            }
        }
    }

    public function down(): void
    {
    }
};
