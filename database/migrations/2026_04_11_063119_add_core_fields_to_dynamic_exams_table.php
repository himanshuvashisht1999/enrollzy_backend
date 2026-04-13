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
        Schema::table('dynamic_exams', function (Blueprint $table) {
            $table->string('short_name')->nullable()->after('name');
            $table->string('exam_type')->nullable()->after('short_name');
            $table->json('exam_category')->nullable()->after('exam_type');
            $table->string('conducting_body_type')->nullable()->after('exam_category');
            $table->string('exam_frequency')->nullable()->after('conducting_body_type');
            $table->string('conducting_authority_name')->nullable()->after('exam_frequency');
            $table->string('logo')->nullable()->after('conducting_authority_name');
            $table->string('cover_image')->nullable()->after('logo');
            
            $table->string('exam_source_type')->default('External')->after('cover_image');
            $table->foreignId('owning_organisation_id')->nullable()->after('exam_source_type')->constrained('organisations')->nullOnDelete();
            
            $table->longText('about_exam')->nullable()->after('owning_organisation_id');
        });
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
