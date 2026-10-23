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
        Schema::table('about_us_pages', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_us_pages', function (Blueprint $table) {
            $table->dropColumn([
                'hero_tagline',
                'simplify_decisions_image',
                'offers_description',
                'impacts_title',
                'founders_title',
                'team_title',
                'team_subtitle',
                'advisory_title',
                'advisory_subtitle'
            ]);
        });
    }
};
