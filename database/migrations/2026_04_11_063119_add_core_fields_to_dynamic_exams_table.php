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
        Schema::table('dynamic_exams', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_exams', function (Blueprint $table) {
            $table->dropForeign(['owning_organisation_id']);
            $table->dropColumn([
                'short_name',
                'exam_type',
                'exam_category',
                'conducting_body_type',
                'exam_frequency',
                'conducting_authority_name',
                'logo',
                'cover_image',
                'exam_source_type',
                'owning_organisation_id',
                'about_exam'
            ]);
        });
    }
};
