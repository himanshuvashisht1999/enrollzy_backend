<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'name')) $table->string('name')->change();
            if (!Schema::hasColumn('exams', 'short_name')) $table->string('short_name')->nullable()->change();
            if (!Schema::hasColumn('exams', 'syllabus_source')) $table->string('syllabus_source')->nullable()->change();
            if (!Schema::hasColumn('exams', 'official_website')) $table->string('official_website')->nullable()->change();
        });
    }
};
