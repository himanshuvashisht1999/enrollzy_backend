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
