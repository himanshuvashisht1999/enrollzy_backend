<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Graduate survey responses (table graduate_surveys, filled by the website's /survey/graduates): one row per mobile.
 * The list filters by graduation, plan, state, status, dates and a search; ?export=csv gives the filtered rows with
 * every answer in its own column. A response's page shows all its answers, the student it matches, and the team's
 * follow-up (status and notes).
 */
class GraduateSurveyController extends Controller
{
    public const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'not_interested' => 'Not interested'];

    public function index(Request $request)
    {
        $q = $this->filtered($request);
        if ($request->query('export') === 'csv') return $this->csv((clone $q)->orderByDesc('updated_at')->get());

        $rows = (clone $q)->orderByDesc('updated_at')->paginate(25)->withQueryString();
        $base = DB::table('graduate_surveys');
        return view('admin.graduate_surveys.index', [
            'rows' => $rows,
            'statuses' => self::STATUSES,
            'streams' => (clone $base)->select('stream', 'stream_label')->distinct()->orderBy('stream_label')->pluck('stream_label', 'stream'),
            'aims' => (clone $base)->select('aim', 'aim_label')->distinct()->orderBy('aim_label')->pluck('aim_label', 'aim'),
            'states' => (clone $base)->whereNotNull('current_state')->distinct()->orderBy('current_state')->pluck('current_state'),
            'totals' => [
                'all' => (clone $base)->count(),
                'today' => (clone $base)->where('created_at', '>=', now()->startOfDay())->count(),
                'new' => (clone $base)->where('status', 'new')->count(),
                'filtered' => (clone $q)->count(),
            ],
            'byAim' => (clone $q)->selectRaw('aim_label, COUNT(*) n')->groupBy('aim_label')->orderByDesc('n')->pluck('n', 'aim_label'),
        ]);
    }

    public function show(int $survey)
    {
        $s = DB::table('graduate_surveys')->where('id', $survey)->first();
        abort_unless($s, 404);
        return view('admin.graduate_surveys.show', [
            's' => $s, 'answers' => json_decode($s->answers, true) ?: [], 'utm' => json_decode((string) $s->utm, true) ?: [],
            'statuses' => self::STATUSES,
            'student' => $s->customer_id ? DB::table('users')->where('id', $s->customer_id)->first(['id', 'name', 'phone', 'email', 'city', 'state']) : null,
        ]);
    }

    /** the team's follow-up: status and notes */
    public function update(Request $request, int $survey)
    {
        $data = $request->validate(['status' => 'required|in:' . implode(',', array_keys(self::STATUSES)), 'notes' => 'nullable|string|max:5000']);
        abort_unless(DB::table('graduate_surveys')->where('id', $survey)->exists(), 404);
        DB::table('graduate_surveys')->where('id', $survey)->update($data); // updated_at stays the latest submission's time
        return back()->with('success', 'Saved.');
    }

    private function filtered(Request $request)
    {
        $q = DB::table('graduate_surveys');
        foreach (['stream', 'aim', 'status', 'current_state'] as $f) {
            if ($request->filled($f)) $q->where($f, $request->input($f));
        }
        if ($request->filled('from')) $q->whereDate('updated_at', '>=', $request->input('from'));
        if ($request->filled('to')) $q->whereDate('updated_at', '<=', $request->input('to'));
        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
                ->orWhere('preferred_course', 'like', "%{$s}%")->orWhere('preferred_place', 'like', "%{$s}%")->orWhere('focus', 'like', "%{$s}%"));
        }
        if ($request->input('student') === 'yes') $q->whereNotNull('customer_id');
        if ($request->input('student') === 'no') $q->whereNull('customer_id');
        return $q;
    }

    /** the filtered responses: the main columns, then one column per question asked anywhere in them */
    private function csv($rows)
    {
        $questions = [];
        foreach ($rows as $r) foreach (json_decode($r->answers, true) ?: [] as $a) $questions[$a['key']] ??= $a['q'];
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF"); // Excel reads the ₹ and other symbols as UTF-8
        fputcsv($out, array_merge(['ID', 'Name', 'Mobile', 'Email', 'Current state', 'Graduation', 'Graduation status', 'Plan', 'Preferred course',
            'Preferred place', 'Focus', 'Existing student', 'Status', 'Notes', 'Submissions', 'First submitted', 'Last submitted', 'UTM source', 'UTM campaign'], array_values($questions)));
        foreach ($rows as $r) {
            $a = collect(json_decode($r->answers, true) ?: [])->pluck('a', 'key');
            $utm = json_decode((string) $r->utm, true) ?: [];
            fputcsv($out, array_merge([$r->id, $r->name, $r->phone, $r->email, $r->current_state, $r->stream_label, $r->grad_status_label, $r->aim_label,
                $r->preferred_course, $r->preferred_place, $r->focus, $r->customer_id ? 'Yes (#' . $r->customer_id . ')' : 'No', self::STATUSES[$r->status] ?? $r->status,
                $r->notes, $r->submissions, $r->created_at, $r->updated_at, $utm['utm_source'] ?? '', $utm['utm_campaign'] ?? ''],
                array_map(fn ($k) => $a[$k] ?? '', array_keys($questions))));
        }
        rewind($out);
        return response(stream_get_contents($out), 200, ['Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="graduate-survey-' . now()->format('Y-m-d') . '.csv"']);
    }
}
