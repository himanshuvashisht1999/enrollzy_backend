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
        Schema::create('digital_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('title')->nullable(); // Card label or title
            $table->string('name'); // Full name e.g. "Amit Singh"
            $table->string('designation')->nullable(); // e.g. "Founder"
            $table->string('company_name')->nullable(); // e.g. "Enrollzy"
            $table->string('slug')->unique(); // unique URL slug e.g. "amit-singh"
            $table->string('profile_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->json('slogan_tags')->nullable(); // ["PEOPLE", "TECHNOLOGY", "GROWTH"]
            $table->text('bio')->nullable(); // Quote or summary
            
            // Associated Businesses / Sub-brands
            $table->json('companies')->nullable(); // [{"name": "...", "subtitle": "...", "icon": "...", "link": "..."}]
            
            // Core Contact Channels
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('whatsapp_message')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('maps_url')->nullable();
            $table->string('website_url')->nullable();
            
            // Social Media Profiles
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('telegram_url')->nullable();
            $table->string('github_url')->nullable();
            $table->json('custom_links')->nullable();
            
            // Design & QR Customization
            $table->string('theme_color')->default('#0f172a');
            $table->string('accent_color')->default('#c59b27');
            $table->string('theme_style')->default('curved_gold');
            $table->string('qr_code_custom_image')->nullable();
            $table->string('qr_scan_action')->default('card_url'); // 'card_url' or 'vcard'
            
            // Status & Metrics
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('downloads_count')->default(0);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_cards');
    }
};
