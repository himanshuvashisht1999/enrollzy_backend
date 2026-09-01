<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filtered_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('filtered_pages', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->after('program_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('filtered_pages', function (Blueprint $table) {
            if (Schema::hasColumn('filtered_pages', 'course_id')) {
                $table->dropColumn('course_id');
            }
        });
    }
};