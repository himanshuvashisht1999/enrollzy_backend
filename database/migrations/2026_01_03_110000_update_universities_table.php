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
        Schema::table('universities', function (Blueprint $table) {
            $table->foreignId('organisation_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_type_id')->nullable()->constrained()->nullOnDelete();
            
            $table->text('address')->nullable();
            $table->boolean('scholarship_available')->default(false);
            $table->longText('international_collaboration')->nullable();
            $table->longText('review')->nullable();
            $table->longText('living_cost')->nullable();
            $table->longText('hostel_fees')->nullable();
            $table->boolean('ncc')->default(false);
            $table->boolean('nss')->default(false);
            $table->string('global_ranking')->nullable();
            $table->boolean('alumni_network')->default(false);
            $table->boolean('mental_health_support')->default(false);
            $table->longText('incubation_center')->nullable();
            $table->integer('total_students')->nullable();
            $table->integer('international_students')->nullable();
            $table->integer('male_students')->nullable();
            $table->integer('female_students')->nullable();
            $table->boolean('lgbtq_friendly')->default(false);
        });
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
