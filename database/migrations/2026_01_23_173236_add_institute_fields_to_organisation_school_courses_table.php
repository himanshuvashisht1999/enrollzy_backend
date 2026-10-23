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
        Schema::table('organisation_school_courses', function (Blueprint $table) {});
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
