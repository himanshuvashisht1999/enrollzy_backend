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
        Schema::table('university_courses', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('university_courses', function (Blueprint $table) {
            if (!Schema::hasColumn('university_courses', 'name')) $table->string('name')->after('university_id');
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
