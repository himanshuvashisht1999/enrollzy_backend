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
        Schema::table('organisation_school_courses', function (Blueprint $table) {
            $table->json('exams_prepared_for')->nullable()->after('about_school');
            $table->json('target_classes')->nullable()->after('exams_prepared_for');
            $table->integer('total_batches')->nullable()->after('target_classes');
            $table->integer('average_batch_size')->nullable()->after('total_batches');
            $table->integer('min_batch_size')->nullable()->after('average_batch_size');
            $table->integer('max_batch_size')->nullable()->after('min_batch_size');
            // 'medium_of_instruction' might already exist or need to be added if not shared
            // Checking previous file view, medium_of_instruction exists in OrganisationSchoolCourse fillable but let's check migration if we can.
            // Assuming it might exist for School, but fine to check or add safely.
            // Actually, let's just add the ones clearly missing from standard school schema that institute needs.

            $table->boolean('integrated_schooling_available')->default(false)->after('max_batch_size');
            $table->boolean('separate_batches_for_droppers')->default(false)->after('integrated_schooling_available');
            $table->boolean('merit_based_batching')->default(false)->after('separate_batches_for_droppers');
            $table->string('student_teacher_ratio')->nullable()->after('merit_based_batching');
            $table->string('delivery_mode')->nullable()->after('student_teacher_ratio'); // Online, Offline, Hybrid
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_school_courses', function (Blueprint $table) {
            $table->dropColumn([
                'exams_prepared_for',
                'target_classes',
                'total_batches',
                'average_batch_size',
                'min_batch_size',
                'max_batch_size',
                'integrated_schooling_available',
                'separate_batches_for_droppers',
                'merit_based_batching',
                'student_teacher_ratio',
                'delivery_mode'
            ]);
        });
    }
};
