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
        Schema::table('exams', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('short_name')->nullable()->change();
            $table->text('syllabus_source')->nullable()->change();
            $table->text('official_website')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('short_name')->nullable()->change();
            $table->string('syllabus_source')->nullable()->change();
            $table->string('official_website')->nullable()->change();
        });
    }
};
