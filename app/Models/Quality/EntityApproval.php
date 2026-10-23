<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\Quality\Concerns\HasVerification;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A statutory approval / recognition / affiliation (AICTE, NMC, BCI, PCI...) of one entity for an academic year, with its sanctioned intake. */
class EntityApproval extends Model
{
    use Auditable, HasVerification, SoftDeletes;

    protected $table = 'entity_approvals';
    protected $guarded = ['id'];
    protected $casts = ['valid_from' => 'date', 'valid_to' => 'date', 'verified_at' => 'datetime', 'sanctioned_intake' => 'integer'];

    public const STATUSES = ['active' => 'Active', 'expired' => 'Expired', 'applied' => 'Applied for', 'withdrawn' => 'Withdrawn'];

    protected function substantiveFields(): array
    {
        return ['entity_type', 'entity_id', 'accreditation_body_id', 'approval_number', 'academic_year', 'sanctioned_intake', 'valid_from', 'valid_to',
            'status', 'source_url', 'evidence_file'];
    }

    public function body() { return $this->belongsTo(AccreditationBody::class, 'accreditation_body_id'); }
    public function entity() { return $this->morphTo('entity', 'entity_type', 'entity_id'); }

    /** "Expired" is computed from the validity date, so it can never be forgotten. */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'active' && $this->valid_to && $this->valid_to->lt(today())) return 'expired';
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

    public function scopeExpiringWithin($q, int $days)
    {
        return $q->where('status', 'active')->whereNotNull('valid_to')->whereBetween('valid_to', [today(), today()->addDays($days)]);
    }

    /** 2025-26 => ends in 2026. Used to put the newest year first. */
    public static function yearSort(?string $y): int
    {
        return $y && preg_match('/^(\d{4})-(\d{2})$/', $y, $m) ? (int) $m[1] : 0;
    }

    protected static function booted(): void
    {
        static::saving(function (EntityApproval $a) {
            if (!Levels::valid($a->entity_type)) {
                throw new \InvalidArgumentException('Unknown level "' . $a->entity_type . '".');
            }
            if ($a->valid_from && $a->valid_to && $a->valid_to->lt($a->valid_from)) {
                throw new \InvalidArgumentException('Valid-to must not be before valid-from.');
            }
            if ($a->academic_year !== null && !preg_match('/^(\d{4})-(\d{2})$/', $a->academic_year, $m)) {
                throw new \InvalidArgumentException('Academic year must look like 2025-26.');
            }
            if ($a->academic_year !== null && ((int) substr((string) ((int) $m[1] + 1), -2)) !== (int) $m[2]) {
                throw new \InvalidArgumentException('Academic year must be consecutive years, like 2025-26.');
            }
        });
    }
}
