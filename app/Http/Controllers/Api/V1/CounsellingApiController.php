<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Counselling\PublicPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Public, read-only counselling endpoints. Everything goes through PublicPresenter, which decides what is visible. */
class CounsellingApiController extends Controller
{
    public function __construct(private PublicPresenter $presenter)
    {
    }

    /** GET /api/v1/counselling : filters: q, level, state, program_level, featured, page, per_page */
    public function index(Request $request): JsonResponse
    {
        return $this->cached($request, function () use ($request) {
            $perPage = max(1, min(50, (int) $request->input('per_page', 20)));
            $q = $this->presenter->visible()->with('conductingBody')
                ->when($request->q, fn ($b, $s) => $b->where(fn ($b) => $b->where('name', 'like', "%$s%")->orWhere('short_name', 'like', "%$s%")->orWhere('aliases', 'like', "%$s%")))
                ->when($request->level, fn ($b, $v) => $b->where('level', $v))
                ->when($request->state, fn ($b, $v) => $b->where('states', 'like', '%"' . str_replace(['%', '"'], '', $v) . '"%'))
                ->when($request->program_level, fn ($b, $v) => $b->where('program_levels', 'like', '%' . (int) $v . '%'))
                ->when($request->boolean('featured'), fn ($b) => $b->where('is_featured', true))
                ->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name');

            $page = $q->paginate($perPage)->withQueryString();
            return [
                'data' => $page->getCollection()->map(fn ($c) => $this->presenter->listItem($c))->all(),
                'meta' => ['page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()],
            ];
        });
    }

    /** GET /api/v1/counselling/{slug}?year=2025&session=... : the full page for the current (or a past) public cycle */
    public function show(Request $request, string $slug): JsonResponse
    {
        $c = $this->presenter->find($slug);
        abort_unless($c, 404, 'Counselling not found.');

        $year = $request->filled('year') ? (int) $request->year : null;
        $cycle = $this->presenter->cycleFor($c, $year, $request->input('session'));
        abort_if($year && !$cycle, 404, 'No public cycle for that year.');

        return $this->cached($request, fn () => $this->presenter->page($c, $cycle));
    }

    /** GET /api/v1/counselling/{slug}/institutes : filters: q, quota, institute_type, state, year */
    public function institutes(Request $request, string $slug): JsonResponse
    {
        [$c, $cycle] = $this->resolve($request, $slug);
        return $this->cached($request, fn () => $this->presenter->institutes($c, $cycle, $request->query()));
    }

    /** GET /api/v1/counselling/{slug}/cutoffs : the cutoffs explorer. filters: round, exam, quota, category, gender, pwd, institute_type, state, course, rank_type, q, sort, year */
    public function cutoffs(Request $request, string $slug): JsonResponse
    {
        [$c, $cycle] = $this->resolve($request, $slug);
        return $this->cached($request, fn () => $this->presenter->cutoffs($c, $cycle, $request->query()));
    }

    private function resolve(Request $request, string $slug): array
    {
        $c = $this->presenter->find($slug);
        abort_unless($c, 404, 'Counselling not found.');
        $cycle = $this->presenter->cycleFor($c, $request->filled('year') ? (int) $request->year : null, $request->input('session'));
        abort_unless($cycle, 404, 'No public cycle yet.');
        return [$c, $cycle];
    }

    private function cached(Request $request, \Closure $build): JsonResponse
    {
        $key = 'cns:api:' . sha1($request->path() . '?' . http_build_query($request->query()));
        $data = Cache::remember($key, config('cns.cache_ttl'), $build);
        return response()->json($data);
    }
}
