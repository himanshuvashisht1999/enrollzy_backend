<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Calling command center (role-wise dashboards, Live Monitor, Team Performance, Notice Board):
 * - calling_status.is_connected: a call with this status reached the person (connect rate). Statuses about the
 *   line (not answered, busy, switched off...) stay 0. Editable on the Calling Status master.
 * - settings: the monitor's rules (shift start, late grace, idle / break limits, overdue follow-up limit, the daily
 *   call target used when a telecaller has no monthly target).
 * - notices + notice_acks: announcements to everyone, a team or one person, optionally with acknowledgement.
 */
return new class extends Migration
{
    private const CONNECTED = ['Call Back Requested', 'Next Year', 'Language Barrier', 'Financial Issue', 'Admission Taken', 'Study Abroad',
        'Positive Calls', 'Not Interested', 'Neutral Call', 'Wrong Details'];

    public function up(): void
    {
        if (!Schema::hasColumn('calling_status', 'is_connected')) {
            Schema::table('calling_status', fn (Blueprint $t) => $t->boolean('is_connected')->default(false)->after('calling_action_id'));
            DB::table('calling_status')->whereIn('name', self::CONNECTED)->update(['is_connected' => true]);
        }

        $rules = ['calling_shift_start' => fn (Blueprint $t) => $t->string('calling_shift_start', 5)->default('10:00'),
            'calling_grace_min' => fn (Blueprint $t) => $t->unsignedSmallInteger('calling_grace_min')->default(15),
            'calling_idle_min' => fn (Blueprint $t) => $t->unsignedSmallInteger('calling_idle_min')->default(30),
            'calling_break_min' => fn (Blueprint $t) => $t->unsignedSmallInteger('calling_break_min')->default(45),
            'calling_overdue_max' => fn (Blueprint $t) => $t->unsignedSmallInteger('calling_overdue_max')->default(15),
            'calling_daily_target' => fn (Blueprint $t) => $t->unsignedSmallInteger('calling_daily_target')->default(60)];
        foreach ($rules as $col => $add) {
            if (!Schema::hasColumn('settings', $col)) Schema::table('settings', $add);
        }

        if (!Schema::hasTable('notices')) {
            Schema::create('notices', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('posted_by');
                $t->enum('audience', ['all', 'team', 'user'])->default('all');   // team: audience_id = the team lead; user: one staff member
                $t->unsignedBigInteger('audience_id')->nullable();
                $t->string('title', 200);
                $t->text('body');
                $t->enum('priority', ['normal', 'important', 'urgent'])->default('normal');
                $t->boolean('pinned')->default(false);
                $t->boolean('require_ack')->default(false);
                $t->date('expires_on')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->index(['audience', 'audience_id']);
            });
        }
        if (!Schema::hasTable('notice_acks')) {
            Schema::create('notice_acks', function (Blueprint $t) {
                $t->id();
                $t->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
                $t->unsignedBigInteger('staff_id');
                $t->timestamp('acked_at')->useCurrent();
                $t->unique(['notice_id', 'staff_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_acks');
        Schema::dropIfExists('notices');
        Schema::table('settings', fn (Blueprint $t) => $t->dropColumn(['calling_shift_start', 'calling_grace_min', 'calling_idle_min', 'calling_break_min', 'calling_overdue_max', 'calling_daily_target']));
        Schema::table('calling_status', fn (Blueprint $t) => $t->dropColumn('is_connected'));
    }
};
