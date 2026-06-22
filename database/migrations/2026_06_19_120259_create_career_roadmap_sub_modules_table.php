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
        Schema::create('career_roadmap_sub_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('career_roadmap_stages')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('career_roadmap_sub_modules')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->json('custom_fields')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_roadmap_sub_modules');
    }
};
