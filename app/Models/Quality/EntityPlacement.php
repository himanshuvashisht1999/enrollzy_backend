<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\Quality\Concerns\HasVerification;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Raw placement figures of one entity for one academic year. Percentages and department / campus / organisation totals are COMPUTED from these
 * rows (see Aggregates); nobody types a total. Course level is the normal home; a row at another level means "reported by the institution".
 */
class EntityPlacement extends Model
{
    use Auditable, HasVerification, SoftDeletes;

    protected $table = 'entity_placements';
    protected $guarded = ['id'];
    protected $casts = ['verified_at' => 'datetime', 'students_graduating' => 'integer', 'students_placed' => 'integer', 'students_higher_studies' => 'integer',
        'median_salary' => 'integer', 'highest_salary' => 'integer', 'average_salary' => 'integer', 'recruiters_count' => 'integer'];

    protected function substantiveFields(): array
    {
        return ['entity_type', 'entity_id', 'academic_year', 'students_graduating', 'students_placed', 'students_higher_studies', 'median_salary',
            'highest_salary', 'average_salary', 'recruiters_count', 'placement_source', 'source_url', 'evidence_file'];
    }

    public function entity() { return $this->morphTo('entity', 'entity_type', 'entity_id'); }

    /** Computed, never typed. Null when either number is missing. */
    public function getPlacementPercentageAttribute(): ?float
    {
        return $this->students_graduating && $this->students_placed !== null ? round($this->students_placed / $this->students_graduating * 100, 1) : null;
    }

    /** Problems that make a row untrustworthy; shown to staff and block verification. */
    public function problems(): array
    {
        $p = [];
        if ($this->students_placed !== null && $this->students_graduating !== null && $this->students_placed > $this->students_graduating) {
            $p[] = 'More students placed (' . number_format($this->students_placed) . ') than graduating (' . number_format($this->students_graduating) . ').';
        }
        if ($this->median_salary !== null && $this->highest_salary !== null && $this->median_salary > $this->highest_salary) {
            $p[] = 'Median salary is higher than the highest salary.';
        }
        return $p;
    }

    protected static function booted(): void
    {
        static::saving(function (EntityPlacement $r) {
            if (!Levels::valid($r->entity_type)) {
                throw new \InvalidArgumentException('Unknown level "' . $r->entity_type . '".');
            }
            if ($r->academic_year !== null) {
                if (!preg_match('/^(\d{4})-(\d{2})$/', $r->academic_year, $m) || ((int) $m[1] + 1) % 100 !== (int) $m[2]) {
                    throw new \InvalidArgumentException('Academic year must be consecutive years, like 2024-25.');
                }
            }
            if ($r->legacy_source === null && ($problems = $r->problems())) {
                throw new \InvalidArgumentException(implode(' ', $problems));
            }
        });
    }
}
