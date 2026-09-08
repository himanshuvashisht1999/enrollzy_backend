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
        Schema::table('departments', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('college_reviews');
            if (!Schema::hasColumn('departments', 'rating_infrastructure')) $table->decimal('rating_infrastructure', 3, 1)->nullable();
            if (!Schema::hasColumn('departments', 'rating_campus_life')) $table->decimal('rating_campus_life', 3, 1)->nullable();
            if (!Schema::hasColumn('departments', 'rating_academics')) $table->decimal('rating_academics', 3, 1)->nullable();
            if (!Schema::hasColumn('departments', 'rating_placements')) $table->decimal('rating_placements', 3, 1)->nullable();
            if (!Schema::hasColumn('departments', 'rating_value_for_money')) $table->decimal('rating_value_for_money', 3, 1)->nullable();
        });
    }
};
