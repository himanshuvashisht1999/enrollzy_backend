<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Calling: a telecaller can escalate a lead to their team lead (with a reason) and mark a lead important, from the
 * call form. The team lead sees open escalations and the team's important leads on their dashboard; an escalation
 * closes when the team lead calls the lead or resolves it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lead_escalations')) {
            Schema::create('lead_escalations', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('customer_id');                 // users.id (the student)
                $t->unsignedBigInteger('raised_by');                   // admin.id
                $t->unsignedBigInteger('team_lead_id')->nullable();    // admin.id the escalation goes to (the raiser's manager)
                $t->string('reason', 500);
                $t->enum('status', ['open', 'resolved'])->default('open');
                $t->unsignedBigInteger('resolved_by')->nullable();
                $t->timestamp('resolved_at')->nullable();
                $t->string('resolution', 500)->nullable();
                $t->timestamps();
                $t->index(['team_lead_id', 'status']);
                $t->index(['customer_id', 'status']);
            });
        }
        if (!Schema::hasTable('important_leads')) {
            Schema::create('important_leads', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('customer_id')->unique();
                $t->unsignedBigInteger('marked_by');
                $t->string('note', 255)->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('important_leads');
        Schema::dropIfExists('lead_escalations');
    }
};
