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
        Schema::table('hero_sliders', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_sliders', function (Blueprint $table) {
            $table->dropColumn([
                'pill_1_label', 'pill_1_url',
                'pill_2_label', 'pill_2_url',
                'pill_3_label', 'pill_3_url',
                'pill_4_label', 'pill_4_url'
            ]);
        });
    }
};
