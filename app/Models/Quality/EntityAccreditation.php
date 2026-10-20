<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\Quality\Concerns\HasVerification;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A grade / accreditation / recognition / affiliation held by one entity, with its validity period. */
class EntityAccreditation extends Model
{
    use Auditable, HasVerification, SoftDeletes;

    protected $table = 'entity_accreditations';
    protected $guarded = ['id'];
    protected $casts = ['valid_from' => 'date', 'valid_to' => 'date', 'verified_at' => 'datetime', 'score' => 'float'];

    public const STATUSES = ['active' => 'Active', 'expired' => 'Expired', 'applied' => 'Applied for', 'withdrawn' => 'Withdrawn'];

    protected function substantiveFields(): array
    {
        return ['entity_type', 'entity_id', 'accreditation_body_id', 'grade', 'score', 'cycle', 'valid_from', 'valid_to', 'status',
            'certificate_number', 'source_url', 'evidence_file'];
    }

    public function body() { return $this->belongsTo(AccreditationBody::class, 'accreditation_body_id'); }
    public function entity() { return $this->morphTo('entity', 'entity_type', 'entity_id'); }

    /** "Expired" is computed from the validity date, so it can never be forgotten: no job has to flip it. */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'active' && $this->valid_to && $this->valid_to->lt(today())) {
            return 'expired';
        }
        return $this->status;
    }

    public function isCurrent(): bool
    {
        return $this->effective_status === 'active';
    }

    public function scopeCurrent($q)
    {
        return $q->where('status', 'active')->where(fn ($w) => $w->whereNull('valid_to')->orWhere('valid_to', '>=', today()));
    }

    public function scopeExpired($q)
    {
        return $q->where(fn ($w) => $w->where('status', 'expired')->orWhere(fn ($x) => $x->where('status', 'active')->whereNotNull('valid_to')->where('valid_to', '<', today())));
    }

    public function scopeExpiringWithin($q, int $days)
    {
        return $q->where('status', 'active')->whereNotNull('valid_to')->whereBetween('valid_to', [today(), today()->addDays($days)]);
    }

    protected static function booted(): void
    {
        static::saving(function (EntityAccreditation $a) {
            if (!Levels::valid($a->entity_type)) {
                throw new \InvalidArgumentException('Unknown level "' . $a->entity_type . '".');
            }
            if ($a->valid_from && $a->valid_to && $a->valid_to->lt($a->valid_from)) {
                throw new \InvalidArgumentException('Valid-to must not be before valid-from.');
            }
        });
    }
}
