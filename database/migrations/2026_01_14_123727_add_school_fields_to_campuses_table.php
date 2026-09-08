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
        Schema::table('campuses', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->dropColumn([
                'nearest_landmark',
                'science_labs_available',
                'computer_labs_available',
                'playground_available',
                'bus_fleet_size',
                'gps_enabled_buses',
                'visitor_management_system'
            ]);
        });
    }
};
