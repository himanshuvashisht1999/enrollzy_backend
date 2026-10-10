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
        Schema::table('campuses', function (Blueprint $table) {
            // Identity
            $table->string('udise_code')->nullable()->after('campus_type_new_id');
            $table->string('affiliation_board')->nullable()->after('udise_code');

            // Location
            $table->string('location_type')->nullable()->after('nearest_transport_hub');

            // Staff Details
            $table->string('headmaster_principal')->nullable();
            $table->unsignedInteger('total_appointed_teachers')->nullable();
            $table->unsignedInteger('male_staff')->nullable();
            $table->unsignedInteger('female_staff')->nullable();

            // Student & Class Profile Stats
            $table->unsignedInteger('total_enrolled_students')->nullable();
            $table->unsignedInteger('enrolled_boys')->nullable();
            $table->unsignedInteger('enrolled_girls')->nullable();
            $table->string('pupil_teacher_ratio')->nullable();

            // Admission & RTE
            $table->longText('admission_procedure')->nullable();
            $table->longText('rte_details')->nullable();
            $table->string('admission_guide_url')->nullable();

            // Reviews
            $table->json('reviews')->nullable();

            // Events & Activities
            $table->json('events_and_activities')->nullable();

            // Alumni
            $table->longText('alumni_details')->nullable();
            $table->json('notable_alumni')->nullable();

            // Clubs & Committees
            $table->json('clubs_and_committees')->nullable();

            // FAQs
            $table->json('faqs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->dropColumn([
                'udise_code',
                'affiliation_board',
                'location_type',
                'headmaster_principal',
                'total_appointed_teachers',
                'male_staff',
                'female_staff',
                'total_enrolled_students',
                'enrolled_boys',
                'enrolled_girls',
                'pupil_teacher_ratio',
                'admission_procedure',
                'rte_details',
                'admission_guide_url',
                'reviews',
                'events_and_activities',
                'alumni_details',
                'notable_alumni',
                'clubs_and_committees',
                'faqs',
            ]);
        });
    }
};
