<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\Quality\Concerns\HasVerification;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One individual rating / review of an entity. Average ratings and counts at every level are COMPUTED from these rows. */
class EntityReview extends Model
{
    use Auditable, HasVerification, SoftDeletes;

    protected $table = 'entity_reviews';
    protected $guarded = ['id'];
    protected $casts = ['reviewed_on' => 'date', 'verified_at' => 'datetime', 'is_published' => 'boolean', 'rating' => 'float', 'infrastructure' => 'float',
        'campus_life' => 'float', 'academics' => 'float', 'placements' => 'float', 'value_for_money' => 'float'];

    public const REVIEWER_TYPES = ['student' => 'Student', 'alumni' => 'Alumni', 'parent' => 'Parent', 'faculty' => 'Faculty', 'recruiter' => 'Recruiter', 'other' => 'Other'];
    public const SUB_RATINGS = ['infrastructure' => 'Infrastructure', 'campus_life' => 'Campus life', 'academics' => 'Academics', 'placements' => 'Placements', 'value_for_money' => 'Value for money'];

    protected function substantiveFields(): array
    {
        return ['entity_type', 'entity_id', 'reviewer_type', 'rating', 'infrastructure', 'campus_life', 'academics', 'placements', 'value_for_money',
            'comment', 'title', 'reviewed_on', 'review_year', 'source', 'source_url'];   // hiding / showing a review does not make it unverified
    }

    public function entity() { return $this->morphTo('entity', 'entity_type', 'entity_id'); }

    /** Lower-case, no punctuation, single spaces: used to spot the same text written twice. */
    public static function normalise(?string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower((string) $s))));
    }

    /** Counts toward averages: published and not rejected. (Public display should use verified() on top of this.) */
    public function scopeCounted($q)
    {
        return $q->where('is_published', true)->where('verification_status', '!=', 'rejected');
    }

    protected static function booted(): void
    {
        static::saving(function (EntityReview $r) {
            if (!Levels::valid($r->entity_type)) {
                throw new \InvalidArgumentException('Unknown level "' . $r->entity_type . '".');
            }
            foreach (array_merge(['rating'], array_keys(self::SUB_RATINGS)) as $f) {
                if ($r->$f !== null && ($r->$f < 1 || $r->$f > 5)) {
                    throw new \InvalidArgumentException(($f === 'rating' ? 'Rating' : self::SUB_RATINGS[$f]) . ' must be between 1 and 5.');
                }
            }
            if ($r->rating === null) {
                throw new \InvalidArgumentException('A review needs an overall rating.');
            }
        });
    }
}
