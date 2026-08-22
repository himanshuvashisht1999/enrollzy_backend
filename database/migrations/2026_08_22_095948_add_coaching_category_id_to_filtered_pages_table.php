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
        Schema::table('filtered_pages', function (Blueprint $table) {
            $table->unsignedBigInteger('coaching_category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filtered_pages', function (Blueprint $table) {
            $table->dropColumn('coaching_category_id');
        });
    }
};
