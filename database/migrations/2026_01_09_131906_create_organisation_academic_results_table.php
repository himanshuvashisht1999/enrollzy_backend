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
        Schema::create('organisation_academic_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('exam_year');
            $table->string('class'); // 10, 12, etc.
            $table->integer('students_appeared')->nullable();
            $table->decimal('pass_percentage', 5, 2)->nullable();
            $table->decimal('distinction_percentage', 5, 2)->nullable();
            $table->decimal('average_score', 5, 2)->nullable();
            $table->decimal('highest_score', 5, 2)->nullable();
            $table->json('topper_names')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_academic_results');
    }
};
