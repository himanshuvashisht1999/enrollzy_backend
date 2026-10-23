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
        // 1. campus_documents (campuses.id is char(36) UUID)
        if (!Schema::hasTable('campus_documents')) {
            Schema::create('campus_documents', function (Blueprint $table) {
                $table->id();
                $table->char('campus_id', 36);
                $table->string('title');
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('campus_id')
                      ->references('id')
                      ->on('campuses')
                      ->onDelete('cascade');
            });
        }

        // 2. department_documents (departments.id is char(36) UUID)
        if (!Schema::hasTable('department_documents')) {
            Schema::create('department_documents', function (Blueprint $table) {
                $table->id();
                $table->char('department_id', 36);
                $table->string('title');
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('department_id')
                      ->references('id')
                      ->on('departments')
                      ->onDelete('cascade');
            });
        }

        // 3. organisation_course_documents (organisation_courses.id is bigint unsigned)
        if (!Schema::hasTable('organisation_course_documents')) {
            Schema::create('organisation_course_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_course_id');
                $table->string('title');
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('organisation_course_id')
                      ->references('id')
                      ->on('organisation_courses')
                      ->onDelete('cascade');
            });
        }

        // 4. course_documents (courses.id is bigint unsigned)
        if (!Schema::hasTable('course_documents')) {
            Schema::create('course_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
                $table->string('title');
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('course_id')
                      ->references('id')
                      ->on('courses')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_documents');
        Schema::dropIfExists('organisation_course_documents');
        Schema::dropIfExists('department_documents');
        Schema::dropIfExists('campus_documents');
    }
};
