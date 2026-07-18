<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_homepage_schema_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('schema_type')->nullable(); // FAQPage, Organization, WebSite, SearchAction
            $table->json('json_data')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_homepage_schema_blocks');
    }
};
