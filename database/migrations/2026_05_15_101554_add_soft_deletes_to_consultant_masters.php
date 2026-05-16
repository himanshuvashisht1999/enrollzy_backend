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
        Schema::table('consultant_statuses', function (Blueprint $table) {
            $table->dropColumn('color');
            $table->softDeletes();
        });

        Schema::table('consultant_lead_visibilities', function (Blueprint $table) {
            $table->dropColumn('key');
            $table->softDeletes();
        });

        Schema::table('consultant_types', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('consultant_access_levels', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultant_statuses', function (Blueprint $table) {
            $table->string('color')->nullable();
            $table->dropSoftDeletes();
        });

        Schema::table('consultant_lead_visibilities', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->dropSoftDeletes();
        });

        Schema::table('consultant_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('consultant_access_levels', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
