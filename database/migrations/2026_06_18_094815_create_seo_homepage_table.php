<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_homepage', function (Blueprint $table) {
            $table->id();
            
            // Meta Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->json('secondary_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->nullable();
            
            // Open Graph
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            
            // Twitter
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            
            // Page Content
            $table->string('breadcrumb_title')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_cta_text')->nullable();
            $table->string('hero_cta_link')->nullable();
            $table->string('featured_image')->nullable();
            
            // Schema & Indexing
            $table->string('schema_type')->nullable();
            $table->json('custom_schema_json')->nullable();
            $table->boolean('allow_index')->default(true);
            $table->boolean('allow_snippet')->default(true);
            $table->boolean('allow_image_preview')->default(true);
            $table->boolean('allow_video_preview')->default(true);
            $table->string('sitemap_priority')->nullable();
            $table->string('change_frequency')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_homepage');
    }
};
