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
        Schema::table('contact_us_details', function (Blueprint $table) {
            // Hero Section
            $table->string('hero_badge')->nullable();
            $table->text('hero_description')->nullable();
            $table->json('hero_trust_points')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('btn_hero_primary_text')->nullable();
            $table->string('btn_hero_primary_url')->nullable();
            $table->string('btn_hero_secondary_text')->nullable();
            $table->string('btn_hero_secondary_url')->nullable();
            
            // Contact Info
            $table->string('phone_sales')->nullable();
            $table->string('email_sales')->nullable();
            
            // Founder Spotlight
            $table->string('founder_badge')->nullable();
            $table->string('founder_heading')->nullable();
            $table->string('btn_founder_book_text')->nullable();
            $table->string('btn_founder_book_url')->nullable();
            
            // Form Section
            $table->json('form_trust_points')->nullable();
            
            // Why Contact Us
            $table->string('why_contact_heading')->nullable();
            $table->json('why_contact_cards')->nullable();
            
            // Consultation CTA
            $table->string('cta_heading')->nullable();
            $table->string('btn_cta_secondary_text')->nullable();
            $table->string('btn_cta_secondary_url')->nullable();
        });
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
