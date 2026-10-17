<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cycle extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'exm_cycles';

    protected $fillable = [
        'exam_id', 'year', 'session_label', 'registration_mode', 'result_mode', 'cycle_status',
        'status_note', 'timezone', 'is_current', 'is_public',
    ];

    protected $casts = ['is_current' => 'boolean', 'is_public' => 'boolean', 'year' => 'integer'];

    public const REGISTRATION_MODES = ['window' => 'Window (open/close dates)', 'rolling' => 'Rolling (book anytime)',
        'via_institution' => 'Via school/college', 'not_applicable' => 'Not applicable'];
    public const RESULT_MODES = ['fixed_date' => 'Fixed date', 'on_demand' => 'On demand'];
    public const STATUSES = ['scheduled' => 'Scheduled', 'postponed' => 'Postponed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];

    protected static function booted(): void
    {
        // Only one current cycle per exam.
        static::saved(function (Cycle $cycle) {
            if ($cycle->is_current) {
                static::where('exam_id', $cycle->exam_id)->where('id', '!=', $cycle->id)
                    ->where('is_current', true)->update(['is_current' => false]);
            }
        });
    }

    public function getTitleAttribute(): string
    {
        return trim($this->year . ' ' . $this->session_label);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function stages()
    {
        return $this->hasMany(Stage::class, 'cycle_id')->orderBy('sort_order');
    }

    public function events()
    {
        return $this->hasMany(CycleEvent::class, 'cycle_id');
    }

    /**
     * Student-facing status for one event type (spec section 3, rules 1-5).
     * Returns ['state' => string, 'label' => string].
     */
    public function statusForType(string $slug): array
    {
        if (in_array($this->cycle_status, ['postponed', 'cancelled'])) {
            return ['state' => $this->cycle_status, 'label' => ucfirst($this->cycle_status) . ($this->status_note ? ': ' . $this->status_note : '')];
        }
        if ($slug === 'registration') {
            if ($this->registration_mode === 'rolling') return ['state' => 'open', 'label' => 'Open, book anytime'];
            if ($this->registration_mode === 'via_institution') return ['state' => 'info', 'label' => 'Through your school/college'];
            if ($this->registration_mode === 'not_applicable') return ['state' => 'hidden', 'label' => ''];
        }

        $events = $this->events()->with('type')->get()
            ->filter(fn ($e) => $e->type && $e->type->slug === $slug);
        if ($events->isEmpty()) {
            return ['state' => 'to_be_announced', 'label' => 'To be announced'];
        }
        $pick = $events->first(fn ($e) => $e->status === 'open')
            ?? $events->filter(fn ($e) => $e->status === 'upcoming')->sortBy('start_at')->first()
            ?? $events->sortByDesc('start_at')->first();

        return $pick->displayStatus();
    }

    /** Copy stages and the event list (without dates) into a new year. */
    public function copyForward(int $year, ?string $sessionLabel = null): self
    {
        return DB::transaction(function () use ($year, $sessionLabel) {
            $new = $this->replicate(['is_current', 'is_public']);
            $new->fill(['year' => $year, 'session_label' => $sessionLabel, 'cycle_status' => 'scheduled',
                'status_note' => null, 'is_current' => false, 'is_public' => false])->save();

            $map = [];
            foreach ($this->stages as $stage) {
                $map[$stage->id] = $new->stages()->create($stage->only(['name', 'sort_order', 'is_qualifying']))->id;
            }
            foreach ($this->events()->with('type')->get() as $event) {
                $new->events()->create([
                    'stage_id' => $event->stage_id ? ($map[$event->stage_id] ?? null) : null,
                    'event_type_id' => $event->event_type_id,
                    'label' => $event->label,
                    'date_precision' => 'tba',
                    'manual_status' => $event->type?->isManual() ? 'not_announced' : null,
                ]);
            }
            return $new;
        });
    }
}
