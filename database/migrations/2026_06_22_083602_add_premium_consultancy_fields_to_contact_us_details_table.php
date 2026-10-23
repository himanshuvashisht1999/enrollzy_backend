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
        Schema::table('contact_us_details', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_us_details', function (Blueprint $table) {
            $table->dropColumn([
                'hero_badge', 'hero_description', 'hero_trust_points', 'hero_image',
                'btn_hero_primary_text', 'btn_hero_primary_url', 'btn_hero_secondary_text', 'btn_hero_secondary_url',
                'phone_sales', 'email_sales',
                'founder_badge', 'founder_heading', 'btn_founder_book_text', 'btn_founder_book_url',
                'form_trust_points', 'why_contact_heading', 'why_contact_cards',
                'cta_heading', 'btn_cta_secondary_text', 'btn_cta_secondary_url'
            ]);
        });
    }
};
