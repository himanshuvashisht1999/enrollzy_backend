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
            // Change the column type from YEAR (4 digits) to STRING to support ranges like "2024-2025"
            $table->string('counselling_year')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counsellings', function (Blueprint $table) {
            // Revert back to YEAR if needed (Warning: data might be truncated if it contains ranges)
            $table->year('counselling_year')->nullable()->change();
        });
    }
};
