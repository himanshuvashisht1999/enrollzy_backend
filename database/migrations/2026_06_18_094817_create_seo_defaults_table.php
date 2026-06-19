<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_defaults', function (Blueprint $table) {
            $table->id();
            
            $table->string('default_meta_title')->nullable();
            $table->text('default_meta_description')->nullable();
            $table->string('default_og_image')->nullable();
            $table->string('default_twitter_image')->nullable();
            $table->string('default_robots')->nullable();
            $table->string('default_schema_type')->nullable();
            $table->string('default_author')->nullable();
            $table->string('default_publisher')->nullable();
            $table->string('default_language')->nullable();
            $table->string('default_country')->nullable();
            $table->string('separator')->nullable();
            $table->string('title_format')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_defaults');
    }
};
