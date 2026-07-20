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
        Schema::table('about_us_pages', function (Blueprint $table) {
            $table->text('hero_tagline')->nullable()->after('hero_description');
            $table->string('simplify_decisions_image')->nullable()->after('hero_image');
            $table->text('offers_description')->nullable()->after('offers_subtitle');
            $table->string('impacts_title')->nullable()->after('offers_description');
            $table->string('founders_title')->nullable()->after('founders_common_message');
            $table->string('team_title')->nullable()->after('founders_title');
            $table->string('team_subtitle')->nullable()->after('team_title');
            $table->string('advisory_title')->nullable()->after('team_subtitle');
            $table->string('advisory_subtitle')->nullable()->after('advisory_title');
        });
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
