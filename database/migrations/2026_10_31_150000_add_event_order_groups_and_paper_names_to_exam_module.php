<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Exam module:
 * - events keep the order the admin sets (exm_cycle_events.sort_order), on the cycle page and on the website. Existing
 *   events are numbered in each cycle by event type order (registration ... result), then date, then when added.
 * - each event type says which group of the website's "Important dates" it belongs to (exm_event_types.date_group),
 *   so a type added in the CRM shows under the right heading instead of "Other dates".
 * - a pattern row is one paper (exm_patterns.paper_name: "Paper 1 (B.E./B.Tech)", "Paper 2A (B.Arch)"), each with its
 *   own mode, duration and marking; a section can set its own mode in the sections JSON (no column).
 */
return new class extends Migration
{
    /** The website's groups until now, by type slug. */
    private const GROUPS = [
        'application' => ['registration', 'late-fee-window', 'correction-window', 'fee-deposit-payment'],
        'exam' => ['city-intimation-slip', 'admit-card', 'exam-date'],
        'result' => ['provisional-answer-key', 'answer-key-objection', 'final-answer-key', 'result', 'scorecard', 'merit-list'],
    ];

    public function up(): void
    {
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_cycle_events', 'sort_order')) $table->unsignedInteger('sort_order')->default(0)->after('label');
        });
        Schema::table('exm_event_types', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_event_types', 'date_group')) $table->string('date_group', 20)->nullable()->after('applies_to');
        });
        Schema::table('exm_patterns', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_patterns', 'paper_name')) $table->string('paper_name', 150)->nullable()->after('stage_id');
        });

        foreach (self::GROUPS as $group => $slugs) {
            DB::table('exm_event_types')->whereNull('date_group')->whereIn('slug', $slugs)->update(['date_group' => $group]);
        }
        DB::table('exm_event_types')->whereNull('date_group')->where('applies_to', 'counselling')->update(['date_group' => 'counselling']);
        DB::table('exm_event_types')->whereNull('date_group')->update(['date_group' => 'other']);

        // number each exam cycle's events 1, 2, 3 ... (counselling events keep their own order)
        $events = DB::table('exm_cycle_events as e')->leftJoin('exm_event_types as t', 't.id', '=', 'e.event_type_id')
            ->whereNotNull('e.cycle_id')
            ->orderBy('e.cycle_id')->orderBy('t.sort_order')->orderByRaw('e.start_at is null')->orderBy('e.start_at')->orderBy('e.id')
            ->get(['e.id', 'e.cycle_id']);
        $n = [];
        foreach ($events as $e) {
            $n[$e->cycle_id] = ($n[$e->cycle_id] ?? 0) + 1;
            DB::table('exm_cycle_events')->where('id', $e->id)->update(['sort_order' => $n[$e->cycle_id]]);
        }
    }

    public function down(): void
    {
        Schema::table('exm_patterns', fn (Blueprint $table) => $table->dropColumn('paper_name'));
        Schema::table('exm_event_types', fn (Blueprint $table) => $table->dropColumn('date_group'));
        Schema::table('exm_cycle_events', fn (Blueprint $table) => $table->dropColumn('sort_order'));
    }
};
