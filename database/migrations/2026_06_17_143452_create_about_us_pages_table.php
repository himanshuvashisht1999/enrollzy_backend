<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('about_us_pages', function (Blueprint $table) {
            $table->id();
            
            // Hero Section
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image')->nullable();
            
            // Story Section
            $table->string('story_title')->nullable();
            $table->string('story_subtitle')->nullable();
            $table->text('story_description')->nullable();
            $table->string('story_purpose_text')->nullable();
            $table->string('story_image')->nullable();
            
            // Offers & Features Headings
            $table->string('offers_title')->nullable();
            $table->string('offers_subtitle')->nullable();
            $table->string('features_title')->nullable();
            $table->string('features_subtitle')->nullable();
            
            // CTA Section
            $table->string('cta_title')->nullable();
            $table->text('cta_description')->nullable();
            $table->string('cta_button_1_text')->nullable();
            $table->string('cta_button_1_link')->nullable();
            $table->string('cta_button_2_text')->nullable();
            $table->string('cta_button_2_link')->nullable();
            $table->string('cta_image')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('about_us_pages');
    }
};
