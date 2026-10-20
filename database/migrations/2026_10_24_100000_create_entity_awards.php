<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Awards & recognitions as structured records at ANY level (organisation / campus / department / course): title, issuer, year, evidence.
 * Replaces the free-text lists awards_and_recognition / awards_or_recognition (which duplicate each other). Old columns are left untouched.
 * `kind` separates real awards from things the old text lists mixed in (ranking claims, memberships, affiliations...), so they can be sorted out.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('entity_awards')) {
            Schema::create('entity_awards', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 20);
                $table->string('entity_id', 40);
                $table->string('title', 500);
                $table->enum('kind', ['award', 'ranking_mention', 'membership', 'affiliation', 'accreditation_mention', 'other'])->default('award');
                $table->string('issuer', 255)->nullable();                          // who gave it
                $table->unsignedSmallInteger('award_year')->nullable();
                $table->string('category', 100)->nullable();                        // Academic, Sports, Sustainability...
                $table->text('description')->nullable();
                $table->string('source_url', 500)->nullable();
                $table->string('evidence_file')->nullable();
                $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('legacy_source', 80)->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['entity_type', 'entity_id']);
                $table->index(['kind', 'verification_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_awards');
    }
};
