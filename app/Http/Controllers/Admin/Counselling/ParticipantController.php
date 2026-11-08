<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Participant;
use App\Models\Counselling\Quota;
use App\Support\Counselling\CnsBlocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Participating institutes of a cycle: college + course + quota. Bulk data comes in through the import centre (CSV);
 * "Search & add" adds a college's courses × quotas in one go; one row at a time is added or edited in the row form.
 */
class ParticipantController extends Controller
{
    public function index(Request $request, CounsellingCycle $cycle)
    {
        // filtered rows of this cycle
        $filtered = fn () => Participant::where('cns_participants.cycle_id', $cycle->id)
            ->when($request->quota, fn ($q, $v) => $q->where('cns_participants.quota_id', $v))
            ->when($request->institute_type, fn ($q, $v) => $q->where('cns_participants.institute_type', $v))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('cns_participants.institute_code', 'like', "%$s%")
                ->orWhereIn('cns_participants.organisation_id', \App\Models\Organisation::where('name', 'like', "%$s%")->select('id'))
                ->orWhereIn('cns_participants.course_id', \App\Models\Course::where('name', 'like', "%$s%")->select('id'))));

        // one row per institute (A–Z): its number of courses and its fee range; its courses open from the course count (courses())
        $colleges = $filtered()->leftJoin('organisations as o', 'o.id', '=', 'cns_participants.organisation_id')
            ->groupBy('cns_participants.organisation_id', 'o.name')
            ->orderBy('o.name')
            ->selectRaw('cns_participants.organisation_id, o.name, COUNT(DISTINCT cns_participants.course_id) as courses, COUNT(*) as rows_count,
                COUNT(DISTINCT cns_participants.quota_id) as quotas, MIN(cns_participants.annual_tuition_fee) as fee_min, MAX(cns_participants.annual_tuition_fee) as fee_max')
            ->paginate(50)->withQueryString();
        $cities = DB::table('campuses')->whereIn('organisation_id', $colleges->pluck('organisation_id'))->whereNull('deleted_at')
            ->orderByRaw("campus_role = 'main' desc")->orderBy('sort_order')->get(['organisation_id', 'city'])
            ->groupBy('organisation_id')->map(fn ($g) => $g->pluck('city')->filter()->first());

        return view('admin.counselling.participants.index', [
            'cycle' => $cycle->load('counselling'), 'colleges' => $colleges, 'cities' => $cities,
            'quotas' => Quota::where('status', true)->orderBy('sort_order')->get(['id', 'name']),
            'total' => Participant::where('cycle_id', $cycle->id)->count(),
        ]);
    }

    /** One institute's courses in this cycle (opened from the course count): each course once, its quotas and fee range. */
    public function courses(CounsellingCycle $cycle, int $organisation)
    {
        $rows = Participant::where('cycle_id', $cycle->id)->where('organisation_id', $organisation)
            ->with(['course:id,name', 'quota:id,name,sort_order'])->get();
        $courses = $rows->groupBy('course_id')->map(fn ($g) => [
            'name' => $g->first()->course->name ?? '(deleted course #' . $g->first()->course_id . ')',
            'id' => $g->first()->course_id,
            'rows' => $g->sortBy(fn ($p) => $p->quota->sort_order ?? 999)->values(),
            'fee_min' => $g->pluck('annual_tuition_fee')->filter(fn ($v) => $v !== null)->min(),
            'fee_max' => $g->pluck('annual_tuition_fee')->filter(fn ($v) => $v !== null)->max(),
            'seats' => $g->pluck('total_seats')->filter(fn ($v) => $v !== null)->sum() ?: null,
        ])->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        $college = DB::table('organisations')->where('id', $organisation)->value('name') ?? '(deleted college #' . $organisation . ')';
        $quotas = Quota::where('status', true)->orderBy('sort_order')->get(['id', 'name']);
        $courseIds = $rows->pluck('course_id')->unique()->values();
        return view('admin.counselling.participants._courses', compact('cycle', 'courses', 'college', 'quotas', 'organisation', 'courseIds'));
    }

    public function store(Request $request, CounsellingCycle $cycle)
    {
        Participant::create($this->validated($request, $cycle) + ['cycle_id' => $cycle->id]);
        return back()->with('success', 'Participant added.');
    }

    /**
     * "Search & add": colleges by name (or short / brand name), with the city and type so same-named colleges can be told apart.
     */
    public function colleges(Request $request, CounsellingCycle $cycle)
    {
        $q = trim((string) $request->q);
        $rows = DB::table('organisations as o')->leftJoin('organisation_types as t', 't.id', '=', 'o.organisation_type_id')
            ->when($q !== '', fn ($b) => $b->where(fn ($w) => $w->where('o.name', 'like', "%$q%")->orWhere('o.short_name', 'like', "%$q%")->orWhere('o.brand_name', 'like', "%$q%")))
            ->orderByRaw('o.name LIKE ? DESC', [$q . '%'])->orderBy('o.name')->limit(30)
            ->get(['o.id', 'o.name', 'o.short_name', 't.title as type',
                DB::raw('(SELECT c.city FROM campuses c WHERE c.organisation_id = o.id AND c.deleted_at IS NULL ORDER BY c.campus_role = \'main\' DESC, c.sort_order LIMIT 1) as city'),
                DB::raw('(SELECT COUNT(*) FROM organisation_courses oc WHERE oc.organisation_id = o.id) as courses'),
                DB::raw('(SELECT COUNT(*) FROM cns_participants p WHERE p.organisation_id = o.id AND p.cycle_id = ' . (int) $cycle->id . ') as added')]);
        return response()->json(['results' => $rows->map(fn ($r) => [
            'id' => $r->id,
            'text' => $r->name . ($r->city ? ' — ' . $r->city : '') . ($r->type ? ' · ' . $r->type : ''),
            'courses' => (int) $r->courses, 'added' => (int) $r->added,
        ])]);
    }

    /**
     * The courses a college offers (its programmes in the CRM) with the yearly fee taken from them, the quotas each already
     * has in this cycle, and the institute type from the college's record. Nothing here is asked again in the pop-up.
     */
    public function collegeCourses(CounsellingCycle $cycle, int $organisation)
    {
        $org = DB::table('organisations')->where('id', $organisation)->first() ?? abort(404);
        $added = Participant::where('cycle_id', $cycle->id)->where('organisation_id', $organisation)->get(['course_id', 'quota_id'])
            ->groupBy('course_id')->map(fn ($g) => $g->pluck('quota_id')->all());
        $fees = $this->yearlyFees($organisation, $varies);
        $courses = DB::table('organisation_courses as oc')->join('courses as c', 'c.id', '=', 'oc.course_id')
            ->leftJoin('program_levels as l', 'l.id', '=', 'c.program_level_id')
            ->where('oc.organisation_id', $organisation)
            ->groupBy('c.id', 'c.name', 'l.title')->orderBy('l.title')->orderBy('c.name')
            ->get(['c.id', 'c.name', 'l.title as level']);
        // courses already added in this cycle that the college no longer lists are shown too
        $extra = array_diff($added->keys()->all(), $courses->pluck('id')->all());
        if ($extra) {
            $courses = $courses->merge(DB::table('courses as c')->leftJoin('program_levels as l', 'l.id', '=', 'c.program_level_id')
                ->whereIn('c.id', $extra)->get(['c.id', 'c.name', 'l.title as level']));
        }
        $type = self::instituteType($org);
        return response()->json([
            'type' => $type ? CnsBlocks::INSTITUTE_TYPES[$type] : null,
            'courses' => $courses->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->name, 'level' => $c->level, 'fee' => $fees[$c->id] ?? null, 'fee_varies' => in_array($c->id, $varies), 'quotas' => $added[$c->id] ?? [],
            ])->values(),
        ]);
    }

    /** The add address opened with GET (page reloaded after adding, or sent back here after logging in again): nothing is saved. */
    public function bulkReopened(CounsellingCycle $cycle)
    {
        return redirect()->route('admin.counselling.participants.index', ['cycle' => $cycle, 'add' => 1])
            ->with('error', 'Nothing was added from that page. If you were adding rows, pick the college and courses again and press Add rows.');
    }

    /** Adds one row per ticked course × quota for a college; rows already in the cycle are skipped. */
    public function bulkStore(Request $request, CounsellingCycle $cycle)
    {
        $data = $request->validate([
            'organisation_id' => ['required', Rule::exists('organisations', 'id')],
            'course_ids' => 'required|array|min:1|max:1000', 'course_ids.*' => ['integer', Rule::exists('courses', 'id')],
            'quota_ids' => 'required|array|min:1|max:30', 'quota_ids.*' => ['integer', Rule::exists('cns_quotas', 'id')],
        ], [
            'organisation_id.required' => 'Pick a college first.',
            'course_ids.required' => 'Tick at least one course.',
            'quota_ids.required' => 'Pick at least one quota.',
            'course_ids.*.exists' => 'One of the ticked courses is no longer in the master list. Pick the college again.',
            'quota_ids.*.exists' => 'One of the ticked quotas no longer exists. Reload the page.',
        ]);
        @set_time_limit(300); // a whole college (hundreds of courses × several quotas) can take a while
        $existing = Participant::where('cycle_id', $cycle->id)->where('organisation_id', $data['organisation_id'])->get(['course_id', 'quota_id'])
            ->map(fn ($p) => $p->course_id . '-' . $p->quota_id)->flip();
        // from the college's own records: its type, and each course's yearly fee. The counselling-only details (authority
        // code, bond, seats, other fees) are not kept on the college: they come from the CSV or the row's edit button.
        $org = DB::table('organisations')->where('id', $data['organisation_id'])->first();
        $type = self::instituteType($org);
        $fees = $this->yearlyFees((int) $data['organisation_id']);
        $added = $skipped = 0;
        DB::transaction(function () use ($data, $cycle, $existing, $fees, $type, &$added, &$skipped) {
            foreach (array_unique(array_map('intval', $data['course_ids'])) as $courseId) {
                foreach (array_unique(array_map('intval', $data['quota_ids'])) as $quotaId) {
                    if (isset($existing[$courseId . '-' . $quotaId])) { $skipped++; continue; }
                    Participant::create(['cycle_id' => $cycle->id, 'organisation_id' => $data['organisation_id'], 'course_id' => $courseId, 'quota_id' => $quotaId,
                        'institute_type' => $type, 'annual_tuition_fee' => $fees[$courseId] ?? null]);
                    $added++;
                }
            }
        });
        $college = DB::table('organisations')->where('id', $data['organisation_id'])->value('name');
        $msg = $added ? "Added $added " . \Illuminate\Support\Str::plural('row', $added) . " for $college." : "Nothing new to add for $college.";
        if ($skipped) $msg .= ' ' . ($skipped === 1 ? '1 row was' : "$skipped rows were") . ' already there and left as is.';
        return redirect()->route('admin.counselling.participants.index', ['cycle' => $cycle, 'search' => $college, 'open' => $data['organisation_id']])->with($added ? 'success' : 'error', $msg);
    }

    /**
     * The counselling's institute type from the college's record. Deemed and central come first (from its legal type or
     * university type); then its ownership (Government / Private / Trust …), which is the most reliably filled; then what
     * is left of the legal and university type. Null when the record says nothing.
     */
    public static function instituteType(?object $org): ?string
    {
        if (!$org) return null;
        $legal = (string) ($org->legal_entity_type ?? '');
        $u = strtolower(trim((string) ($org->university_type ?? '')));
        if ($legal === 'deemed' || str_contains($u, 'deemed')) return 'deemed';
        if ($legal === 'central' || str_contains($u, 'central')) return 'central_university';
        $own = strtolower(trim((string) ($org->ownership_type ?? '')));
        if (str_contains($own, 'govern') || str_contains($own, 'public')) return 'government';
        if (preg_match('/private|trust|minority|society|ppp/', $own)) return 'private';
        if ($legal === 'autonomous_college' || (!empty($org->autonomous_status) && ($org->organisation_type_id ?? null) == 2)) return 'autonomous';
        return match (true) {
            in_array($legal, ['state_public', 'institution_of_national_importance'], true), str_contains($u, 'state'), str_contains($u, 'government') => 'government',
            $legal === 'state_private', str_contains($u, 'private') => 'private',
            $own !== '' => 'other',
            default => null,
        };
    }

    /**
     * Each course's yearly fee at a college (course id => rupees): the "₹2,50,000 / Year" fee text when it gives one,
     * else the total course fee divided by the course's years. A course listed more than once (specialisations, campuses)
     * gets its lowest yearly fee; $varies collects the courses whose fees differ. Courses with neither are left out.
     */
    private function yearlyFees(int $organisationId, ?array &$varies = null): array
    {
        $all = [];
        $rows = DB::table('organisation_courses as oc')->join('courses as c', 'c.id', '=', 'oc.course_id')->where('oc.organisation_id', $organisationId)
            ->get(['oc.course_id', 'oc.fees', 'oc.total_fees', 'oc.duration', 'c.duration as course_duration']);
        foreach ($rows as $r) {
            $fee = null;
            $text = str_replace(',', '', (string) $r->fees);
            $dur = (string) ($r->duration ?: $r->course_duration);
            if (preg_match('/(\d+(?:\.\d+)?)\s*(lakhs?|lacs?|l\b)?[^\d]{0,12}(?:\/\s*y(?:ea)?r|per\s+(?:year|annum)|annual|p\.?\s?a\b|yearly)/i', $text, $m)) {
                $fee = (float) $m[1] * (!empty($m[2]) ? 100000 : 1);
            } elseif ($r->total_fees > 0) {
                $years = match (true) {
                    (bool) preg_match('/(\d+(?:\.\d+)?)\s*(year|yr)/i', $dur, $y) => (float) $y[1],
                    (bool) preg_match('/(\d+)\s*month/i', $dur, $mo) => max(1, (int) $mo[1]) / 12,
                    (bool) preg_match('/^\s*(\d+(?:\.\d+)?)\s*$/', $dur, $n) => (float) $n[1],   // "4" = 4 years
                    default => null,
                };
                if ($years) $fee = round((float) $r->total_fees / max($years, 1));
            }
            if ($fee !== null && $fee >= 1000) $all[$r->course_id][] = $fee;
        }
        $varies = array_keys(array_filter($all, fn ($f) => min($f) != max($f)));
        return array_map('min', $all);
    }

    public function update(Request $request, Participant $participant)
    {
        $participant->update($this->validated($request, $participant->cycle, $participant));
        return back()->with('success', 'Participant updated.');
    }

    public function destroy(Participant $participant)
    {
        $participant->delete();
        return back()->with('success', 'Participant removed.');
    }

    private function validated(Request $request, CounsellingCycle $cycle, ?Participant $existing = null): array
    {
        return $request->validate([
            'organisation_id' => ['required', Rule::exists('organisations', 'id')],
            'course_id' => ['required', Rule::exists('courses', 'id')],
            'quota_id' => ['required', Rule::exists('cns_quotas', 'id'),
                // one college + course + quota per cycle
                Rule::unique('cns_participants', 'quota_id')->where('cycle_id', $cycle->id)->where('organisation_id', $request->organisation_id)
                    ->where('course_id', $request->course_id)->ignore($existing?->id)],
            'institute_type' => ['nullable', Rule::in(array_keys(CnsBlocks::INSTITUTE_TYPES))],
            'institute_code' => 'nullable|string|max:60',
            'annual_tuition_fee' => 'nullable|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'bond_details' => 'nullable|string|max:2000',
            'total_seats' => 'nullable|integer|min:0',
        ], ['quota_id.unique' => 'This college, course and quota is already a participant of this cycle.']);
    }
}
