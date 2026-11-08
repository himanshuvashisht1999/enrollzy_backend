<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An approved expert edits their profile on the website (expert dashboard > My profile); every change waits for staff
 * approval (CRM: Expert Management > Profile changes). One row per request: the changed fields with their new and old
 * values; approving writes them to the expert. A new photo waits privately in expert-docs until approved.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expert_profile_changes')) return;
        Schema::create('expert_profile_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expert_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('changes');                                   // field => new value
            $table->json('previous')->nullable();                      // field => value when the request was made
            $table->string('photo')->nullable();                       // a new photo (private) waiting for approval
            $table->string('status', 10)->default('pending')->index(); // pending, approved, rejected, withdrawn
            $table->string('note', 1000)->nullable();                  // staff's reply (why rejected, or what was changed)
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_profile_changes');
    }
};
