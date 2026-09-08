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
        Schema::table('counsellings', function (Blueprint $table) {});
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
