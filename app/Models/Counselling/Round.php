<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\ExamModule\CycleEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Round extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'cns_rounds';

    protected $fillable = [
        'cycle_id', 'name', 'round_type_id', 'sort_order', 'quotas', 'round_status', 'status_note',
        'fresh_registration_allowed', 'choices_carry_forward', 'willingness_options', 'instructions',
        'notice_url', 'source_url', 'last_verified_at', 'verified_by',
    ];

    protected $casts = [
        'quotas' => 'array', 'willingness_options' => 'array', 'last_verified_at' => 'datetime',
        'fresh_registration_allowed' => 'boolean', 'choices_carry_forward' => 'boolean',
    ];

    public function cycle()
    {
        return $this->belongsTo(CounsellingCycle::class, 'cycle_id');
    }

    public function type()
    {
        return $this->belongsTo(RoundType::class, 'round_type_id');
    }

    public function events()
    {
        return $this->hasMany(CycleEvent::class, 'round_id');
    }

    public function results()
    {
        return $this->hasMany(AllotmentResult::class, 'round_id')->orderByDesc('published_at');
    }

    public function seatMatrixVersions()
    {
        return $this->hasMany(SeatMatrixVersion::class, 'round_id')->orderByDesc('version_no');
    }

    public function liveSeatMatrix()
    {
        return $this->hasOne(SeatMatrixVersion::class, 'round_id')->where('is_live', true);
    }

    public function cutoffs()
    {
        return $this->hasMany(Cutoff::class, 'round_id');
    }

    // ---- Inheritance: a round stores only what differs from its cycle / round type ----

    public function effectiveFreshRegistration(): bool
    {
        return $this->fresh_registration_allowed ?? (bool) $this->type?->is_fresh_registration_default;
    }

    public function effectiveWillingness(): array
    {
        return $this->willingness_options ?? $this->cycle?->willingness_options ?? [];
    }

    /** This round's choice filling window when it is open right now (round dates first, then the cycle's own), else null. */
    public function openChoiceFilling(): ?\App\Models\ExamModule\CycleEvent
    {
        $isOpen = fn ($e) => $e->type?->slug === 'choice-filling' && $e->status === 'open';
        return $this->events()->with('type')->get()->first($isOpen)
            ?? $this->cycle?->events()->with('type')->get()->first($isOpen);
    }

    /** Previous and next round by sort order, for the round page navigation. */
    public function previous(): ?self
    {
        return static::where('cycle_id', $this->cycle_id)
            ->where(fn ($q) => $q->where('sort_order', '<', $this->sort_order)->orWhere(fn ($q) => $q->where('sort_order', $this->sort_order)->where('id', '<', $this->id)))
            ->orderByDesc('sort_order')->orderByDesc('id')->first();
    }

    public function next(): ?self
    {
        return static::where('cycle_id', $this->cycle_id)
            ->where(fn ($q) => $q->where('sort_order', '>', $this->sort_order)->orWhere(fn ($q) => $q->where('sort_order', $this->sort_order)->where('id', '>', $this->id)))
            ->orderBy('sort_order')->orderBy('id')->first();
    }
}
