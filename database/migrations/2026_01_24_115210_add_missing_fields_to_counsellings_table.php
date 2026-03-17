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
        Schema::table('counsellings', function (Blueprint $table) {
            $table->year('counselling_year')->nullable()->after('exit_and_refund_rules');
            $table->date('registration_start_date')->nullable()->after('counselling_year');
            $table->date('registration_end_date')->nullable()->after('registration_start_date');
            $table->date('choice_filling_start_date')->nullable()->after('registration_end_date');
            $table->date('choice_filling_end_date')->nullable()->after('choice_filling_start_date');
            $table->date('seat_allotment_result_date')->nullable()->after('choice_filling_end_date');
            $table->date('reporting_start_date')->nullable()->after('seat_allotment_result_date');
            $table->date('reporting_end_date')->nullable()->after('reporting_start_date');
            $table->json('round_wise_schedule')->nullable()->after('reporting_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counsellings', function (Blueprint $table) {
            $table->dropColumn([
                'counselling_year',
                'registration_start_date',
                'registration_end_date',
                'choice_filling_start_date',
                'choice_filling_end_date',
                'seat_allotment_result_date',
                'reporting_start_date',
                'reporting_end_date',
                'round_wise_schedule',
            ]);
        });
    }
};
