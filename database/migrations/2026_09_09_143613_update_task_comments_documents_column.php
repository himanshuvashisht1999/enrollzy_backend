<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('task_comments')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE task_comments MODIFY documents TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('task_comments')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE task_comments MODIFY documents VARCHAR(255) NULL');
        }
    }
};
