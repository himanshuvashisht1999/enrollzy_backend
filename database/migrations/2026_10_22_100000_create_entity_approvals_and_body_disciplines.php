<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Priority 3 of the Ranking & Accreditation review: statutory approvals (AICTE, NMC, BCI, PCI, INC, NCTE, COA, DCI...)
 * are granted per programme per academic year with a sanctioned intake, so they get their own record table.
 * NBA / AMBA / ABET stay in entity_accreditations (they are accreditations, not approvals).
 * Also maps which regulator governs which disciplines, so the admin can see which programmes are missing an approval.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('entity_approvals')) {
            Schema::create('entity_approvals', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 20);                                  // usually course; campus / organisation for institution-level approvals
                $table->string('entity_id', 40);
                $table->foreignId('accreditation_body_id')->constrained('accreditation_bodies');
                $table->string('approval_number', 100)->nullable();
                $table->string('academic_year', 9)->nullable();                     // 2025-26
                $table->unsignedInteger('sanctioned_intake')->nullable();           // seats sanctioned for that year
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->enum('status', ['active', 'expired', 'applied', 'withdrawn'])->default('active');   // "expired" is also computed from valid_to
                $table->string('source_url', 500)->nullable();
                $table->string('evidence_file')->nullable();
                $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('legacy_source', 60)->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['entity_type', 'entity_id']);
                $table->index(['accreditation_body_id', 'academic_year'], 'entity_approvals_body_year');
            });
        }

        if (!Schema::hasTable('accreditation_body_disciplines')) {
            Schema::create('accreditation_body_disciplines', function (Blueprint $table) {
                $table->foreignId('accreditation_body_id')->constrained('accreditation_bodies')->cascadeOnDelete();
                $table->foreignId('discipline_id')->constrained('disciplines')->cascadeOnDelete();
                $table->primary(['accreditation_body_id', 'discipline_id'], 'acc_body_discipline_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_body_disciplines');
        Schema::dropIfExists('entity_approvals');
    }
};
