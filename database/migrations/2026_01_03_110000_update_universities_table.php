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
        Schema::table('universities', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropForeign(['organisation_type_id']);
            $table->dropForeign(['campus_type_id']);
            $table->dropColumn([
                'organisation_type_id',
                'campus_type_id',
                'address',
                'scholarship_available',
                'international_collaboration',
                'review',
                'living_cost',
                'hostel_fees',
                'ncc',
                'nss',
                'global_ranking',
                'alumni_network',
                'mental_health_support',
                'incubation_center',
                'total_students',
                'international_students',
                'male_students',
                'female_students',
                'lgbtq_friendly',
            ]);
        });
    }
};
