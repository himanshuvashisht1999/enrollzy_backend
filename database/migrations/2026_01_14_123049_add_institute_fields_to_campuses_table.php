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
            // Modify campus_type to supports string instead of strict enum to allow Centre/Branch
            // Or just add a new column if modification is complex in SQLite/MySQL strict mode.
            // Using change() usually requires doctrine/dbal. 
            // For simplicity/robustness, we can modify the column to string(50) to allow any type.
            $table->string('campus_type', 50)->change();

            // New Fields for Type 3
            $table->enum('ownership_model', ['Owned', 'Franchise', 'Partner'])->nullable();
            $table->string('franchise_partner_name')->nullable();
            $table->year('franchise_start_year')->nullable();
            $table->boolean('brand_compliance_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->dropColumn(['ownership_model', 'franchise_partner_name', 'franchise_start_year', 'brand_compliance_verified']);
            // Reverting campus_type is tricky if data exists, skipping strict revert for safety.
        });
    }
};
