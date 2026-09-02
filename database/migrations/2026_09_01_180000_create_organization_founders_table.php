<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('organization_founders')) {
            Schema::create('organization_founders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seo_organization_setting_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('name');
                $table->string('job_title')->nullable();
                $table->string('image')->nullable();
                $table->string('profile_url')->nullable();
                $table->string('linkedin_url')->nullable();
                $table->json('same_as')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_founders');
    }
};