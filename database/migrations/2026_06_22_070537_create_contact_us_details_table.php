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
        Schema::create('contact_us_details', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            
            // Call Us
            $table->string('phone_general')->nullable();
            $table->string('phone_toll_free')->nullable();
            $table->string('phone_international')->nullable();
            
            // Visit Us
            $table->text('address_head_office')->nullable();
            $table->text('address_regional_office')->nullable();
            $table->text('address_us_office')->nullable();
            $table->string('office_timings')->nullable();
            
            // Email Us
            $table->string('email_queries')->nullable();
            $table->string('email_support')->nullable();
            
            // Co-founder
            $table->string('co_founder_name')->nullable();
            $table->string('co_founder_title')->nullable();
            $table->text('co_founder_message')->nullable();
            $table->string('co_founder_image')->nullable();
            $table->string('co_founder_email')->nullable();
            $table->string('co_founder_linkedin')->nullable();
            $table->string('co_founder_instagram')->nullable();
            
            // Map
            $table->text('map_embed_url')->nullable();
            
            // Career Coach
            $table->string('career_coach_title')->nullable();
            $table->json('career_coach_points')->nullable();
            $table->string('career_coach_image')->nullable();
            $table->string('btn_book_session_url')->nullable();
            $table->string('btn_talk_advisor_url')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_us_details');
    }
};
