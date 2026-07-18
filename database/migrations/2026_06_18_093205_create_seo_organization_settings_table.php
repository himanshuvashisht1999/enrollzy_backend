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
        Schema::create('seo_organization_settings', function (Blueprint $table) {
            $table->id();
            
            // Organization Details
            $table->string('organization_name')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('alternate_name')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->string('website')->nullable();
            
            // Logos & Images
            $table->string('logo')->nullable();
            $table->string('white_logo')->nullable();
            $table->string('dark_logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('apple_touch_icon')->nullable();
            $table->string('og_image')->nullable();
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('support_email')->nullable();
            
            // Additional Organization Info
            $table->date('founding_date')->nullable();
            $table->string('founder_name')->nullable();
            $table->string('organization_type')->nullable(); // Organization, EducationalOrganization, CollegeOrUniversity, LocalBusiness, Corporation, NGO
            
            // Tax & Business IDs
            $table->string('tax_number')->nullable();
            $table->string('gst_number')->nullable();
            
            // Address
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            
            // Misc
            $table->text('opening_hours')->nullable();
            $table->string('price_range')->nullable();
            $table->string('default_currency')->nullable();
            $table->text('google_map_embed')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('copyright_year')->nullable();
            
            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->json('same_as')->nullable();
            
            // Search Action Settings
            $table->string('search_url')->nullable();
            
            // Default Social Sharing
            $table->string('default_og_title')->nullable();
            $table->text('default_og_description')->nullable();
            $table->string('default_og_image')->nullable();
            $table->string('default_twitter_title')->nullable();
            $table->text('default_twitter_description')->nullable();
            $table->string('default_twitter_image')->nullable();
            
            // Analytics
            $table->string('ga4_id')->nullable();
            $table->string('gtm_id')->nullable();
            $table->string('meta_pixel_id')->nullable();
            $table->string('linkedin_insight_tag')->nullable();
            $table->string('clarity_id')->nullable();
            $table->boolean('schema_enabled')->default(true);
            
            // Verification Codes
            $table->string('google_site_verification')->nullable();
            $table->string('bing_site_verification')->nullable();
            $table->string('yandex_verification')->nullable();
            $table->string('pinterest_verification')->nullable();
            $table->string('facebook_domain_verification')->nullable();
            
            // Robots Settings
            $table->string('default_robots')->nullable();
            $table->string('default_sitemap_priority')->nullable();
            $table->string('default_change_frequency')->nullable();
            
            // Schema Features
            $table->boolean('organization_schema')->default(true);
            $table->boolean('search_action_schema')->default(true);
            $table->boolean('website_schema')->default(true);
            $table->boolean('breadcrumb_schema')->default(true);
            $table->boolean('logo_schema')->default(true);
            $table->boolean('social_profile_schema')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_organization_settings');
    }
};
