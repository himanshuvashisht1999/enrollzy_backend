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
        if (!Schema::hasTable('organisation_elearnings')) {
            Schema::create('organisation_elearnings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id')->unique();
                $table->string('tagline')->nullable();
                $table->string('legal_name')->nullable();
                $table->string('parent_organisation_name')->nullable();
                $table->string('organisation_model')->nullable();
                $table->json('platform_types')->nullable();
                $table->json('target_audiences')->nullable();
                $table->json('credential_types_offered')->nullable();
                $table->string('provides_own_programs')->nullable();
                $table->string('program_provider_model')->nullable();
                $table->json('delivery_modes')->nullable();
                $table->json('learning_features')->nullable();
                $table->json('platform_features')->nullable();
                $table->json('career_services')->nullable();
                $table->json('career_stats')->nullable();
                $table->json('learner_stats')->nullable();
                $table->json('geographic_reach')->nullable();
                $table->json('instruction_languages')->nullable();
                $table->string('business_model')->nullable();
                $table->json('pricing_options')->nullable();
                $table->boolean('has_enterprise_offering')->default(false);
                $table->json('corporate_services')->nullable();
                $table->json('third_party_ratings')->nullable();
                $table->json('industry_recognitions')->nullable();
                $table->text('promotional_video_url')->nullable();
                $table->json('contact_details')->nullable();
                $table->json('social_media_links')->nullable();
                $table->json('app_details')->nullable();
                $table->json('community_details')->nullable();
                $table->json('financial_aid_details')->nullable();
                $table->json('enrollment_details')->nullable();
                $table->json('document_urls')->nullable();
                $table->timestamps();

                $table->foreign('organisation_id')
                      ->references('id')
                      ->on('organisations')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_elearnings');
    }
};
