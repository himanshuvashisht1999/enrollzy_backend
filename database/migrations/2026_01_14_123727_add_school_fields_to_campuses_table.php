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
            // Location
            $table->string('nearest_landmark')->nullable();
            
            // Physical Infrastructure (Schools)
            $table->boolean('science_labs_available')->default(false);
            $table->boolean('computer_labs_available')->default(false);
            $table->boolean('playground_available')->default(false);
            
            // Transport
            $table->integer('bus_fleet_size')->nullable();
            $table->boolean('gps_enabled_buses')->default(false);
            
            // Safety
            $table->boolean('visitor_management_system')->default(false);
        });
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
