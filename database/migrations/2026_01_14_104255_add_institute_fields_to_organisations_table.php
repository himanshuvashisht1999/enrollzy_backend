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
        Schema::table('organisations', function (Blueprint $table) {
            // Institute Specific Fields (Type 3)
            
            // Core Identity (distinct from University)
            $table->uuid('institute_id')->nullable();
            $table->text('about_organisation')->nullable(); 

            // Ownership & Legal Structure
            $table->string('registered_entity_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->boolean('gst_registered')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->json('legal_documents_urls')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'institute_id',
                'about_organisation',
                'registered_entity_name',
                'registration_number',
                'gst_registered',
                'gst_number',
                'pan_number',
                'legal_documents_urls',
            ]);
        });
    }
};
