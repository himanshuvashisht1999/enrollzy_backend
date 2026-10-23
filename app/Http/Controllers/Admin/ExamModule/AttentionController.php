<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Counselling;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Round;
use App\Models\ExamModule\AuditLog;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\CycleEvent;
use App\Models\ExamModule\Exam;
use Illuminate\Http\Request;

/**
 * Needs Attention dashboard (exam spec section 8), with a Counselling filter (counselling spec section 15).
 *
 * The dates table is shared: an exam date has cycle_id set, a counselling date has counselling_cycle_id set.
 * Each section reads only its own kind, and only dates whose owner still exists: an exam or counselling that was
 * deleted keeps its cycles and dates in the database, and those must not appear here (nor break the page).
 */
class AttentionController extends Controller
{
    public function index(Request $request)
    {
        $scope = in_array($request->scope, ['exam', 'counselling']) ? $request->scope : 'all';

        return view('admin.exam-module.attention', ['scope' => $scope] + $this->exams() + $this->counselling());
    }

    private function exams(): array
    {
        // Live exam cycles of exams that still exist (the exam relation skips deleted exams).
        $liveCycle = fn ($c) => $c->whereNotIn('cycle_status', ['cancelled', 'completed'])->whereHas('exam');
        $events = CycleEvent::with(['type', 'cycle.exam'])->whereNotNull('cycle_id')->whereHas('cycle', $liveCycle)->get();

        return [
            // 1. Result-type events whose expected date has passed but are not declared.
            'overdueResults' => $events->filter(fn ($e) => $e->type?->isManual()
                && $e->start_at && $e->start_at->isPast()
                && !in_array($e->manual_status, ['declared', 'cancelled']))->values(),

            // 2. Open or upcoming events not verified in the last 7 days.
            'unverified' => $events->filter(fn ($e) => $e->type && !$e->type->isManual()
                && in_array($e->status, ['open', 'upcoming'])
                && (!$e->last_verified_at || $e->last_verified_at->lt(now()->subDays(7))))->values(),

            // 3. Cycles postponed for more than 14 days with no update.
            'stalePostponed' => Cycle::with('exam')->whereHas('exam')->where('cycle_status', 'postponed')
                ->where('updated_at', '<', now()->subDays(14))->get(),

            // 4. Public exams with no current cycle for the coming year.
            'noCurrent' => Exam::where('visibility', 'public')->where('status', true)->where('is_archived', false)
                ->whereDoesntHave('cycles', fn ($q) => $q->where('is_current', true)->where('year', '>=', now()->year))->get(),
        ];
    }

    /** The six counselling checks of the counselling spec, section 15. */
    private function counselling(): array
    {
        // Live counselling cycles of counsellings that still exist; round dates only when the round still exists.
        $liveCycle = fn ($c) => $c->whereNotIn('cycle_status', ['cancelled', 'completed'])->whereHas('counselling');
        $events = CycleEvent::with(['type', 'round', 'counsellingCycle.counselling'])->whereNotNull('counselling_cycle_id')
            ->whereHas('counsellingCycle', $liveCycle)
            ->where(fn ($q) => $q->whereNull('round_id')->orWhereHas('round', fn ($r) => $r->whereNotIn('round_status', ['cancelled'])))
            ->get();
        $liveRounds = fn () => Round::with(['cycle.counselling'])->whereHas('cycle', $liveCycle);

        return [
            // 1. Open or upcoming counselling events not verified in 7 days.
            'cUnverified' => $events->filter(fn ($e) => $e->type && !$e->type->isManual() && in_array($e->status, ['open', 'upcoming'])
                && (!$e->last_verified_at || $e->last_verified_at->lt(now()->subDays(7))))->values(),

            // 2. Allotment result (or any manual result) expected date passed, not declared.
            'cOverdueResults' => $events->filter(fn ($e) => $e->type?->isManual() && $e->start_at && $e->start_at->isPast()
                && !in_array($e->manual_status, ['declared', 'cancelled']))->values(),

            // 3. Choice filling is open but the round has no live seat matrix: students are choosing blind.
            'cNoSeatMatrix' => $liveRounds()->with('events.type')->whereNotIn('round_status', ['cancelled', 'completed'])
                ->whereDoesntHave('seatMatrixVersions', fn ($v) => $v->where('is_live', true))->get()
                ->filter(fn ($r) => $r->events->contains(fn ($e) => $e->type?->slug === 'choice-filling' && $e->status === 'open'))->values(),

            // 4. Round completed but no cutoffs imported 14 days after it finished (its last date; the last edit if it has no dates).
            'cNoCutoffs' => Round::with(['cycle.counselling', 'events'])->whereHas('cycle', fn ($c) => $c->whereHas('counselling'))
                ->where('round_status', 'completed')->whereDoesntHave('cutoffs')->get()
                ->filter(fn ($r) => ($this->finishedAt($r) ?? $r->updated_at)->lt(now()->subDays(14)))->values(),

            // 5. Current cycle with date fields cleared by Copy Cycle and not yet re-entered, or exam cycles that need an update.
            'cNeedsDates' => CounsellingCycle::with('counselling')->whereHas('counselling')->where('is_current', true)->get()
                ->map(fn ($c) => ['cycle' => $c, 'todo' => array_merge($c->needsNewDates(), $c->needsUpdate())])->filter(fn ($x) => $x['todo'])->values(),

            // 6. Public counselling with no current cycle for this year.
            'cNoCurrent' => Counselling::where('visibility', 'public')->where('is_active', true)->where('is_archived', false)
                ->whereDoesntHave('cycles', fn ($q) => $q->where('is_current', true)->where('year', '>=', now()->year))->get(),
        ];
    }

    /** When a round's last dated event ended (extended end, else end, else start). */
    private function finishedAt(Round $round): ?\Carbon\CarbonInterface
    {
        return $round->events->map(fn ($e) => $e->extended_end_at ?? $e->end_at ?? $e->start_at)->filter()->max();
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
