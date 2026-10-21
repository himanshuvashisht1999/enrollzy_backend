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
            'canonical' => $this->cns->frontend('exams/' . $exam->slug),
            'robots' => 'index,follow',
        ];
        return $payload;
    }
}
