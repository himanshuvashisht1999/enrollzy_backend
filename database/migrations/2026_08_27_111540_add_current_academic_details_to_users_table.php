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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('current_university_id')->nullable()->after('lead_quality_id');
            $table->string('current_university_text')->nullable()->after('current_university_id');
            $table->unsignedBigInteger('current_course_id')->nullable()->after('current_university_text');
            $table->string('current_course_text')->nullable()->after('current_course_id');
            $table->string('current_course_type')->nullable()->after('current_course_text');
            $table->string('current_session')->nullable()->after('current_course_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'current_university_id',
                'current_university_text',
                'current_course_id',
                'current_course_text',
                'current_course_type',
                'current_session',
            ]);
        });
    }
};
