<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ExamModule\PublicExamPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** GET /api/v1/exams/{slug}?year= : the public exam page (dates, eligibility, pattern, syllabus, fees, FAQs, counsellings). */
class ExamApiController extends Controller
{
    public function __construct(private PublicExamPresenter $presenter)
    {
    }

    /** GET /api/v1/exams : published exams for listings (filters and sort: see PublicExamPresenter::list). */
    public function index(Request $request): JsonResponse
    {
        $key = 'cns:api:exams:' . sha1(http_build_query($request->query()));
        return response()->json(Cache::remember($key, config('cns.cache_ttl'), fn () => $this->presenter->list($request->query())));
    }

    /** GET /api/v1/exam-lookup?q[]=... : which published exam each name / short name / alias / slug means (null = none). */
    public function lookup(Request $request): JsonResponse
    {
        $terms = array_values(array_filter((array) $request->query('q', []), fn ($t) => is_string($t) && trim($t) !== ''));
        $key = 'cns:api:exam-lookup:' . sha1(json_encode($terms));
        return response()->json(Cache::remember($key, config('cns.cache_ttl'), fn () => $this->presenter->lookup($terms)));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $exam = $this->presenter->find($slug);
        abort_unless($exam, 404, 'Exam not found.');

        $year = $request->filled('year') ? (int) $request->year : null;
        $cycle = $this->presenter->cycleFor($exam, $year);
        abort_if($year && !$cycle, 404, 'No public cycle for that year.');

        $key = 'cns:api:exam:' . $exam->id . ':' . ($cycle?->id ?? 0);
        return response()->json(Cache::remember($key, config('cns.cache_ttl'), fn () => $this->presenter->page($exam, $cycle)));
    }
}
