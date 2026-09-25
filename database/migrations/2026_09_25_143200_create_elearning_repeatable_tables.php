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
        // 1. organisation_domains
        if (!Schema::hasTable('organisation_domains')) {
            Schema::create('organisation_domains', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->string('domain_name');
                $table->boolean('is_primary')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('organisation_id')
                      ->references('id')
                      ->on('organisations')
                      ->onDelete('cascade');
            });
        }

        // 2. organisation_partners
        if (!Schema::hasTable('organisation_partners')) {
            Schema::create('organisation_partners', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->string('partner_type')->default('University'); // University, Industry, Certification Body, Hiring Partner
                $table->string('partner_name');
                $table->string('partner_logo')->nullable();
                $table->string('partner_website')->nullable();
                $table->string('relationship_type')->nullable(); // Degree Partner, Certificate Partner, Hiring Partner, Content Partner
                $table->boolean('co_branded_credential')->default(false);
                $table->integer('programs_count')->default(0);
                $table->text('description')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('organisation_id')
                      ->references('id')
                      ->on('organisations')
                      ->onDelete('cascade');
            });
        }

        // 3. organisation_instructors
        if (!Schema::hasTable('organisation_instructors')) {
            Schema::create('organisation_instructors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->string('name');
                $table->string('photo_url')->nullable();
                $table->string('designation')->nullable();
                $table->string('current_company_or_institution')->nullable();
                $table->integer('experience_years')->nullable();
                $table->string('linkedin_url')->nullable();
                $table->text('bio')->nullable();
                $table->decimal('rating', 3, 2)->nullable();
                $table->boolean('status')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('organisation_id')
                      ->references('id')
                      ->on('organisations')
                      ->onDelete('cascade');
            });
        }

        // 4. organisation_leaders
        if (!Schema::hasTable('organisation_leaders')) {
            Schema::create('organisation_leaders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->string('name');
                $table->string('designation')->nullable();
                $table->string('photo_url')->nullable();
                $table->text('bio')->nullable();
                $table->string('linkedin_url')->nullable();
                $table->boolean('is_founder')->default(false);
                $table->integer('sort_order')->default(0);
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
        Schema::dropIfExists('organisation_leaders');
        Schema::dropIfExists('organisation_instructors');
        Schema::dropIfExists('organisation_partners');
        Schema::dropIfExists('organisation_domains');
    }
};
