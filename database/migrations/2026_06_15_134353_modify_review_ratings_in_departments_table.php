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
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn([
                'rating_infrastructure',
                'rating_campus_life',
                'rating_academics',
                'rating_placements',
                'rating_value_for_money'
            ]);
            $table->json('college_reviews')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('college_reviews');
            $table->decimal('rating_infrastructure', 3, 1)->nullable();
            $table->decimal('rating_campus_life', 3, 1)->nullable();
            $table->decimal('rating_academics', 3, 1)->nullable();
            $table->decimal('rating_placements', 3, 1)->nullable();
            $table->decimal('rating_value_for_money', 3, 1)->nullable();
        });
    }
};
