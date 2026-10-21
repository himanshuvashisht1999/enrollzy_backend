<?php

namespace App\Services\Counselling;

use App\Models\Counselling\AllotmentResult;
use App\Models\Counselling\Category;
use App\Models\Counselling\CnsRow;
use App\Models\Counselling\Counselling;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Cutoff;
use App\Models\Counselling\Participant;
use App\Models\Counselling\Quota;
use App\Models\Counselling\Round;
use App\Models\Counselling\SeatMatrixRow;
use App\Models\Counselling\SeatMatrixVersion;
use App\Models\ExamModule\CycleEvent;
use App\Support\Counselling\CnsBlocks;
use App\Support\Counselling\Inheritance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds the PUBLIC payloads. This is the one place that decides what a student may see:
 *   - a counselling must be public and active;
 *   - a cycle must have is_public on (the current cycle is not automatically public);
 *   - nothing from drafts, deleted records or unpublished exams is ever returned.
 * Cancelled and archived counsellings keep their page, flagged, instead of disappearing.
 */
class PublicPresenter
{
    private array $memo = [];

    // ------------------------------------------------------------------ visibility

    public function visible()
    {
        return Counselling::where('visibility', 'public')->where('is_active', true);
    }

    public function find(string $slug): ?Counselling
    {
        return $this->visible()->where('slug', $slug)->with('conductingBody')->first();
    }

    /** The cycle a student should see: the requested year, else the current public one, else the latest public one. */
    public function cycleFor(Counselling $c, ?int $year = null, ?string $session = null): ?CounsellingCycle
    {
        $q = CounsellingCycle::where('counselling_id', $c->id)->where('is_public', true);
        if ($year) {
            return $q->where('year', $year)->when($session, fn ($q, $s) => $q->where('session_label', $s))->orderByDesc('id')->first();
        }
        return (clone $q)->where('is_current', true)->first() ?? $q->orderByDesc('year')->orderByDesc('id')->first();
    }

    public function publicExam(?int $id): ?object
    {
        return $id ? DB::table('exm_exams')->where('id', $id)->whereNull('deleted_at')->where('visibility', 'public')->where('status', true)->first() : null;
    }

    // ------------------------------------------------------------------ list + page

    public function listItem(Counselling $c): array
    {
        $cycle = $this->cycleFor($c);
        $bar = $cycle ? $cycle->statusBar() : null;

        return [
            'id' => $c->id, 'name' => $c->name, 'short_name' => $c->short_name, 'slug' => $c->slug,
            'level' => $c->level, 'states' => $c->states ?? [], 'mode' => $c->mode,
            'conducting_body' => $c->conductingBody?->name, 'logo_url' => $this->file($c->logo),
            'is_featured' => $c->is_featured, 'is_archived' => $c->is_archived,
            'program_levels' => $this->names('program_levels', $c->program_levels),
            'exams' => $this->mappedExams($c)->map(fn ($e) => ['name' => $e['name'], 'short_name' => $e['short_name'], 'slug' => $e['slug']])->all(),
            'current_cycle' => $cycle ? ['year' => $cycle->year, 'session_label' => $cycle->session_label, 'status' => $cycle->cycle_status] : null,
            'current_round' => $bar ? $bar['round']?->name : null,
            'now' => $this->now($cycle, $bar),
            'quotas' => $cycle ? $this->cycleQuotas($cycle) : [],
            'colleges_count' => $cycle ? $this->collegeCount($cycle) : 0,
            'url' => $this->frontend('counselling/' . $c->slug),
            'api_url' => route('api.v1.counselling.show', $c->slug),
        ];
    }

    /** "Counselling after this exam": the public counsellings mapped to an exam, with their status line. */
    public function examCounsellings(int $examId): array
    {
        return \App\Models\Counselling\CounsellingExam::where('exam_id', $examId)->orderBy('sort_order')->get()
            ->map(function ($m) {
                $c = $this->visible()->with('conductingBody')->find($m->counselling_id);
                if (!$c) return null;
                $item = $this->listItem($c);
                $cycle = $this->cycleFor($c);
                $bar = $cycle ? $cycle->statusBar() : null;
                return $item + [
                    'role' => $m->role, 'seat_scope_note' => $m->seat_scope_note,
                    'status' => $cycle ? (in_array($cycle->cycle_status, ['postponed', 'cancelled'])
                        ? ['state' => $cycle->cycle_status, 'text' => ucfirst($cycle->cycle_status) . ($cycle->status_note ? ': ' . $cycle->status_note : '')]
                        : ($bar ? ['state' => 'scheduled', 'text' => ($bar['round'] ? $bar['round']->name : 'Counselling ' . $cycle->title) . ': ' . collect($bar['items'])->map(fn ($s, $l) => "$l - {$s['label']}")->implode(', ')]
                            : ['state' => 'to_be_announced', 'text' => 'Schedule to be announced']))
                        : ['state' => 'to_be_announced', 'text' => 'Schedule to be announced'],
                ];
            })->filter()->values()->all();
    }

    public function page(Counselling $c, ?CounsellingCycle $cycle): array
    {
        $years = CounsellingCycle::where('counselling_id', $c->id)->where('is_public', true)->orderByDesc('year')->pluck('year')->unique()->values()->all();

        $payload = [
            'counselling' => [
                'id' => $c->id, 'name' => $c->name, 'short_name' => $c->short_name, 'slug' => $c->slug, 'aliases' => $c->aliases ?? [],
                'level' => $c->level, 'states' => $c->states ?? [], 'mode' => $c->mode,
                'conducting_body' => $c->conductingBody ? ['name' => $c->conductingBody->name, 'short_name' => $c->conductingBody->short_name ?: $c->conductingBody->abbreviation,
                    'type' => $c->conductingBody->authority_type, 'slug' => $c->conductingBody->slug, 'website' => $this->safeUrl($c->conductingBody->official_website)] : null,
                'program_levels' => $this->names('program_levels', $c->program_levels),
                'course_categories' => $this->names('exam_categories', $c->course_categories),
                'official_website' => $c->official_website, 'registration_portal_url' => $c->registration_portal_url, 'helpline' => $c->helpline,
                'logo_url' => $this->file($c->logo), 'cover_url' => $this->file($c->cover_image),
                'about' => $c->about, 'is_featured' => $c->is_featured, 'is_archived' => $c->is_archived,
            ],
            'exams' => $this->mappedExams($c)->all(),
            'available_years' => $years,
            'cycle' => null,
        ];

        if (!$cycle) {
            $payload['status_bar'] = ['state' => 'to_be_announced', 'label' => 'Counselling schedule to be announced', 'current_round' => null, 'items' => []];
            $payload['seo'] = $this->seo($c, null);
            $payload['marked'] = $this->marked($c, null);
            return $payload;
        }

        $rounds = $cycle->rounds()->with('type')->get();

        $payload['cycle'] = [
            'id' => $cycle->id, 'year' => $cycle->year, 'session_label' => $cycle->session_label, 'title' => $cycle->title,
            'status' => $cycle->cycle_status, 'status_note' => $cycle->status_note, 'timezone' => $cycle->timezone, 'is_current' => $cycle->is_current,
            'information_bulletin' => ['url' => $this->safeUrl($cycle->information_bulletin_url), 'pdf_url' => $this->file($cycle->information_bulletin_pdf)],
            'reservation_policy' => $cycle->reservation_policy, 'seat_allotment_method' => $cycle->seat_allotment_method,
            'willingness_options' => collect($cycle->willingness_options)->map(fn ($w) => CounsellingCycle::WILLINGNESS[$w] ?? $w)->all(),
            'source_url' => $this->safeUrl($cycle->source_url), 'last_verified_at' => $cycle->last_verified_at?->toIso8601String(),
            'exam_cycles' => $cycle->examCycles()->with('exam:id,name,slug')->get()->map(fn ($ec) => ['exam' => $ec->exam->name, 'year' => $ec->year, 'session_label' => $ec->session_label])->all(),
        ];
        $payload['status_bar'] = $this->statusBar($cycle);
        $payload['now'] = $this->now($cycle);
        $payload['dates'] = [
            'cycle' => $this->events($cycle->events()->with(['type', 'quota'])->orderBy('start_at')->get()),
            'rounds' => $rounds->map(fn ($r) => ['round_id' => $r->id, 'name' => $r->name, 'status' => $r->round_status,
                'events' => $this->events($r->events()->with(['type', 'quota'])->orderBy('start_at')->get())])->all(),
        ];
        $payload['eligibility'] = $this->eligibility($cycle);
        $payload['process'] = $this->process($cycle, null);
        $payload['fees'] = $this->rows(CnsRow::forScope('fees', $cycle->id), fn ($r) => $this->fee($r));
        $payload['documents'] = $this->rows(CnsRow::forScope('documents', $cycle->id), fn ($r) => $this->document($r));
        $payload['rules'] = $this->rows(CnsRow::forScope('rules', $cycle->id), fn ($r) => $this->rule($r));
        $payload['rounds'] = $rounds->map(fn ($r) => $this->round($c, $cycle, $r))->all();
        $payload['institutes'] = [
            'count' => Participant::where('cycle_id', $cycle->id)->count(),
            'colleges' => $this->collegeCount($cycle),
            'quotas' => $this->cycleQuotas($cycle),
            'api_url' => route('api.v1.counselling.institutes', $c->slug) . ($cycle->is_current ? '' : '?year=' . $cycle->year),
        ];
        $payload['cutoffs'] = ['count' => Cutoff::whereIn('round_id', $rounds->pluck('id'))->count(),
            'api_url' => route('api.v1.counselling.cutoffs', $c->slug) . ($cycle->is_current ? '' : '?year=' . $cycle->year)];
        $payload['seo'] = $this->seo($c, $cycle);
        $payload['marked'] = $this->marked($c, $cycle);
        $payload['last_updated_at'] = $this->lastUpdated($cycle, $rounds)?->toIso8601String();

        return $payload;
    }

    // ------------------------------------------------------------------ pieces

    private function statusBar(CounsellingCycle $cycle): array
    {
        if (in_array($cycle->cycle_status, ['postponed', 'cancelled'])) {
            return ['state' => $cycle->cycle_status, 'label' => ucfirst($cycle->cycle_status) . ($cycle->status_note ? ': ' . $cycle->status_note : ''), 'current_round' => null, 'items' => []];
        }
        $bar = $cycle->statusBar();
        if (!$bar) {
            return ['state' => 'to_be_announced', 'label' => 'Counselling schedule to be announced', 'current_round' => null, 'items' => []];
        }
        return [
            'state' => 'scheduled', 'label' => $bar['round'] ? $bar['round']->name : 'Counselling ' . $cycle->title,
            'current_round' => $bar['round'] ? ['id' => $bar['round']->id, 'name' => $bar['round']->name] : null,
            'items' => collect($bar['items'])->map(fn ($s, $label) => ['label' => $label, 'state' => $s['state'], 'text' => $s['label']])->values()->all(),
        ];
    }

    /**
     * One-line "what is happening now" for lists and banners, calculated from the status bar (never typed):
     * state open | upcoming | declared | closed | tba | postponed | cancelled, plus the step ("Round 2 choice filling")
     * and when ("Open, closes 18 Aug 2027"). Open steps win, then upcoming ones, then a declared result.
     */
    public function now(?CounsellingCycle $cycle, ?array $bar = null): array
    {
        $tba = ['state' => 'tba', 'label' => 'Awaited', 'step' => 'Schedule awaited', 'when' => 'No dates yet'];
        if (!$cycle) return $tba;
        if (in_array($cycle->cycle_status, ['postponed', 'cancelled'])) {
            return ['state' => $cycle->cycle_status, 'label' => ucfirst($cycle->cycle_status), 'step' => 'Counselling ' . $cycle->cycle_status, 'when' => (string) $cycle->status_note];
        }
        $bar ??= $cycle->statusBar();
        if (!$bar) return $tba;
        $round = $bar['round']?->name;
        $step = fn ($label) => trim(($round ? $round . ' ' : '') . ($round ? Str::lower($label) : $label));
        $items = $bar['items'] ?? [];

        // Result declared and reporting still ahead or running: the result is the news, reporting the deadline.
        $reporting = $items['Reporting'] ?? null;
        if (($items['Allotment result']['state'] ?? null) === 'declared' && $reporting && in_array($reporting['state'], ['open', 'upcoming'])) {
            return ['state' => 'declared', 'label' => 'Result out', 'step' => $step('Allotment result') . ' declared', 'when' => 'Reporting: ' . Str::lcfirst($reporting['label'])];
        }
        foreach ($items as $label => $s) {
            if ($s['state'] === 'open') return ['state' => 'open', 'label' => 'Open now', 'step' => $step($label), 'when' => $s['label']];
        }
        foreach ($items as $label => $s) {
            if (in_array($s['state'], ['upcoming', 'expected'])) return ['state' => 'upcoming', 'label' => 'Upcoming', 'step' => $step($label), 'when' => $s['label']];
        }
        foreach ($items as $label => $s) {
            if (in_array($s['state'], ['postponed', 'cancelled', 'withheld'])) return ['state' => $s['state'] === 'withheld' ? 'tba' : $s['state'], 'label' => ucfirst($s['state']), 'step' => $step($label), 'when' => $s['label']];
        }
        if (($items['Allotment result']['state'] ?? null) === 'declared') {
            return ['state' => 'declared', 'label' => 'Result out', 'step' => $step('Allotment result'), 'when' => 'Declared'];
        }
        if ($items && collect($items)->every(fn ($s) => in_array($s['state'], ['closed', 'declared']))) {
            return ['state' => 'closed', 'label' => 'Closed', 'step' => $round ?: 'Counselling ' . $cycle->title, 'when' => 'All steps closed'];
        }
        return $tba;
    }

    /** Quota names that have participating seats in this cycle (for "All India, State quota, Management"). */
    private function cycleQuotas(CounsellingCycle $cycle): array
    {
        return $this->memo['quotas'][$cycle->id] ??= Participant::where('cns_participants.cycle_id', $cycle->id)
            ->join('cns_quotas', 'cns_quotas.id', '=', 'cns_participants.quota_id')
            ->groupBy('cns_quotas.id', 'cns_quotas.name', 'cns_quotas.sort_order')->orderBy('cns_quotas.sort_order')->orderBy('cns_quotas.name')
            ->pluck('cns_quotas.name')->all();
    }

    /** Distinct colleges taking part in this cycle. */
    private function collegeCount(CounsellingCycle $cycle): int
    {
        return $this->memo['colleges'][$cycle->id] ??= (int) Participant::where('cycle_id', $cycle->id)->distinct()->count('organisation_id');
    }

    private function round(Counselling $c, CounsellingCycle $cycle, Round $r): array
    {
        $live = SeatMatrixVersion::where('round_id', $r->id)->where('is_live', true)->first();
        $versions = SeatMatrixVersion::where('round_id', $r->id)->orderByDesc('version_no')->get();
        $changes = null;
        if ($live && ($older = $versions->first(fn ($v) => $v->version_no < $live->version_no))) {
            $diff = $live->diffAgainst($older);
            $changes = ['since_version' => $older->version_no, 'added' => $diff['added']->count(), 'removed' => $diff['removed']->count(), 'changed' => $diff['changed']->count()];
        }

        return [
            'id' => $r->id, 'name' => $r->name, 'type' => $r->type?->name, 'order' => $r->sort_order, 'status' => $r->round_status, 'status_note' => $r->status_note,
            'quotas' => $r->quotas ? Quota::whereIn('id', $r->quotas)->pluck('name')->all() : null,
            'fresh_registration_allowed' => $r->effectiveFreshRegistration(), 'choices_carry_forward' => $r->choices_carry_forward,
            'willingness_options' => collect($r->effectiveWillingness())->map(fn ($w) => CounsellingCycle::WILLINGNESS[$w] ?? $w)->all(),
            'instructions' => $r->instructions, 'notice_url' => $this->safeUrl($r->notice_url), 'source_url' => $this->safeUrl($r->source_url),
            'last_verified_at' => $r->last_verified_at?->toIso8601String(),
            'process' => $this->process($cycle, $r),
            // What actually applies in this round, after the round's overrides.
            'rules' => Inheritance::effective('rules', $r)->map(fn ($i) => $this->rule($i['row']) + ['source' => $i['source']])->all(),
            'fees' => Inheritance::effective('fees', $r)->map(fn ($i) => $this->fee($i['row']) + ['source' => $i['source']])->all(),
            'extra_documents' => CnsRow::forScope('documents', $cycle->id, $r->id)->map(fn ($d) => $this->document($d))->all(),
            'seat_matrix' => [
                'live' => $live ? ['version_no' => $live->version_no, 'label' => $live->label, 'published_on' => $live->published_on?->toDateString(),
                    'source_url' => $this->safeUrl($live->source_url), 'row_count' => $live->row_count,
                    'total_seats' => (int) SeatMatrixRow::where('version_id', $live->id)->sum('seats')] : null,
                'changes_since_previous' => $changes,
                'versions' => $versions->map(fn ($v) => ['version_no' => $v->version_no, 'label' => $v->label, 'published_on' => $v->published_on?->toDateString(), 'is_live' => $v->is_live])->all(),
            ],
            'results' => AllotmentResult::where('round_id', $r->id)->orderByDesc('published_at')->get()->map(fn ($res) => [
                'title' => $res->title, 'type' => $res->result_type, 'published_at' => $res->published_at?->toIso8601String(),
                'url' => $this->safeUrl($res->url) ?? $this->file($res->file), 'source_url' => $this->safeUrl($res->source_url),
            ])->all(),
            'cutoffs' => ['count' => Cutoff::where('round_id', $r->id)->count(),
                'api_url' => route('api.v1.counselling.cutoffs', $c->slug) . '?round=' . $r->id . ($cycle->is_current ? '' : '&year=' . $cycle->year)],
        ];
    }

    private function eligibility(CounsellingCycle $cycle): array
    {
        return CnsRow::forScope('eligibility', $cycle->id)->map(function ($r) {
            $scores = DB::table('cns_eligibility_scores')->where('eligibility_id', $r->id)->get();
            return [
                'quota' => $r->quota_id ? $this->name('cns_quotas', $r->quota_id) : null,
                'qualifying_class' => CnsBlocks::CLASSES[$r->qualifying_class] ?? null, 'qualifying_subjects' => $r->qualifying_subjects ?? [],
                'nationality_allowed' => $r->nationality_allowed ?? [],
                'age' => ['min' => $r->age_min, 'max' => $r->age_max, 'as_of' => $r->age_as_of?->toDateString()],
                'domicile' => ['rule' => $r->domicile_rule, 'label' => CnsBlocks::DOMICILE_RULES[$r->domicile_rule] ?? null, 'states' => $r->domicile_states ?? [],
                    'study_years_required' => $r->study_years_required, 'notes' => $r->domicile_notes],
                'other_conditions' => $r->other_conditions,
                'minimum_scores' => $scores->map(fn ($s) => [
                    'category' => $this->name('cns_categories', $s->category_id), 'exam' => $s->exam_id ? $this->name('exm_exams', $s->exam_id) : null,
                    'min_percentile' => $s->min_percentile !== null ? (float) $s->min_percentile : null, 'min_score' => $s->min_score !== null ? (float) $s->min_score : null,
                    'max_rank' => $s->max_rank, 'min_qualifying_percent' => $s->min_qualifying_percent !== null ? (float) $s->min_qualifying_percent : null,
                ])->all(),
            ];
        })->all();
    }

    /** Process timeline; a step linked to an event type shows that event's date. A round adds its own steps after the cycle's. */
    private function process(CounsellingCycle $cycle, ?Round $round): array
    {
        $steps = $round ? Inheritance::effective('process', $round)->pluck('row') : CnsRow::forScope('process', $cycle->id);
        return $steps->map(function ($s) use ($cycle, $round) {
            $ev = $s->event_type_id
                ? CycleEvent::with('type')->where('counselling_cycle_id', $cycle->id)->where('event_type_id', $s->event_type_id)
                    ->where(fn ($q) => $q->where('round_id', $round?->id)->orWhereNull('round_id'))->orderByRaw('round_id is null')->first()
                : null;
            return ['step_no' => $s->step_no, 'title' => $s->title, 'description' => $s->description, 'link_url' => $this->safeUrl($s->link_url),
                'date' => $ev ? ['text' => $ev->dateText(), 'status' => $ev->status, 'start_at' => $ev->start_at?->toIso8601String(), 'end_at' => ($ev->extended_end_at ?? $ev->end_at)?->toIso8601String()] : null];
        })->values()->all();
    }

    private function fee($r): array
    {
        return ['fee_type' => $r->fee_type, 'fee_type_label' => CnsBlocks::FEE_TYPES[$r->fee_type] ?? $r->fee_type,
            'amount' => (float) $r->amount, 'currency' => $r->currency, 'is_refundable' => (bool) $r->is_refundable, 'note' => $r->note,
            'quota' => $r->quota_id ? $this->name('cns_quotas', $r->quota_id) : null, 'category' => $r->category_id ? $this->name('cns_categories', $r->category_id) : null,
            'institute_type' => CnsBlocks::INSTITUTE_TYPES[$r->institute_type] ?? null];
    }

    private function document($r): array
    {
        return ['name' => $this->name('cns_document_types', $r->document_type_id), 'needed_at' => CnsBlocks::DOC_STAGES[$r->stage] ?? $r->stage,
            'is_mandatory' => (bool) $r->is_mandatory, 'format_notes' => $r->format_notes,
            'quota' => $r->applies_to_quota_id ? $this->name('cns_quotas', $r->applies_to_quota_id) : null,
            'category' => $r->applies_to_category_id ? $this->name('cns_categories', $r->applies_to_category_id) : null,
            'template_url' => $this->file($r->template_file) ?? $this->safeUrl($r->template_url)];
    }

    private function rule($r): array
    {
        return ['action' => $this->name('cns_candidate_actions', $r->candidate_action_id), 'quota' => $r->quota_id ? $this->name('cns_quotas', $r->quota_id) : null,
            'institute_type' => CnsBlocks::INSTITUTE_TYPES[$r->institute_type] ?? null,
            'deposit' => ['outcome' => $r->deposit_outcome, 'label' => CnsBlocks::DEPOSIT_OUTCOMES[$r->deposit_outcome] ?? null,
                'refund_percent' => $r->refund_percent !== null ? (float) $r->refund_percent : null, 'refund_amount' => $r->refund_amount !== null ? (float) $r->refund_amount : null],
            'seat_outcome' => $r->seat_outcome, 'eligible_for_next_round' => (bool) $r->eligible_for_next_round, 'penalty_note' => $r->penalty_note,
            'source_url' => $this->safeUrl($r->source_url)];
    }

    /** @return array<int, array> */
    public function events(Collection $events): array
    {
        return $events->map(function (CycleEvent $e) {
            $s = $e->displayStatus();
            return [
                'id' => $e->id, 'type' => ['slug' => $e->type->slug, 'name' => $e->type->name], 'label' => $e->label, 'quota' => $e->quota?->name,
                'start_at' => $e->start_at?->toIso8601String(), 'end_at' => $e->end_at?->toIso8601String(), 'extended_end_at' => $e->extended_end_at?->toIso8601String(),
                'date_precision' => $e->date_precision, 'date_text' => $e->dateText(),
                'status' => $e->status, 'status_text' => $s['label'], 'link_url' => $this->safeUrl($e->link_url), 'source_url' => $this->safeUrl($e->source_url),
                'note' => $e->note, 'verified' => $e->last_verified_at !== null, 'last_verified_at' => $e->last_verified_at?->toIso8601String(),
            ];
        })->values()->all();
    }

    private function rows(Collection $rows, callable $map): array
    {
        return $rows->map($map)->values()->all();
    }

    private function mappedExams(Counselling $c): Collection
    {
        return $this->memo['exams'][$c->id] ??= $c->mappings()->with('exam')->get()->map(function ($m) {
            $exam = $m->exam;
            $public = $exam && $exam->visibility === 'public' && $exam->status;
            return ['name' => $exam?->name, 'short_name' => $exam?->short_name, 'slug' => $public ? $exam->slug : null, 'role' => $m->role,
                'valid_from_year' => $m->valid_from_year, 'valid_to_year' => $m->valid_to_year, 'seat_scope_note' => $m->seat_scope_note];
        })->filter(fn ($e) => $e['name'])->values();
    }

    // ------------------------------------------------------------------ seo + flags

    private function seo(Counselling $c, ?CounsellingCycle $cycle): array
    {
        $isCurrent = !$cycle || $cycle->is_current || $cycle->id === optional($this->cycleFor($c))->id;
        $year = $cycle?->year;
        return [
            'title' => trim($c->name . ' ' . ($year ?: '') . ' - Dates, Eligibility, Fees, Rounds & Cutoffs'),
            'description' => Str::limit(strip_tags((string) ($c->about ?: $c->name . ' counselling schedule, eligibility, fees, rounds, seat matrix and cutoffs.')), 155),
            // The current cycle is canonical at /counselling/{slug}; past years live at /counselling/{slug}/{year}.
            'canonical' => $this->frontend('counselling/' . $c->slug . ($isCurrent || !$year ? '' : '/' . $year)),
            'robots' => 'index,follow',
        ];
    }

    /** Cancelled / archived counsellings keep their page, marked rather than removed. */
    private function marked(Counselling $c, ?CounsellingCycle $cycle): ?string
    {
        if ($c->is_archived) return 'archived';
        if ($cycle && $cycle->cycle_status === 'cancelled') return 'cancelled';
        return null;
    }

    private function lastUpdated(CounsellingCycle $cycle, Collection $rounds)
    {
        $roundIds = $rounds->pluck('id');
        $stamps = collect([$cycle->updated_at, $rounds->max('updated_at'),
            CycleEvent::where('counselling_cycle_id', $cycle->id)->max('updated_at'),
            SeatMatrixVersion::whereIn('round_id', $roundIds)->max('updated_at'),
            AllotmentResult::whereIn('round_id', $roundIds)->max('updated_at')]);
        foreach (['cns_eligibility', 'cns_process_steps', 'cns_fees', 'cns_documents', 'cns_rules'] as $t) {
            $stamps->push(DB::table($t)->where('cycle_id', $cycle->id)->max('updated_at'));
        }
        $latest = $stamps->filter()->map(fn ($s) => \Carbon\Carbon::parse($s))->max();
        return $latest;
    }

    // ------------------------------------------------------------------ cutoffs + institutes (filterable)

    public function institutes(Counselling $c, CounsellingCycle $cycle, array $f): array
    {
        $perPage = max(1, min(100, (int) ($f['per_page'] ?? 30)));
        $q = Participant::query()->where('cns_participants.cycle_id', $cycle->id)
            ->join('organisations', 'organisations.id', '=', 'cns_participants.organisation_id')
            ->join('courses', 'courses.id', '=', 'cns_participants.course_id')
            ->join('cns_quotas', 'cns_quotas.id', '=', 'cns_participants.quota_id')
            ->select('cns_participants.*', 'organisations.name as org_name', 'organisations.slug as org_slug', 'organisations.logo_url as org_logo',
                'courses.name as course_name', 'courses.slug as course_slug', 'cns_quotas.name as quota_name');
        $this->applyCollegeFilters($q, $f, 'cns_participants');
        if (!empty($f['quota'])) $q->where('cns_participants.quota_id', (int) $f['quota']);
        if (!empty($f['institute_type'])) $q->where('cns_participants.institute_type', $f['institute_type']);

        $page = $q->orderBy('organisations.name')->orderBy('courses.name')->paginate($perPage)->withQueryString();
        $campus = $this->campusMap($page->getCollection()->pluck('organisation_id')->all());

        return [
            'data' => $page->getCollection()->map(fn ($p) => [
                'college' => ['id' => $p->organisation_id, 'name' => $p->org_name, 'slug' => $p->org_slug, 'logo_url' => $p->org_logo, 'city' => $campus[$p->organisation_id]['city'] ?? null, 'state' => $campus[$p->organisation_id]['state'] ?? null],
                'course' => ['id' => $p->course_id, 'name' => $p->course_name, 'slug' => $p->course_slug],
                'quota' => $p->quota_name, 'institute_type' => CnsBlocks::INSTITUTE_TYPES[$p->institute_type] ?? null, 'authority_code' => $p->institute_code,
                'annual_tuition_fee' => $p->annual_tuition_fee !== null ? (float) $p->annual_tuition_fee : null, 'other_fees' => $p->other_fees !== null ? (float) $p->other_fees : null,
                'bond_details' => $p->bond_details, 'total_seats' => $p->total_seats,
            ])->all(),
            'meta' => $this->meta($page),
            'filters' => ['quotas' => Participant::where('cycle_id', $cycle->id)->join('cns_quotas', 'cns_quotas.id', '=', 'cns_participants.quota_id')->distinct()->orderBy('cns_quotas.name')->get(['cns_quotas.id', 'cns_quotas.name'])->all(),
                'institute_types' => collect(CnsBlocks::INSTITUTE_TYPES)->map(fn ($l, $k) => ['value' => $k, 'label' => $l])->values()->all()],
        ];
    }

    /**
     * Participating colleges, one row per college (institutes?group=college): its quotas, courses, fee range, seats and,
     * for a category, the closing rank in the latest round that has cutoffs (the most generous seat at that college).
     * Filters: q, quota, institute_type, state, max_fee, category. Sort: college (default) | fee_asc | seats_desc | closing_asc.
     */
    public function colleges(Counselling $c, CounsellingCycle $cycle, array $f): array
    {
        $perPage = max(1, min(100, (int) ($f['per_page'] ?? 24)));
        $catId = !empty($f['category']) ? (int) $f['category'] : null;
        $quotaId = !empty($f['quota']) ? (int) $f['quota'] : null;
        $closingRound = $this->latestCutoffRound($cycle);

        $q = DB::table('cns_participants as p')->where('p.cycle_id', $cycle->id)
            ->join('organisations', 'organisations.id', '=', 'p.organisation_id')
            ->join('courses', 'courses.id', '=', 'p.course_id')
            ->groupBy('p.organisation_id', 'organisations.name', 'organisations.slug', 'organisations.logo_url')
            ->select('p.organisation_id', 'organisations.name as org_name', 'organisations.slug as org_slug', 'organisations.logo_url as org_logo',
                DB::raw('MIN(p.annual_tuition_fee) as min_fee'), DB::raw('MAX(p.annual_tuition_fee) as max_fee'),
                DB::raw('SUM(p.total_seats) as seats'), DB::raw('COUNT(p.total_seats) as seat_rows'),
                DB::raw('GROUP_CONCAT(DISTINCT p.quota_id) as quota_ids'), DB::raw('GROUP_CONCAT(DISTINCT p.course_id) as course_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT p.institute_type) as types'));
        if ($catId && $closingRound) {
            $q->selectSub(fn ($s) => $s->from('cns_cutoffs as co')->selectRaw('MAX(co.closing_value)')
                ->whereColumn('co.organisation_id', 'p.organisation_id')->where('co.round_id', $closingRound->id)->where('co.category_id', $catId)
                ->whereIn('co.rank_type', Cutoff::HIGHER_IS_WORSE)->whereNull('co.gender')->where('co.pwd', false)
                ->when($quotaId, fn ($s) => $s->where('co.quota_id', $quotaId)), 'closing');
        } else {
            $q->addSelect(DB::raw('NULL as closing'));
        }
        $this->applyCollegeFilters($q, $f, 'p');
        if ($quotaId) $q->where('p.quota_id', $quotaId);
        if (!empty($f['institute_type'])) $q->where('p.institute_type', $f['institute_type']);
        if (!empty($f['max_fee']) && is_numeric($f['max_fee'])) $q->where('p.annual_tuition_fee', '<=', (float) $f['max_fee']);

        match ($f['sort'] ?? 'college') {
            'fee_asc' => $q->orderByRaw('MIN(p.annual_tuition_fee) is null')->orderByRaw('MIN(p.annual_tuition_fee)'),
            'seats_desc' => $q->orderByRaw('SUM(p.total_seats) desc'),
            'closing_asc' => $catId && $closingRound ? $q->orderByRaw('closing is null')->orderBy('closing') : $q->orderBy('organisations.name'),
            default => $q->orderBy('organisations.name'),
        };
        $page = $q->orderBy('p.organisation_id')->paginate($perPage)->withQueryString();
        $rows = $page->getCollection();
        $campus = $this->campusMap($rows->pluck('organisation_id')->all());
        $courseNames = DB::table('courses')->whereIn('id', $rows->flatMap(fn ($r) => explode(',', (string) $r->course_ids))->filter()->unique()->all())->pluck('name', 'id');
        $ids = fn ($csv) => array_values(array_filter(explode(',', (string) $csv), 'strlen'));

        $orgIds = DB::table('cns_participants')->where('cycle_id', $cycle->id)->distinct()->pluck('organisation_id')->all();
        return [
            'data' => $rows->map(fn ($r) => [
                'college' => ['id' => $r->organisation_id, 'name' => $r->org_name, 'slug' => $r->org_slug, 'logo_url' => $r->org_logo,
                    'city' => $campus[$r->organisation_id]['city'] ?? null, 'state' => $campus[$r->organisation_id]['state'] ?? null],
                'quotas' => collect($ids($r->quota_ids))->map(fn ($id) => $this->name('cns_quotas', $id))->filter()->values()->all(),
                'courses' => collect($ids($r->course_ids))->map(fn ($id) => $courseNames[$id] ?? null)->filter()->values()->all(),
                'institute_types' => collect($ids($r->types))->map(fn ($t) => CnsBlocks::INSTITUTE_TYPES[$t] ?? null)->filter()->values()->all(),
                'fee' => ['min' => $r->min_fee !== null ? (float) $r->min_fee : null, 'max' => $r->max_fee !== null ? (float) $r->max_fee : null],
                'seats' => $r->seat_rows ? (int) $r->seats : null,
                'closing_value' => $r->closing !== null ? (float) $r->closing : null,
            ])->all(),
            'meta' => $this->meta($page) + ['closing_round' => $closingRound ? ['id' => $closingRound->id, 'name' => $closingRound->name] : null],
            'filters' => [
                'quotas' => array_map(fn ($n) => ['id' => $n['id'], 'name' => $n['name']], $this->cycleQuotaRows($cycle)),
                'states' => $this->statesOf($orgIds),
                'institute_types' => DB::table('cns_participants')->where('cycle_id', $cycle->id)->whereNotNull('institute_type')->distinct()->pluck('institute_type')
                    ->map(fn ($k) => ['value' => $k, 'label' => CnsBlocks::INSTITUTE_TYPES[$k] ?? $k])->values()->all(),
                'categories' => $this->cutoffCategories($cycle),
            ],
        ];
    }

    /**
     * Seat matrix explorer: the live version of one round (default: the latest round with a live version), one row per
     * college + course + quota with seats per category and the total. "changed" marks rows that differ from the
     * previous version. Filters: round, q, quota, institute_type, state, changed=1. Sort: total_desc (default) | total_asc | college.
     */
    public function seatMatrix(Counselling $c, CounsellingCycle $cycle, array $f): array
    {
        $rounds = $cycle->rounds()->get(['id', 'name']);
        $lives = SeatMatrixVersion::whereIn('round_id', $rounds->pluck('id'))->where('is_live', true)->get()->keyBy('round_id');
        $roundFilters = $rounds->filter(fn ($r) => $lives->has($r->id))->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values()->all();
        $round = !empty($f['round']) ? $rounds->firstWhere('id', (int) $f['round']) : $rounds->reverse()->first(fn ($r) => $lives->has($r->id));
        $live = $round ? $lives->get($round->id) : null;

        $empty = ['data' => [], 'categories' => [], 'meta' => ['page' => 1, 'per_page' => 0, 'total' => 0, 'last_page' => 1, 'round' => $round ? ['id' => $round->id, 'name' => $round->name] : null, 'version' => null],
            'filters' => ['rounds' => $roundFilters, 'quotas' => [], 'states' => [], 'institute_types' => []]];
        if (!$live) return $empty;

        $previous = SeatMatrixVersion::where('round_id', $round->id)->where('version_no', '<', $live->version_no)->orderByDesc('version_no')->first();
        $changedKeys = $previous ? $this->changedSeatGroups($live->id, $previous->id) : [];

        $categories = DB::table('cns_seat_matrix_rows')->where('version_id', $live->id)->join('cns_categories', 'cns_categories.id', '=', 'cns_seat_matrix_rows.category_id')
            ->groupBy('cns_categories.id', 'cns_categories.name', 'cns_categories.short_name', 'cns_categories.sort_order')->orderBy('cns_categories.sort_order')
            ->get(['cns_categories.id', 'cns_categories.name', 'cns_categories.short_name']);

        $perPage = max(1, min(100, (int) ($f['per_page'] ?? 25)));
        $q = DB::table('cns_seat_matrix_rows as r')->where('r.version_id', $live->id)
            ->join('organisations', 'organisations.id', '=', 'r.organisation_id')
            ->join('courses', 'courses.id', '=', 'r.course_id')
            ->join('cns_quotas', 'cns_quotas.id', '=', 'r.quota_id')
            ->groupBy('r.organisation_id', 'r.course_id', 'r.quota_id', 'organisations.name', 'organisations.slug', 'courses.name', 'courses.slug', 'cns_quotas.name')
            ->select('r.organisation_id', 'r.course_id', 'r.quota_id', 'organisations.name as org_name', 'organisations.slug as org_slug',
                'courses.name as course_name', 'courses.slug as course_slug', 'cns_quotas.name as quota_name', DB::raw('SUM(r.seats) as total'));
        foreach ($categories as $cat) {
            $q->addSelect(DB::raw('SUM(CASE WHEN r.category_id = ' . (int) $cat->id . ' THEN r.seats ELSE 0 END) as c_' . (int) $cat->id));
        }
        $this->applyCollegeFilters($q, $f, 'r');
        if (!empty($f['quota'])) $q->where('r.quota_id', (int) $f['quota']);
        if (!empty($f['institute_type'])) {
            $q->whereExists(fn ($e) => $e->select(DB::raw(1))->from('cns_participants as p')->where('p.cycle_id', $cycle->id)
                ->whereColumn('p.organisation_id', 'r.organisation_id')->whereColumn('p.course_id', 'r.course_id')->whereColumn('p.quota_id', 'r.quota_id')
                ->where('p.institute_type', $f['institute_type']));
        }
        if (!empty($f['changed'])) {
            if (!$changedKeys) $q->whereRaw('1 = 0');
            else $q->whereIn(DB::raw("CONCAT(r.organisation_id, '|', r.course_id, '|', r.quota_id)"), array_keys($changedKeys));
        }
        match ($f['sort'] ?? 'total_desc') {
            'total_asc' => $q->orderByRaw('SUM(r.seats) asc'),
            'college' => $q->orderBy('organisations.name')->orderBy('courses.name'),
            default => $q->orderByRaw('SUM(r.seats) desc'),
        };
        $page = $q->orderBy('organisations.name')->orderBy('cns_quotas.name')->paginate($perPage)->withQueryString();
        $rows = $page->getCollection();
        $campus = $this->campusMap($rows->pluck('organisation_id')->all());
        $types = DB::table('cns_participants')->where('cycle_id', $cycle->id)->whereIn('organisation_id', $rows->pluck('organisation_id')->unique()->all())
            ->get(['organisation_id', 'course_id', 'quota_id', 'institute_type'])->mapWithKeys(fn ($p) => ["$p->organisation_id|$p->course_id|$p->quota_id" => $p->institute_type]);

        $orgIds = DB::table('cns_seat_matrix_rows')->where('version_id', $live->id)->distinct()->pluck('organisation_id')->all();
        return [
            'data' => $rows->map(function ($r) use ($categories, $campus, $types, $changedKeys) {
                $key = "$r->organisation_id|$r->course_id|$r->quota_id";
                return [
                    'college' => ['id' => $r->organisation_id, 'name' => $r->org_name, 'slug' => $r->org_slug,
                        'city' => $campus[$r->organisation_id]['city'] ?? null, 'state' => $campus[$r->organisation_id]['state'] ?? null],
                    'course' => ['id' => $r->course_id, 'name' => $r->course_name, 'slug' => $r->course_slug],
                    'quota' => $r->quota_name, 'institute_type' => CnsBlocks::INSTITUTE_TYPES[$types[$key] ?? ''] ?? null,
                    'seats' => $categories->mapWithKeys(fn ($cat) => [$cat->id => (int) $r->{'c_' . $cat->id}])->all(),
                    'total' => (int) $r->total,
                    'changed' => isset($changedKeys[$key]),
                ];
            })->all(),
            'categories' => $categories->map(fn ($cat) => ['id' => $cat->id, 'name' => $cat->name, 'short_name' => $cat->short_name ?: $cat->name])->all(),
            'meta' => $this->meta($page) + [
                'round' => ['id' => $round->id, 'name' => $round->name],
                'version' => ['version_no' => $live->version_no, 'label' => $live->label, 'published_on' => $live->published_on?->toDateString(),
                    'source_url' => $this->safeUrl($live->source_url), 'total_seats' => (int) DB::table('cns_seat_matrix_rows')->where('version_id', $live->id)->sum('seats')],
                'previous' => $previous ? ['version_no' => $previous->version_no, 'label' => $previous->label, 'published_on' => $previous->published_on?->toDateString()] : null,
                'changed_rows' => count($changedKeys),
            ],
            'filters' => [
                'rounds' => $roundFilters,
                'quotas' => DB::table('cns_quotas')->whereIn('id', DB::table('cns_seat_matrix_rows')->where('version_id', $live->id)->distinct()->pluck('quota_id'))
                    ->orderBy('sort_order')->orderBy('name')->get(['id', 'name'])->all(),
                'states' => $this->statesOf($orgIds),
                'institute_types' => DB::table('cns_participants')->where('cycle_id', $cycle->id)->whereNotNull('institute_type')->distinct()->pluck('institute_type')
                    ->map(fn ($k) => ['value' => $k, 'label' => CnsBlocks::INSTITUTE_TYPES[$k] ?? $k])->values()->all(),
            ],
        ];
    }

    /** College + course + quota groups whose seats differ between two versions (added, removed or changed rows). */
    private function changedSeatGroups(int $liveId, int $previousId): array
    {
        $sig = function (int $versionId) {
            $out = [];
            foreach (DB::table('cns_seat_matrix_rows')->where('version_id', $versionId)->orderBy('id')
                ->get(['organisation_id', 'course_id', 'quota_id', 'category_id', 'gender', 'pwd', 'seats']) as $r) {
                $out["$r->organisation_id|$r->course_id|$r->quota_id"][$r->category_id . '|' . ($r->gender ?? '*') . '|' . (int) $r->pwd] = (int) $r->seats;
            }
            return $out;
        };
        $new = $sig($liveId);
        $old = $sig($previousId);
        $changed = [];
        foreach ($new as $k => $cells) {
            if (!isset($old[$k])) { $changed[$k] = true; continue; }
            ksort($cells);
            $o = $old[$k];
            ksort($o);
            if ($cells !== $o) $changed[$k] = true;
        }
        return $changed;
    }

    /** The last round (in order) that has any cutoff: its closing ranks are the ones to show next to a college. */
    public function latestCutoffRound(CounsellingCycle $cycle): ?Round
    {
        return $this->memo['cutoffRound'][$cycle->id] ??= $cycle->rounds()->get()->reverse()
            ->first(fn ($r) => DB::table('cns_cutoffs')->where('round_id', $r->id)->exists());
    }

    /** Categories that have cutoffs in this cycle (ids + names), in master order. */
    public function cutoffCategories(CounsellingCycle $cycle): array
    {
        $roundIds = $cycle->rounds()->pluck('id');
        return DB::table('cns_categories')->whereIn('id', DB::table('cns_cutoffs')->whereIn('round_id', $roundIds)->distinct()->pluck('category_id'))
            ->orderBy('sort_order')->get(['id', 'name', 'short_name'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'short_name' => $c->short_name ?: $c->name])->all();
    }

    private function cycleQuotaRows(CounsellingCycle $cycle): array
    {
        return DB::table('cns_participants')->where('cns_participants.cycle_id', $cycle->id)
            ->join('cns_quotas', 'cns_quotas.id', '=', 'cns_participants.quota_id')
            ->groupBy('cns_quotas.id', 'cns_quotas.name', 'cns_quotas.sort_order')->orderBy('cns_quotas.sort_order')->orderBy('cns_quotas.name')
            ->get(['cns_quotas.id', 'cns_quotas.name'])->map(fn ($q) => ['id' => $q->id, 'name' => $q->name])->all();
    }

    /** States where these colleges have a campus (the "State" filter). */
    private function statesOf(array $orgIds): array
    {
        if (!$orgIds) return [];
        return DB::table('campuses')->whereIn('organisation_id', array_unique($orgIds))->whereNull('deleted_at')->whereNotNull('state')->where('state', '!=', '')
            ->distinct()->orderBy('state')->pluck('state')->all();
    }

    public function cutoffs(Counselling $c, CounsellingCycle $cycle, array $f): array
    {
        $roundIds = $cycle->rounds()->pluck('id');
        $perPage = max(1, min(100, (int) ($f['per_page'] ?? 30)));
        $q = Cutoff::query()->whereIn('cns_cutoffs.round_id', $roundIds)
            ->join('organisations', 'organisations.id', '=', 'cns_cutoffs.organisation_id')
            ->join('courses', 'courses.id', '=', 'cns_cutoffs.course_id')
            ->join('cns_quotas', 'cns_quotas.id', '=', 'cns_cutoffs.quota_id')
            ->join('cns_categories', 'cns_categories.id', '=', 'cns_cutoffs.category_id')
            ->join('exm_exams', 'exm_exams.id', '=', 'cns_cutoffs.exam_id')
            ->join('cns_rounds', 'cns_rounds.id', '=', 'cns_cutoffs.round_id')
            ->leftJoin('cns_participants', fn ($j) => $j->on('cns_participants.organisation_id', '=', 'cns_cutoffs.organisation_id')
                ->on('cns_participants.course_id', '=', 'cns_cutoffs.course_id')->on('cns_participants.quota_id', '=', 'cns_cutoffs.quota_id')->where('cns_participants.cycle_id', $cycle->id))
            ->select('cns_cutoffs.*', 'organisations.name as org_name', 'organisations.slug as org_slug', 'courses.name as course_name', 'courses.slug as course_slug',
                'cns_quotas.name as quota_name', 'cns_categories.name as category_name', 'exm_exams.name as exam_name', 'cns_rounds.name as round_name', 'cns_participants.institute_type',
                'cns_participants.annual_tuition_fee');

        if (!empty($f['round'])) $q->where('cns_cutoffs.round_id', (int) $f['round']);
        if (!empty($f['exam'])) $q->where(fn ($w) => is_numeric($f['exam']) ? $w->where('cns_cutoffs.exam_id', (int) $f['exam']) : $w->where('exm_exams.slug', $f['exam']));
        if (!empty($f['quota'])) $q->where('cns_cutoffs.quota_id', (int) $f['quota']);
        if (!empty($f['category'])) $q->where('cns_cutoffs.category_id', (int) $f['category']);
        if (!empty($f['course'])) $q->where('cns_cutoffs.course_id', (int) $f['course']);
        if (!empty($f['rank_type'])) $q->where('cns_cutoffs.rank_type', $f['rank_type']);
        if (!empty($f['institute_type'])) $q->where('cns_participants.institute_type', $f['institute_type']);
        if (($f['gender'] ?? '') === 'neutral') $q->whereNull('cns_cutoffs.gender');
        elseif (in_array($f['gender'] ?? '', ['male', 'female'])) $q->where('cns_cutoffs.gender', $f['gender']);
        if (isset($f['pwd']) && $f['pwd'] !== '') $q->where('cns_cutoffs.pwd', filter_var($f['pwd'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        $this->applyCollegeFilters($q, $f, 'cns_cutoffs');

        // "Your rank": rank-type rows (AIR, category rank, state merit rank) whose closing rank is at or above it.
        $rank = isset($f['rank']) && is_numeric($f['rank']) && $f['rank'] > 0 ? (float) $f['rank'] : null;
        if ($rank && !empty($f['within'])) {
            $q->whereIn('cns_cutoffs.rank_type', Cutoff::HIGHER_IS_WORSE)->where('cns_cutoffs.closing_value', '>=', $rank);
        }
        if (!empty($f['max_fee']) && is_numeric($f['max_fee'])) $q->where('cns_participants.annual_tuition_fee', '<=', (float) $f['max_fee']);

        match ($f['sort'] ?? 'closing_asc') {
            'closing_desc' => $q->orderByDesc('cns_cutoffs.closing_value'),
            'opening_asc' => $q->orderBy('cns_cutoffs.opening_value'),
            'fee_asc' => $q->orderByRaw('cns_participants.annual_tuition_fee is null')->orderBy('cns_participants.annual_tuition_fee'),
            'college' => $q->orderBy('organisations.name')->orderBy('courses.name'),
            // Best for my rank: seats that closed at or above the rank first (hardest first), then the rest.
            'fit' => $rank ? $q->orderByRaw('(cns_cutoffs.closing_value < ?) asc', [$rank])->orderBy('cns_cutoffs.closing_value') : $q->orderBy('cns_cutoffs.closing_value'),
            default => $q->orderBy('cns_cutoffs.closing_value'),
        };
        $page = $q->orderBy('cns_cutoffs.id')->paginate($perPage)->withQueryString();
        $campus = $this->campusMap($page->getCollection()->pluck('organisation_id')->all());

        $ranks = DB::table('cns_cutoffs')->whereIn('round_id', $roundIds);
        return [
            'data' => $page->getCollection()->map(fn ($r) => [
                'college' => ['id' => $r->organisation_id, 'name' => $r->org_name, 'slug' => $r->org_slug, 'city' => $campus[$r->organisation_id]['city'] ?? null, 'state' => $campus[$r->organisation_id]['state'] ?? null],
                'course' => ['id' => $r->course_id, 'name' => $r->course_name, 'slug' => $r->course_slug],
                'round' => ['id' => $r->round_id, 'name' => $r->round_name], 'exam' => $r->exam_name, 'quota' => $r->quota_name, 'category' => $r->category_name,
                'gender' => $r->gender ?: 'neutral', 'pwd' => (bool) $r->pwd, 'institute_type' => CnsBlocks::INSTITUTE_TYPES[$r->institute_type] ?? null,
                'rank_type' => $r->rank_type, 'rank_type_label' => Cutoff::RANK_TYPES[$r->rank_type] ?? $r->rank_type,
                'higher_is_worse' => in_array($r->rank_type, Cutoff::HIGHER_IS_WORSE, true),
                'opening_value' => $r->opening_value !== null ? (float) $r->opening_value : null, 'closing_value' => $r->closing_value !== null ? (float) $r->closing_value : null,
                'annual_tuition_fee' => $r->annual_tuition_fee !== null ? (float) $r->annual_tuition_fee : null,
            ])->all(),
            'meta' => $this->meta($page) + ['cycle' => ['year' => $cycle->year, 'session_label' => $cycle->session_label]],
            'filters' => [
                'rounds' => $cycle->rounds()->get(['id', 'name'])->map(fn ($r) => ['id' => $r->id, 'name' => $r->name,
                    'cutoffs' => (int) DB::table('cns_cutoffs')->where('round_id', $r->id)->count()])->all(),
                'states' => $this->statesOf((clone $ranks)->distinct()->pluck('organisation_id')->all()),
                'exams' => DB::table('exm_exams')->whereIn('id', (clone $ranks)->distinct()->pluck('exam_id'))->get(['id', 'name', 'slug'])->all(),
                'quotas' => DB::table('cns_quotas')->whereIn('id', (clone $ranks)->distinct()->pluck('quota_id'))->orderBy('sort_order')->get(['id', 'name'])->all(),
                'categories' => DB::table('cns_categories')->whereIn('id', (clone $ranks)->distinct()->pluck('category_id'))->orderBy('sort_order')->get(['id', 'name'])->all(),
                'rank_types' => collect(Cutoff::RANK_TYPES)->map(fn ($l, $k) => ['value' => $k, 'label' => $l])->values()->all(),
                'institute_types' => collect(CnsBlocks::INSTITUTE_TYPES)->map(fn ($l, $k) => ['value' => $k, 'label' => $l])->values()->all(),
            ],
        ];
    }

    private function applyCollegeFilters($q, array $f, string $table): void
    {
        if (!empty($f['q'])) {
            $s = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($f['q'])) . '%';
            $q->where(fn ($w) => $w->where('organisations.name', 'like', $s)->orWhere('courses.name', 'like', $s));
        }
        if (!empty($f['state'])) {
            $q->whereExists(fn ($e) => $e->select(DB::raw(1))->from('campuses')->whereColumn('campuses.organisation_id', "$table.organisation_id")->where('campuses.state', $f['state']));
        }
    }

    private function campusMap(array $orgIds): array
    {
        if (!$orgIds) return [];
        return DB::table('campuses')->whereIn('organisation_id', array_unique($orgIds))->orderBy('id')->get(['organisation_id', 'city', 'state'])
            ->unique('organisation_id')->mapWithKeys(fn ($c) => [$c->organisation_id => ['city' => $c->city, 'state' => $c->state]])->all();
    }

    private function meta($page): array
    {
        return ['page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()];
    }

    // ------------------------------------------------------------------ helpers

    private function names(string $kind, ?array $ids): array
    {
        if (!$ids) return [];
        $table = $kind === 'program_levels' ? 'program_levels' : 'exam_categories';
        return DB::table($table)->whereIn('id', $ids)->pluck('title')->all();
    }

    private function name(string $table, $id): ?string
    {
        $this->memo['n'][$table] ??= DB::table($table)->pluck('name', 'id')->all();
        return $this->memo['n'][$table][$id] ?? null;
    }

    public function file(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }

    /** Only absolute external http(s) links are ever returned. */
    public function safeUrl(?string $url): ?string
    {
        return CycleEvent::safeUrl($url);
    }

    public function frontend(string $path): string
    {
        return config('cns.frontend_url') . '/' . ltrim($path, '/');
    }
}
