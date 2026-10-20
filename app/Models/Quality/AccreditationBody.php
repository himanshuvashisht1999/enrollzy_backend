<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class AccreditationBody extends Model
{
    use Auditable;

    protected $table = 'accreditation_bodies';
    protected $fillable = ['name', 'short_name', 'kind', 'applicable_levels', 'has_grade', 'grade_options', 'has_validity', 'country', 'website', 'status', 'sort_order'];
    protected $casts = ['applicable_levels' => 'array', 'grade_options' => 'array', 'has_grade' => 'boolean', 'has_validity' => 'boolean', 'status' => 'boolean'];

    public const KINDS = ['accreditation' => 'Accreditation', 'statutory_approval' => 'Statutory approval', 'recognition' => 'Recognition', 'affiliation' => 'Affiliation'];

    public function records() { return $this->hasMany(EntityAccreditation::class, 'accreditation_body_id'); }
    public function approvals() { return $this->hasMany(EntityApproval::class, 'accreditation_body_id'); }

    /** Disciplines this regulator governs: used to find programmes that should have an approval. */
    public function disciplines() { return $this->belongsToMany(\App\Models\Discipline::class, 'accreditation_body_disciplines', 'accreditation_body_id', 'discipline_id'); }

    public function getDisciplineIdsAttribute(): array
    {
        return $this->disciplines()->pluck('disciplines.id')->all();
    }

    /** Accreditations (NAAC, NBA, AMBA...) go in entity_accreditations; everything else (statutory approvals, recognitions, affiliations) in entity_approvals. */
    public function isApprovalKind(): bool
    {
        return $this->kind !== 'accreditation';
    }

    public function getLevelsLabelAttribute(): string
    {
        return empty($this->applicable_levels) ? 'Any level' : implode(', ', array_map(fn ($l) => \App\Support\Quality\Levels::LABELS[$l] ?? $l, $this->applicable_levels));
    }

    public function allowsLevel(string $level): bool
    {
        return empty($this->applicable_levels) || in_array($level, $this->applicable_levels, true);
    }
}
