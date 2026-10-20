<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\Quality\Concerns\HasVerification;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** An award or recognition received by one entity, with who gave it, when, and the evidence. Shown to students only once verified. */
class EntityAward extends Model
{
    use Auditable, HasVerification, SoftDeletes;

    protected $table = 'entity_awards';
    protected $guarded = ['id'];
    protected $casts = ['verified_at' => 'datetime', 'award_year' => 'integer'];

    public const KINDS = [
        'award' => 'Award / recognition', 'ranking_mention' => 'Ranking mention (belongs under Rankings)', 'membership' => 'Membership',
        'affiliation' => 'Affiliation (belongs under Approvals / board affiliation)', 'accreditation_mention' => 'Accreditation mention (belongs under Accreditations)', 'other' => 'Other / descriptive claim',
    ];
    public const SHORT_KINDS = ['award' => 'Award', 'ranking_mention' => 'Ranking mention', 'membership' => 'Membership', 'affiliation' => 'Affiliation', 'accreditation_mention' => 'Accreditation mention', 'other' => 'Other'];
    public const CATEGORIES = ['Academic', 'Sports', 'Arts & culture', 'Infrastructure', 'Sustainability', 'Leadership', 'Research & innovation', 'Teaching & faculty', 'Student achievement', 'Community service', 'Other'];

    protected function substantiveFields(): array
    {
        return ['entity_type', 'entity_id', 'title', 'kind', 'issuer', 'award_year', 'category', 'description', 'source_url', 'evidence_file'];
    }

    public function entity() { return $this->morphTo('entity', 'entity_type', 'entity_id'); }

    /** Lower-case, no punctuation, single spaces: used to spot the same award written twice. */
    public static function normalise(string $title): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($title))));
    }

    protected static function booted(): void
    {
        static::saving(function (EntityAward $a) {
            if (!Levels::valid($a->entity_type)) {
                throw new \InvalidArgumentException('Unknown level "' . $a->entity_type . '".');
            }
            if (trim((string) $a->title) === '') {
                throw new \InvalidArgumentException('An award needs a title.');
            }
            if ($a->award_year !== null && ($a->award_year < 1900 || $a->award_year > (int) now()->year + 1)) {
                throw new \InvalidArgumentException('Award year must be between 1900 and next year.');
            }
        });
    }
}
