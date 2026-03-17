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
        // Rename Tables
        Schema::rename('universities', 'organisations');
        Schema::rename('university_courses', 'organisation_courses');
        Schema::rename('university_awards', 'organisation_awards');
        Schema::rename('university_sports', 'organisation_sports');
        Schema::rename('university_accreditations', 'organisation_accreditations');

        // Rename Foreign Keys
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->renameColumn('university_id', 'organisation_id');
        });
        Schema::table('organisation_awards', function (Blueprint $table) {
            $table->renameColumn('university_id', 'organisation_id');
        });
        Schema::table('organisation_sports', function (Blueprint $table) {
            $table->renameColumn('university_id', 'organisation_id');
        });
        Schema::table('organisation_accreditations', function (Blueprint $table) {
            $table->renameColumn('university_id', 'organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert Foreign Keys
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'university_id');
        });
        Schema::table('organisation_awards', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'university_id');
        });
        Schema::table('organisation_sports', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'university_id');
        });
        Schema::table('organisation_accreditations', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'university_id');
        });

        // Revert Table Names
        Schema::rename('organisations', 'universities');
        Schema::rename('organisation_courses', 'university_courses');
        Schema::rename('organisation_awards', 'university_awards');
        Schema::rename('organisation_sports', 'university_sports');
        Schema::rename('organisation_accreditations', 'university_accreditations');
    }
};
