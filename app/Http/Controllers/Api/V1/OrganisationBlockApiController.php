<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Cutoff;
use App\Models\Counselling\Participant;
use App\Services\Counselling\PublicPresenter;
use App\Support\Counselling\CnsBlocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** "Admission through": the block for a college page, with the latest closing rank per category. */
class OrganisationBlockApiController extends Controller
{
    public function __construct(private PublicPresenter $presenter)
    {
    }

    public function show(Request $request, string $organisation)
    {
        $org = DB::table('organisations')->where(is_numeric($organisation) ? 'id' : 'slug', $organisation)->first(['id', 'name', 'slug']);
        abort_unless($org, 404, 'College not found.');

        $data = Cache::remember('cns:api:org-block:' . $org->id, config('cns.cache_ttl'), fn () => $this->build($org));

        if ($request->input('format') === 'html') {
            return response(view('public.counselling.blocks.admission-through', $data)->render(), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        return response()->json($data);
    }

    private function build(object $org): array
    {
        $cycleIds = Participant::where('organisation_id', $org->id)->distinct()->pluck('cycle_id');
        $counsellingIds = CounsellingCycle::whereIn('id', $cycleIds)->pluck('counselling_id')->unique();

        $blocks = [];
        foreach ($this->presenter->visible()->whereIn('id', $counsellingIds)->with('conductingBody')->orderBy('name')->get() as $c) {
            $cycle = $this->presenter->cycleFor($c);          // only the cycle students can see
            if (!$cycle) continue;

            $participants = Participant::where('cycle_id', $cycle->id)->where('organisation_id', $org->id)
                ->join('courses', 'courses.id', '=', 'cns_participants.course_id')->join('cns_quotas', 'cns_quotas.id', '=', 'cns_participants.quota_id')
                ->select('cns_participants.*', 'courses.name as course_name', 'courses.slug as course_slug', 'cns_quotas.name as quota_name')
                ->orderBy('courses.name')->get();
            if ($participants->isEmpty()) continue;

            $rounds = $cycle->rounds()->get(['id', 'name', 'sort_order'])->keyBy('id');
            $cutoffs = Cutoff::where('organisation_id', $org->id)->whereIn('round_id', $rounds->keys())
                ->join('cns_categories', 'cns_categories.id', '=', 'cns_cutoffs.category_id')->join('cns_quotas', 'cns_quotas.id', '=', 'cns_cutoffs.quota_id')
                ->select('cns_cutoffs.*', 'cns_categories.name as category_name', 'cns_quotas.name as quota_name')->get();

            $courses = $participants->groupBy('course_id')->map(function ($rows) use ($cutoffs, $rounds) {
                $courseId = $rows->first()->course_id;
                // Latest round wins for each course + quota + category + gender + PwD + rank type.
                $latest = $cutoffs->where('course_id', $courseId)->sortByDesc(fn ($x) => $rounds[$x->round_id]->sort_order * 1000000 + $x->id)
                    ->unique(fn ($x) => implode('|', [$x->quota_id, $x->category_id, $x->gender ?? '*', (int) $x->pwd, $x->rank_type]))->values();
                return [
                    'course' => ['id' => $courseId, 'name' => $rows->first()->course_name, 'slug' => $rows->first()->course_slug],
                    'quotas' => $rows->map(fn ($p) => ['quota' => $p->quota_name, 'institute_type' => CnsBlocks::INSTITUTE_TYPES[$p->institute_type] ?? null,
                        'total_seats' => $p->total_seats, 'annual_tuition_fee' => $p->annual_tuition_fee !== null ? (float) $p->annual_tuition_fee : null, 'bond_details' => $p->bond_details])->values()->all(),
                    'latest_cutoffs' => $latest->take(40)->map(fn ($x) => [
                        'round' => $rounds[$x->round_id]->name, 'quota' => $x->quota_name, 'category' => $x->category_name, 'gender' => $x->gender ?: 'neutral', 'pwd' => (bool) $x->pwd,
                        'rank_type' => $x->rank_type, 'closing_value' => $x->closing_value !== null ? (float) $x->closing_value : null,
                    ])->all(),
                ];
            })->values()->all();

            $blocks[] = [
                'counselling' => ['name' => $c->name, 'slug' => $c->slug, 'level' => $c->level, 'conducting_body' => $c->conductingBody?->name, 'url' => $this->presenter->frontend('counselling/' . $c->slug)],
                'cycle' => ['year' => $cycle->year, 'session_label' => $cycle->session_label],
                'courses' => $courses,
            ];
        }

        return ['college' => ['id' => $org->id, 'name' => $org->name, 'slug' => $org->slug], 'counsellings' => $blocks];
    }
}
