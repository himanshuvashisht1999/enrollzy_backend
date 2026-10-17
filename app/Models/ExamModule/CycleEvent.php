<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CycleEvent extends Model
{
    use Auditable;

    protected $table = 'exm_cycle_events';

    protected $fillable = [
        'cycle_id', 'stage_id', 'event_type_id', 'label', 'start_at', 'end_at', 'extended_end_at',
        'date_precision', 'manual_status', 'on_demand_text', 'link_url', 'note', 'source_url',
        'last_verified_at', 'verified_by',
    ];

    protected $casts = ['last_verified_at' => 'datetime'];

    public const PRECISIONS = ['exact' => 'Exact', 'month_only' => 'Month only', 'tentative' => 'Tentative', 'tba' => 'To be announced'];
    public const MANUAL_STATUSES = ['not_announced' => 'Not announced', 'expected' => 'Expected', 'declared' => 'Declared',
        'postponed' => 'Postponed', 'withheld' => 'Withheld', 'cancelled' => 'Cancelled'];

    /** Dates are stored in UTC and read/written in the cycle's timezone. */
    private function tzDate(): Attribute
    {
        return Attribute::make(
            get: fn ($v) => $v ? Carbon::parse($v, 'UTC')->setTimezone($this->cycleTimezone()) : null,
            set: fn ($v) => blank($v) ? null
                : ($v instanceof Carbon ? $v->copy() : Carbon::parse($v, $this->cycleTimezone()))->utc()->format('Y-m-d H:i:s'),
        );
    }

    public function startAt(): Attribute { return $this->tzDate(); }
    public function endAt(): Attribute { return $this->tzDate(); }
    public function extendedEndAt(): Attribute { return $this->tzDate(); }

    private function cycleTimezone(): string
    {
        return ($this->cycle_id ? $this->cycle?->timezone : null) ?: 'Asia/Kolkata';
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function type()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    /** Calculated for date-based types; stored for manual types (spec section 3). */
    public function getStatusAttribute(): string
    {
        if ($this->type?->isManual()) {
            return $this->manual_status ?? 'not_announced';
        }
        if ($this->date_precision === 'tba' || !$this->start_at) {
            return 'to_be_announced';
        }
        $now = now();
        $end = $this->extended_end_at ?? $this->end_at;

        if ($now->lt($this->start_at)) return 'upcoming';
        if ($end && $now->lte($end)) return 'open';
        if (!$end && $now->isSameDay($this->start_at)) return 'open';
        return 'closed';
    }

    public function displayStatus(): array
    {
        $s = $this->status;
        $end = $this->extended_end_at ?? $this->end_at;

        if ($this->type?->isManual()) {
            $label = match ($s) {
                'declared' => 'Declared',
                'expected' => $this->start_at
                    ? 'Expected ' . $this->start_at->format($this->date_precision === 'month_only' ? 'F Y' : 'd M Y')
                    : 'Expected',
                'postponed' => 'Postponed',
                'withheld' => 'Withheld',
                'cancelled' => 'Cancelled',
                default => 'Not announced',
            };
            return ['state' => $s, 'label' => $label];
        }
        if ($this->date_precision === 'month_only' && $this->start_at) {
            return ['state' => 'expected', 'label' => 'Expected ' . $this->start_at->format('F Y')];
        }
        return match ($s) {
            'to_be_announced' => ['state' => $s, 'label' => 'To be announced'],
            'upcoming' => ['state' => $s, 'label' => 'Starts on ' . $this->start_at->format('d M Y')],
            'open' => ['state' => $s, 'label' => $end ? 'Open, closes ' . $end->format('d M Y') : 'Open today'],
            default => ['state' => 'closed', 'label' => 'Closed'],
        };
    }

    /** Date text honouring precision, for admin and public display. */
    public function dateText(): string
    {
        if ($this->on_demand_text) return $this->on_demand_text;
        if ($this->date_precision === 'tba' || !$this->start_at) return 'To be announced';
        if ($this->date_precision === 'month_only') return $this->start_at->format('F Y');
        $s = $this->start_at->format('d M Y, h:i A');
        $end = $this->extended_end_at ?? $this->end_at;
        $out = $end && !$end->equalTo($this->start_at) ? $s . ' - ' . $end->format('d M Y, h:i A') : $s;
        return $this->date_precision === 'tentative' ? $out . ' (tentative)' : $out;
    }
}
