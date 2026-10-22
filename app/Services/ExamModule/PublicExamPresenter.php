<?php

namespace App\Services\ExamModule;

use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\Exam;
use App\Services\Counselling\PublicPresenter;
use App\Support\ExamModule\ContentBlocks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The PUBLIC exam page payload (GET /api/v1/exams/{slug}). Same visibility rules as counselling:
 * an exam must be public and active, a cycle must have its public switch on, deleted rows are never returned.
 */
class PublicExamPresenter
{
    /** The four boxes at the top of the exam page. */
    public const STATUS_CARD = ['registration' => 'Registration', 'admit-card' => 'Admit card', 'exam-date' => 'Exam date', 'result' => 'Result'];

    public function __construct(private PublicPresenter $cns)
    {
    }

    public function find(string $slug): ?Exam
    {
        return Exam::where('slug', $slug)->where('visibility', 'public')->where('status', true)->with('conductingBody')->first();
    }

    /** The requested year, else the current public cycle, else the latest public one. */
    public function cycleFor(Exam $exam, ?int $year = null): ?Cycle
    {
        $q = Cycle::where('exam_id', $exam->id)->where('is_public', true);
        if ($year) return $q->where('year', $year)->orderByDesc('id')->first();
        return (clone $q)->where('is_current', true)->first() ?? $q->orderByDesc('year')->orderByDesc('id')->first();
    }

    // ------------------------------------------------------------------ listing (/top-exams, homepage, menus)

    /** Exam modes used by the listing's "mode" filter (the website's CBT / pen & paper / online / offline pages). */
    public const MODE_GROUPS = ['cbt' => ['cbt'], 'pen_paper' => ['pen_paper'], 'online' => ['cbt', 'home_based'], 'offline' => ['pen_paper']];

    /** Published (public + active) exams; archived ones keep their page but are left out of lists. */
    public function visible()
    {
        return Exam::where('visibility', 'public')->where('status', true)->where('is_archived', false);
    }

    /**
     * GET /exams. Filters: q, type, category (ids, comma or array), body (conducting organisation id), featured=1,
     * frequency (comma: yearly, twice_a_year, quarterly, monthly, on_demand), mode (cbt | pen_paper | online | offline),
     * category_like (text in a category name), vacancy=1 (recruitment exams), ids (comma). Sort: latest (default) | name | featured.
     */
    public function list(array $f): array
    {
        $perPage = max(1, min(100, (int) ($f['per_page'] ?? 12)));
        $list = fn ($v) => array_values(array_filter(array_map('trim', is_array($v) ? $v : explode(',', (string) $v)), 'strlen'));
        $q = $this->visible()->with('conductingBody');

        if (!empty($f['q'])) {
            $s = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($f['q'])) . '%';
            $q->where(fn ($w) => $w->where('name', 'like', $s)->orWhere('short_name', 'like', $s)->orWhere('aliases', 'like', $s)
                ->orWhereIn('conducting_organisation_id', DB::table('organisations')->where('name', 'like', $s)->orWhere('short_name', 'like', $s)->select('id')));
        }
        if (!empty($f['type'])) $q->whereIn('exam_type', $list($f['type']));
        if ($cats = $list($f['category'] ?? [])) {
            $q->where(function ($w) use ($cats) {
                foreach ($cats as $id) {
                    $w->orWhere('exam_categories', 'like', '%"' . (int) $id . '"%')->orWhereJsonContains('exam_categories', (int) $id);
                }
            });
        }
        if (!empty($f['category_like'])) {
            $ids = DB::table('exam_categories')->where('title', 'like', '%' . $f['category_like'] . '%')->pluck('id')->all();
            $q->where(function ($w) use ($ids) {
                $w->whereRaw('1 = 0');
                foreach ($ids as $id) $w->orWhere('exam_categories', 'like', '%"' . (int) $id . '"%')->orWhereJsonContains('exam_categories', (int) $id);
            });
        }
        if (!empty($f['body'])) $q->where('conducting_organisation_id', (int) $f['body']);
        if (!empty($f['featured']) && filter_var($f['featured'], FILTER_VALIDATE_BOOLEAN)) $q->where('featured', true);
        if ($freq = $list($f['frequency'] ?? [])) $q->whereIn('frequency', $freq);
        if (!empty($f['mode']) && isset(self::MODE_GROUPS[$f['mode']])) {
            $modes = self::MODE_GROUPS[$f['mode']];
            $q->whereExists(fn ($e) => $e->select(DB::raw(1))->from('exm_patterns as p')->join('exm_cycles as c', 'c.id', '=', 'p.cycle_id')
                ->whereColumn('c.exam_id', 'exm_exams.id')->where('c.is_public', true)->whereNull('c.deleted_at')->whereIn('p.mode', $modes));
        }
        if (!empty($f['vacancy'])) {
            // recruitment (vacancy-based) exams: by category, or by a recruitment word in the name
            $words = ['recruit', 'job', 'civil service', 'government', 'defence', 'bank', 'railway', 'staff selection', 'police', 'clerk', 'officer'];
            $catIds = DB::table('exam_categories')->where(fn ($w) => array_map(fn ($x) => $w->orWhere('title', 'like', "%$x%"), $words))->pluck('id')->all();
            $q->where(function ($w) use ($catIds, $words) {
                foreach ($catIds as $id) $w->orWhere('exam_categories', 'like', '%"' . (int) $id . '"%');
                foreach ($words as $x) $w->orWhere('name', 'like', "%$x%");
            });
        }
        if ($ids = $list($f['ids'] ?? [])) $q->whereIn('id', array_map('intval', $ids));

        match ($f['sort'] ?? 'latest') {
            'name' => $q->orderBy('name'),
            'featured' => $q->orderByDesc('featured')->orderBy('sort_order')->orderBy('name'),
            default => $q->orderBy('sort_order')->orderByDesc('id'),
        };
        $page = $q->paginate($perPage)->withQueryString();

        $all = $this->visible();
        return [
            'data' => $page->getCollection()->map(fn ($e) => $this->listItem($e))->all(),
            'meta' => ['page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()],
            'filters' => [
                'types' => (clone $all)->whereNotNull('exam_type')->distinct()->pluck('exam_type')
                    ->map(fn ($t) => ['value' => $t, 'label' => Exam::TYPES[$t] ?? ucfirst($t)])->sortBy('label')->values()->all(),
                'categories' => collect($this->categoryNames())->only((clone $all)->pluck('exam_categories')->flatten()->filter()->map(fn ($v) => (int) $v)->unique()->all())
                    ->map(fn ($n, $id) => ['id' => $id, 'name' => $n])->sortBy('name')->values()->all(),
                'bodies' => DB::table('organisations')->whereIn('id', (clone $all)->whereNotNull('conducting_organisation_id')->distinct()->pluck('conducting_organisation_id'))
                    ->orderBy('name')->get(['id', 'name', 'short_name'])->map(fn ($o) => ['id' => $o->id, 'name' => $o->name, 'short_name' => $o->short_name])->all(),
                'frequencies' => (clone $all)->whereNotNull('frequency')->distinct()->pluck('frequency')
                    ->map(fn ($v) => ['value' => $v, 'label' => Exam::FREQUENCIES[$v] ?? $v])->values()->all(),
            ],
        ];
    }

    /** One exam as a card: identity, conducting body, current cycle and its registration / exam date / result status. */
    public function listItem(Exam $exam): array
    {
        $cycle = $this->cycleFor($exam);
        $status = [];
        if ($cycle) {
            foreach (['registration' => 'Registration', 'exam-date' => 'Exam date', 'result' => 'Result'] as $slug => $label) {
                $s = $cycle->statusForType($slug);
                if ($s['state'] !== 'hidden') $status[$slug] = ['label' => $label, 'state' => $s['state'], 'text' => $s['label']];
            }
        }
        $has = fn (string $table) => $cycle ? DB::table($table)->where('cycle_id', $cycle->id)->exists() : false;
        $body = $exam->conductingBody;

        return [
            'id' => $exam->id, 'name' => $exam->name, 'short_name' => $exam->short_name, 'slug' => $exam->slug,
            'url' => $this->cns->frontend('exam/' . $exam->slug),
            'type' => $exam->exam_type, 'type_label' => Exam::TYPES[$exam->exam_type] ?? null,
            'categories' => array_values(array_filter(array_map(fn ($id) => $this->categoryNames()[(int) $id] ?? null, $exam->exam_categories ?? []))),
            'program_levels' => $exam->program_levels ? DB::table('program_levels')->whereIn('id', $exam->program_levels)->pluck('title')->all() : [],
            'frequency' => $exam->frequency, 'frequency_label' => Exam::FREQUENCIES[$exam->frequency] ?? null,
            'conducting_body' => $body ? ['id' => $body->id, 'name' => $body->name, 'short_name' => $body->short_name ?: $body->abbreviation] : null,
            'about' => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $exam->about))), 160) ?: null,
            'logo_url' => $this->cns->file($exam->logo), 'is_featured' => (bool) $exam->featured,
            'cycle' => $cycle ? ['year' => $cycle->year, 'session_label' => $cycle->session_label, 'status' => $cycle->cycle_status] : null,
            'status' => $status,
            'sections' => [
                'dates' => $cycle ? $cycle->events()->exists() : false, 'eligibility' => $has('exm_eligibilities'), 'pattern' => $has('exm_patterns'),
                'syllabus' => $has('exm_syllabus'), 'faqs' => $has('exm_faqs'),
                'counselling' => DB::table('cns_counselling_exams')->where('exam_id', $exam->id)->exists(),
            ],
        ];
    }

    /**
     * GET /exam-lookup?q[]=JEE Main&q[]=old-slug : which published exam each name / short name / alias / slug means.
     * Used for redirects from old exam URLs and for homepage boxes that stored names. Exact (case and punctuation
     * insensitive) matches only; nothing is guessed.
     */
    public function lookup(array $terms): array
    {
        $norm = fn ($s) => trim(preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower((string) $s)));
        $index = [];
        foreach ($this->visible()->get(['id', 'name', 'short_name', 'slug', 'aliases']) as $e) {
            foreach (array_merge([$e->slug, $e->name, $e->short_name], $e->aliases ?? []) as $n) {
                if (($k = $norm($n)) !== '') $index[$k] ??= $e->id;
            }
        }
        $found = Exam::with('conductingBody')->whereIn('id', array_unique($index))->get()->keyBy('id');
        $out = [];
        foreach (array_slice(array_values(array_filter($terms, 'is_string')), 0, 50) as $t) {
            $id = $index[$norm($t)] ?? null;
            $out[$t] = $id && $found->has($id) ? $this->listItem($found[$id]) : null;
        }
        return ['matches' => $out];
    }

    private function categoryNames(): array
    {
        static $names = null;
        return $names ??= DB::table('exam_categories')->pluck('title', 'id')->map(fn ($v) => (string) $v)->all();
    }

    public function page(Exam $exam, ?Cycle $cycle): array
    {
        $body = $exam->conductingBody;
        $payload = [
            'exam' => [
                'id' => $exam->id, 'name' => $exam->name, 'short_name' => $exam->short_name, 'slug' => $exam->slug,
                'type' => $exam->exam_type, 'type_label' => Exam::TYPES[$exam->exam_type] ?? null,
                'categories' => $exam->exam_categories ? DB::table('exam_categories')->whereIn('id', $exam->exam_categories)->pluck('title')->all() : [],
                'program_levels' => $exam->program_levels ? DB::table('program_levels')->whereIn('id', $exam->program_levels)->pluck('title')->all() : [],
                'frequency' => Exam::FREQUENCIES[$exam->frequency] ?? null, 'languages' => $exam->languages ?? [], 'states' => $exam->states ?? [],
                'about' => $exam->about, 'official_website' => $this->cns->safeUrl($exam->official_website),
                'logo_url' => $this->cns->file($exam->logo), 'cover_url' => $this->cns->file($exam->cover_image),
                'conducting_body' => $body ? ['name' => $body->name, 'short_name' => $body->short_name ?: $body->abbreviation, 'slug' => $body->slug,
                    'website' => $this->cns->safeUrl($body->official_website)] : null,
            ],
            'available_years' => Cycle::where('exam_id', $exam->id)->where('is_public', true)->orderByDesc('year')->pluck('year')->unique()->values()->all(),
            'cycle' => null, 'status_card' => [], 'dates' => [], 'stages' => [],
            'eligibility' => [], 'pattern' => [], 'syllabus' => [], 'fees' => [], 'documents' => [], 'faqs' => [], 'papers' => [],
            'counsellings' => $this->cns->examCounsellings($exam->id),
            'last_verified_at' => null,
        ];

        if ($cycle) {
            $events = $cycle->events()->with(['type', 'quota', 'stage'])->orderByRaw('start_at is null')->orderBy('start_at')->get();
            $payload['cycle'] = ['id' => $cycle->id, 'year' => $cycle->year, 'session_label' => $cycle->session_label, 'title' => $cycle->title,
                'status' => $cycle->cycle_status, 'status_note' => $cycle->status_note, 'timezone' => $cycle->timezone, 'is_current' => (bool) $cycle->is_current];
            $payload['status_card'] = collect(self::STATUS_CARD)->map(function ($label, $slug) use ($cycle, $events) {
                $s = $cycle->statusForType($slug);
                $e = $events->first(fn ($e) => $e->type?->slug === $slug && in_array($e->status, ['open', 'upcoming']))
                    ?? $events->filter(fn ($e) => $e->type?->slug === $slug)->last();
                return ['key' => $slug, 'label' => $label, 'state' => $s['state'], 'text' => $s['label'], 'date_text' => $e?->dateText()];
            })->reject(fn ($s) => $s['state'] === 'hidden')->values()->all();
            $payload['dates'] = array_map(function ($row) use ($events) {
                $e = $events->firstWhere('id', $row['id']);
                return $row + ['stage' => $e?->stage?->name];
            }, $this->cns->events($events));
            $payload['stages'] = $cycle->stages()->get(['id', 'name', 'is_qualifying'])->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'is_qualifying' => (bool) $s->is_qualifying])->all();
            $payload['last_verified_at'] = $events->max('last_verified_at')?->toIso8601String();

            $rows = fn (string $table) => DB::table($table)->where('cycle_id', $cycle->id)->orderBy('id')->get();
            $stage = fn ($r) => $r->stage_id ? DB::table('exm_stages')->where('id', $r->stage_id)->value('name') : null;
            $list = fn ($v) => is_array($v) ? $v : (json_decode((string) $v, true) ?: []);
            $num = fn ($v) => $v !== null ? (float) $v : null;
            $categories = ContentBlocks::categories();

            $payload['eligibility'] = $rows('exm_eligibilities')->map(fn ($r) => [
                'stage' => $stage($r),
                'qualification' => $r->min_qualification_id ? DB::table('program_levels')->where('id', $r->min_qualification_id)->value('title') : null,
                'subjects' => $list($r->subjects), 'nationality' => $r->nationality,
                'min_percentage_general' => $num($r->min_percentage_general), 'min_percentage_reserved' => $num($r->min_percentage_reserved),
                'age' => ['min' => $r->age_min, 'max' => $r->age_max, 'as_of' => $r->age_as_of],
                'age_relaxation' => collect($list($r->age_relaxation))->map(fn ($a) => ['category' => $categories[$a['category_id'] ?? 0] ?? null, 'years' => $a['years'] ?? null])->filter(fn ($a) => $a['category'])->values()->all(),
                'max_attempts' => $r->max_attempts, 'appearing_allowed' => $r->appearing_allowed !== null ? (bool) $r->appearing_allowed : null,
                'other_conditions' => $r->other_conditions,
            ])->all();
            $payload['pattern'] = $rows('exm_patterns')->map(fn ($r) => [
                'stage' => $stage($r), 'mode' => ContentBlocks::MODES[$r->mode] ?? null, 'duration_minutes' => $r->duration_minutes,
                'total_questions' => $r->total_questions, 'total_marks' => $num($r->total_marks),
                'negative_marking' => (bool) $r->negative_marking, 'negative_marks' => $num($r->negative_marks),
                'question_types' => $list($r->question_types),
                'sections' => collect($list($r->sections))->map(fn ($s) => ['name' => $s['name'] ?? null, 'questions' => $s['questions'] ?? null, 'marks' => $s['marks'] ?? null])->values()->all(),
            ])->all();
            $payload['syllabus'] = $rows('exm_syllabus')->map(fn ($r) => ['stage' => $stage($r), 'subject' => $r->subject, 'topics' => $list($r->topics), 'pdf_url' => $this->cns->file($r->pdf)])->all();
            $payload['fees'] = $rows('exm_fees')->map(fn ($r) => ['stage' => $stage($r), 'category' => $categories[$r->category_id] ?? null,
                'gender' => ContentBlocks::GENDERS[$r->gender] ?? null, 'amount' => (float) $r->amount, 'currency' => $r->currency])->all();
            $payload['documents'] = $rows('exm_documents')->map(fn ($r) => ['stage' => $stage($r), 'name' => $r->name, 'is_mandatory' => (bool) $r->is_mandatory, 'notes' => $r->notes])->all();
            $payload['faqs'] = DB::table('exm_faqs')->where('cycle_id', $cycle->id)->orderBy('sort_order')->orderBy('id')->get()
                ->map(fn ($r) => ['question' => $r->question, 'answer' => $r->answer])->all();
            $payload['papers'] = DB::table('exm_papers')->where('cycle_id', $cycle->id)->orderByDesc('year')->orderBy('id')->get()
                ->map(fn ($r) => ['title' => $r->title, 'year' => $r->year, 'shift' => $r->shift, 'url' => $this->cns->file($r->file)])->all();
        }

        $year = $cycle?->year;
        $payload['seo'] = [
            'title' => trim(($exam->short_name ?: $exam->name) . ' ' . ($year ?: '') . ' - Dates, Eligibility, Pattern, Syllabus & Counselling'),
            'description' => Str::limit(strip_tags((string) ($exam->about ?: $exam->name . ' dates, eligibility, pattern, syllabus, fees and counselling.')), 155),
            'canonical' => $this->cns->frontend('exam/' . $exam->slug),
            'robots' => 'index,follow',
        ];
        return $payload;
    }
}
