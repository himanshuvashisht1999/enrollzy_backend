<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\ExamModule\CycleEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One year of a counselling. Cycle-level events have round_id null; round events carry round_id. */
class CounsellingCycle extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'cns_cycles';

    protected $fillable = [
        'counselling_id', 'year', 'session_label', 'cycle_status', 'status_note', 'timezone', 'is_current', 'is_public',
        'information_bulletin_pdf', 'information_bulletin_url', 'reservation_policy', 'seat_allotment_method',
        'willingness_options', 'source_url', 'last_verified_at', 'verified_by',
    ];

    protected $casts = [
        'is_current' => 'boolean', 'is_public' => 'boolean', 'year' => 'integer',
        'willingness_options' => 'array', 'last_verified_at' => 'datetime',
    ];

    public const STATUSES = ['scheduled' => 'Scheduled', 'postponed' => 'Postponed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];
    public const WILLINGNESS = ['freeze' => 'Freeze', 'float' => 'Float', 'slide' => 'Slide', 'upgrade' => 'Upgrade', 'retain' => 'Retain'];

    /** Slugs shown in the "current round" status bar. */
    public const STATUS_BAR = ['registration' => 'Registration', 'choice-filling' => 'Choice filling',
        'allotment-result' => 'Allotment result', 'reporting-joining' => 'Reporting'];

    protected static function booted(): void
    {
        // Only one current cycle per counselling.
        static::saved(function (CounsellingCycle $cycle) {
            if ($cycle->is_current) {
                static::where('counselling_id', $cycle->counselling_id)->where('id', '!=', $cycle->id)
                    ->where('is_current', true)->update(['is_current' => false]);
            }
        });
    }

    public function getTitleAttribute(): string
    {
        return trim($this->year . ' ' . $this->session_label);
    }

    public function counselling()
    {
        return $this->belongsTo(Counselling::class, 'counselling_id');
    }

    public function rounds()
    {
        return $this->hasMany(Round::class, 'cycle_id')->orderBy('sort_order')->orderBy('id');
    }

    /** Cycle-level events only (registration, fee payment, merit list...). */
    public function events()
    {
        return $this->hasMany(CycleEvent::class, 'counselling_cycle_id')->whereNull('round_id');
    }

    /** Cycle-level and every round's events. */
    public function allEvents()
    {
        return $this->hasMany(CycleEvent::class, 'counselling_cycle_id');
    }

    public function examCycles()
    {
        return $this->belongsToMany(\App\Models\ExamModule\Cycle::class, 'cns_cycle_exam_cycles', 'cycle_id', 'exam_cycle_id');
    }

    /**
     * Copy Cycle (spec section 14). Copies eligibility (with score rows), process steps, fees, documents (and
     * their templates), rules, rounds with their overrides, the event list WITHOUT dates and, when asked, the
     * participating institutes. Does not copy the bulletin, verification stamps, seat matrix, results or cutoffs.
     * Date-type fields inside copied content (Eligibility "age as of") are cleared; needsNewDates() lists them.
     * Each linked exam cycle moves on to that exam's next cycle; when there is none yet the link is left empty and
     * needsUpdate() flags it.
     */
    public function copyForward(int $year, ?string $sessionLabel = null, bool $withParticipants = false): self
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($year, $sessionLabel, $withParticipants) {
            $new = $this->replicate(['is_current', 'is_public', 'information_bulletin_pdf', 'information_bulletin_url', 'last_verified_at', 'verified_by', 'source_url']);
            $new->fill(['year' => $year, 'session_label' => $sessionLabel, 'cycle_status' => 'scheduled', 'status_note' => null,
                'is_current' => false, 'is_public' => false])->save();

            // Rounds keep their names, types and flags; their status, notices and verification start fresh.
            $roundMap = [];
            foreach ($this->rounds as $round) {
                $copy = $round->replicate(['round_status', 'status_note', 'notice_url', 'source_url', 'last_verified_at', 'verified_by']);
                $copy->fill(['cycle_id' => $new->id, 'round_status' => 'scheduled'])->save();
                $roundMap[$round->id] = $copy->id;
            }

            foreach (\App\Support\Counselling\CnsBlocks::all() as $key => $def) {
                $files = \App\Support\Counselling\CnsBlocks::fileFields($def);
                $childFields = array_filter($def['fields'], fn ($f) => $f['type'] === 'children');

                foreach (CnsRow::block($key)->newQuery()->where('cycle_id', $this->id)->get() as $row) {
                    $copy = $row->replicate();
                    $copy->cycle_id = $new->id;
                    if (in_array('round', $def['scopes'])) {
                        $copy->round_id = $row->round_id ? ($roundMap[$row->round_id] ?? null) : null;
                    }
                    foreach ($files as $f) {
                        $copy->$f = $this->copyFile($row->$f);
                    }
                    if ($key === 'eligibility') {
                        $copy->age_as_of = null;   // a date: must be entered again for the new year
                    }
                    $copy->save();

                    foreach ($childFields as $f) {
                        foreach (\Illuminate\Support\Facades\DB::table($f['table'])->where($f['fk'], $row->id)->get() as $child) {
                            $attrs = (array) $child;
                            unset($attrs['id']);
                            $attrs[$f['fk']] = $copy->id;
                            \Illuminate\Support\Facades\DB::table($f['table'])->insert($attrs);
                        }
                    }
                }
            }

            // The event list, without dates, links or verification.
            foreach ($this->allEvents()->with('type')->get() as $event) {
                $new->allEvents()->create([
                    'round_id' => $event->round_id ? ($roundMap[$event->round_id] ?? null) : null,
                    'quota_id' => $event->quota_id,
                    'event_type_id' => $event->event_type_id,
                    'label' => $event->label,
                    'date_precision' => 'tba',
                    'manual_status' => $event->type?->isManual() ? 'not_announced' : null,
                ]);
            }

            $new->examCycles()->sync($this->nextExamCycleIds($year));

            // Participating institutes (optional): same colleges, courses and quotas; the import link is not carried over.
            if ($withParticipants) {
                $now = now();
                Participant::where('cycle_id', $this->id)->orderBy('id')->chunk(500, function ($chunk) use ($new, $now) {
                    \Illuminate\Support\Facades\DB::table('cns_participants')->insert($chunk->map(fn ($p) => [
                        'cycle_id' => $new->id, 'organisation_id' => $p->organisation_id, 'course_id' => $p->course_id, 'quota_id' => $p->quota_id,
                        'institute_type' => $p->institute_type, 'institute_code' => $p->institute_code, 'annual_tuition_fee' => $p->annual_tuition_fee,
                        'other_fees' => $p->other_fees, 'bond_details' => $p->bond_details, 'total_seats' => $p->total_seats,
                        'import_batch_id' => null, 'created_at' => $now, 'updated_at' => $now,
                    ])->all());
                });
            }

            return $new;
        });
    }

    /**
     * Exam cycles for a copy made for $year: each exam cycle linked to this cycle moves on to the same exam's next
     * cycle (the newest one after it, up to $year). An exam that was not linked gets its cycle for $year, if any.
     * Nothing is linked when the exam has no such cycle yet: needsUpdate() then flags it on the new cycle.
     */
    public function nextExamCycleIds(int $year): array
    {
        $linked = $this->examCycles()->get();
        $ids = [];
        foreach ($this->mappedExamIdsFor($year) as $examId) {
            $prev = $linked->where('exam_id', $examId)->sortByDesc('year')->first();
            $q = \App\Models\ExamModule\Cycle::where('exam_id', $examId)->where('year', '<=', $year);
            $q = $prev ? $q->where('year', '>', $prev->year) : $q->where('year', $year);
            $next = $q->orderByDesc('year')->orderByDesc('is_current')->orderBy('id')->first();
            if ($next) $ids[] = $next->id;
        }
        return $ids;
    }

    /** Exams mapped to the counselling whose acceptance window (valid from / to year) covers $year. */
    public function mappedExamIdsFor(int $year): array
    {
        return $this->counselling->mappings()
            ->where(fn ($q) => $q->whereNull('valid_from_year')->orWhere('valid_from_year', '<=', $year))
            ->where(fn ($q) => $q->whereNull('valid_to_year')->orWhere('valid_to_year', '>=', $year))
            ->whereHas('exam')->pluck('exam_id')->all();
    }

    /**
     * Links that need an update (shown as "Needs update"): a mapped exam with no exam cycle linked, or one linked to an
     * older exam cycle than this counselling year (for example a 2028 counselling still on the 2027 exam).
     */
    public function needsUpdate(): array
    {
        $linked = $this->examCycles()->with('exam:id,name')->get();
        $out = [];
        foreach (\App\Models\ExamModule\Exam::whereIn('id', $this->mappedExamIdsFor((int) $this->year))->orderBy('name')->get(['id', 'name']) as $exam) {
            $mine = $linked->where('exam_id', $exam->id);
            if ($mine->isEmpty()) {
                $out[] = 'Exam cycle for ' . $exam->name . ': none linked (no ' . $this->year . ' cycle of the exam yet). Link it once the exam cycle exists.';
            } elseif ($mine->max('year') < $this->year) {
                $out[] = 'Exam cycle for ' . $exam->name . ': still linked to the ' . $mine->max('year') . ' exam cycle. Link the ' . $this->year . ' one.';
            }
        }
        return $out;
    }

    /** Date fields cleared by Copy Cycle that still need a value (shown as "Needs a new date"). */
    public function needsNewDates(): array
    {
        return CnsRow::block('eligibility')->newQuery()->where('cycle_id', $this->id)
            ->whereNull('age_as_of')->where(fn ($q) => $q->whereNotNull('age_min')->orWhereNotNull('age_max'))->get()
            ->map(fn ($r) => 'Eligibility' . ($r->quota_id ? ' (' . (Quota::find($r->quota_id)->name ?? 'quota') . ')' : '') . ': "age as of" date')->all();
    }

    private function copyFile(?string $path): ?string
    {
        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return null;
        }
        $target = dirname($path) . '/' . \Illuminate\Support\Str::random(20) . '_' . basename($path);
        \Illuminate\Support\Facades\Storage::disk('public')->copy($path, $target);
        return $target;
    }

    /**
     * Student-facing status for one event type (spec section 13). Override order:
     * cycle postponed/cancelled > round postponed/cancelled > calculated event status.
     * With a round, that round's events are used; if it has none for the type, cycle-level events are.
     */
    public function statusForType(string $slug, ?Round $round = null): array
    {
        if (in_array($this->cycle_status, ['postponed', 'cancelled'])) {
            return ['state' => $this->cycle_status, 'label' => ucfirst($this->cycle_status) . ($this->status_note ? ': ' . $this->status_note : '')];
        }
        if ($round && in_array($round->round_status, ['postponed', 'cancelled'])) {
            return ['state' => $round->round_status, 'label' => ucfirst($round->round_status) . ($round->status_note ? ': ' . $round->status_note : '')];
        }

        $of = fn ($events) => $events->filter(fn ($e) => $e->type && $e->type->slug === $slug);
        $events = $of($round ? $round->events()->with('type')->get() : collect());
        if ($events->isEmpty()) {
            $events = $of($this->events()->with('type')->get());
        }
        if ($events->isEmpty()) {
            return ['state' => 'to_be_announced', 'label' => 'To be announced'];
        }

        $pick = $events->first(fn ($e) => $e->status === 'open')
            ?? $events->filter(fn ($e) => $e->status === 'upcoming')->sortBy('start_at')->first()
            ?? $events->sortByDesc('start_at')->first();

        return $pick->displayStatus();
    }

    /**
     * The round students should look at now: the first round with something open, else the first
     * with something upcoming, else the last finished round. Null means no schedule yet.
     */
    public function currentRound(): ?Round
    {
        $rounds = $this->rounds()->with('events.type')->get();
        $rounds = $rounds->filter(fn ($r) => !in_array($r->round_status, ['cancelled']));

        return $rounds->first(fn ($r) => $r->events->contains(fn ($e) => $e->status === 'open'))
            ?? $rounds->first(fn ($r) => $r->events->contains(fn ($e) => $e->status === 'upcoming'))
            ?? $rounds->last(fn ($r) => $r->round_status === 'completed'
                || $r->events->contains(fn ($e) => $e->type?->slug === 'allotment-result' && $e->status === 'declared'));
    }

    /**
     * Status bar for the current round: ['round' => Round|null, 'items' => [label => status]], or null when nothing is scheduled.
     * With no round to show (none added yet, or none with dates), the cycle-level dates (registration, payment...) are
     * shown on their own, so an open registration is never hidden behind "schedule to be announced".
     */
    public function statusBar(): ?array
    {
        $round = $this->currentRound();
        if (!$round) {
            $items = [];
            foreach (self::STATUS_BAR as $slug => $label) {
                $items[$label] = $this->statusForType($slug, null);
            }
            $known = array_filter($items, fn ($s) => $s['state'] !== 'to_be_announced');
            return $known ? ['round' => null, 'items' => $known] : null;
        }
        $bar = ['round' => $round];
        foreach (self::STATUS_BAR as $slug => $label) {
            $bar['items'][$label] = $this->statusForType($slug, $round);
        }
        return $bar;
    }
}
