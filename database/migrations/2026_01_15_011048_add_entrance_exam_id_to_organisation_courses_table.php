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
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->foreignId('entrance_exam_id')->nullable()->after('course_id')->constrained('exams')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->dropForeign(['entrance_exam_id']);
            $table->dropColumn('entrance_exam_id');
        });
    }
};
