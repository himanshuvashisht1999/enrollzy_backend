<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\AuditLog;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\CycleEvent;
use App\Models\ExamModule\Exam;
use Illuminate\Http\Request;

class AttentionController extends Controller
{
    /** Needs Attention dashboard (spec section 8). */
    public function index()
    {
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
        $noCurrent = Exam::where('visibility', 'public')->where('status', true)
            ->whereDoesntHave('cycles', fn ($q) => $q->where('is_current', true)->where('year', '>=', now()->year))->get();

        return view('admin.exam-module.attention', compact('overdueResults', 'unverified', 'stalePostponed', 'noCurrent'));
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
