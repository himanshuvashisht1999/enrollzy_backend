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
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->json('specialization_ids')->nullable()->after('specialization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_courses', function (Blueprint $table) {
            $table->dropColumn('specialization_ids');
        });
    }
};
