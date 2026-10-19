<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Counselling;
use App\Models\Counselling\CounsellingCycle;
use App\Models\ExamModule\AuditLog;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\CycleEvent;
use App\Models\ExamModule\Exam;
use Illuminate\Http\Request;

class AttentionController extends Controller
{
    /** Needs Attention dashboard (spec section 8), with a Counselling filter (counselling spec section 15). */
    public function index(Request $request)
    {
        $scope = in_array($request->scope, ['exam', 'counselling']) ? $request->scope : 'all';

        $live = fn ($q) => $q->whereHas('cycle', fn ($c) => $c->whereNotIn('cycle_status', ['cancelled', 'completed']));

        $events = CycleEvent::with(['type', 'cycle.exam'])->where($live)->get();

        // 1. Result-type events whose expected date has passed but are not declared.
        $overdueResults = $events->filter(fn ($e) => $e->type?->isManual()
            && $e->start_at && $e->start_at->isPast()
            && !in_array($e->manual_status, ['declared', 'cancelled']))->values();

        // 2. Open or upcoming events not verified in the last 7 days.
        $unverified = $events->filter(fn ($e) => !$e->type?->isManual()
            && in_array($e->status, ['open', 'upcoming'])
            && (!$e->last_verified_at || $e->last_verified_at->lt(now()->subDays(7))))->values();

        // 3. Cycles postponed for more than 14 days with no update.
        $stalePostponed = Cycle::with('exam')->where('cycle_status', 'postponed')
            ->where('updated_at', '<', now()->subDays(14))->get();

        // 4. Public exams with no current cycle for the coming year.
        $noCurrent = Exam::where('visibility', 'public')->where('status', true)->where('is_archived', false)
            ->whereDoesntHave('cycles', fn ($q) => $q->where('is_current', true)->where('year', '>=', now()->year))->get();

        return view('admin.exam-module.attention', compact('overdueResults', 'unverified', 'stalePostponed', 'noCurrent', 'scope') + $this->counselling());
    }

    /**
     * Counselling items. Seat-matrix and cutoff checks ("choice filling open but no live seat matrix",
     * "round completed, no cutoffs after 14 days") arrive with the seat matrix and cutoffs in Phase B.
     */
    private function counselling(): array
    {
        $liveCycle = fn ($q) => $q->whereHas('counsellingCycle', fn ($c) => $c->whereNotIn('cycle_status', ['cancelled', 'completed']));
        $events = CycleEvent::with(['type', 'round', 'counsellingCycle.counselling'])->whereNotNull('counselling_cycle_id')->where($liveCycle)->get();

        return [
            // Allotment result (or any manual result) expected date passed, not declared.
            'cOverdueResults' => $events->filter(fn ($e) => $e->type?->isManual() && $e->start_at && $e->start_at->isPast()
                && !in_array($e->manual_status, ['declared', 'cancelled']))->values(),

            // Open or upcoming events not verified in the last 7 days.
            'cUnverified' => $events->filter(fn ($e) => !$e->type?->isManual() && in_array($e->status, ['open', 'upcoming'])
                && (!$e->last_verified_at || $e->last_verified_at->lt(now()->subDays(7))))->values(),

            // Current cycle with date fields cleared by Copy Cycle and not yet re-entered.
            'cNeedsDates' => CounsellingCycle::with('counselling')->where('is_current', true)->get()
                ->map(fn ($c) => ['cycle' => $c, 'todo' => $c->needsNewDates()])->filter(fn ($x) => $x['todo'])->values(),

            // Choice filling is open but the round has no live seat matrix: students are choosing blind.
            'cNoSeatMatrix' => \App\Models\Counselling\Round::with(['cycle.counselling', 'events.type'])
                ->whereNotIn('round_status', ['cancelled', 'completed'])
                ->whereHas('cycle', fn ($c) => $c->whereNotIn('cycle_status', ['cancelled', 'completed']))
                ->whereDoesntHave('seatMatrixVersions', fn ($v) => $v->where('is_live', true))->get()
                ->filter(fn ($r) => $r->events->contains(fn ($e) => $e->type?->slug === 'choice-filling' && $e->status === 'open'))->values(),

            // A round marked completed for 14+ days that still has no cutoffs.
            'cNoCutoffs' => \App\Models\Counselling\Round::with('cycle.counselling')->where('round_status', 'completed')
                ->where('updated_at', '<', now()->subDays(14))->whereDoesntHave('cutoffs')->get(),

            // Public counselling with no current cycle for this year.
            'cNoCurrent' => Counselling::where('visibility', 'public')->where('is_active', true)->where('is_archived', false)
                ->whereDoesntHave('cycles', fn ($q) => $q->where('is_current', true)->where('year', '>=', now()->year))->get(),
        ];
    }

    public function audit(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->type, fn ($q, $t) => $q->where('auditable_type', 'like', '%\\' . $t))
            ->when($request->action, fn ($q, $a) => $q->where('action', $a))
            ->latest('id')->paginate(30)->withQueryString();

        return view('admin.exam-module.audit', compact('logs'));
    }
}
