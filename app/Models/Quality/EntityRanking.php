<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\Quality\Concerns\HasVerification;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One ranking of one entity by one body in one year. A band (101-150) is stored as a band, never as an invented number. */
class EntityRanking extends Model
{
    use Auditable, HasVerification, SoftDeletes;

    protected $table = 'entity_rankings';
    protected $guarded = ['id'];
    protected $casts = ['published_on' => 'date', 'verified_at' => 'datetime', 'score' => 'float'];

    protected function substantiveFields(): array
    {
        return ['entity_type', 'entity_id', 'ranking_body_id', 'ranking_category_id', 'ranking_year', 'rank_exact', 'rank_band_from', 'rank_band_to',
            'score', 'total_ranked', 'external_institute_id', 'source_url', 'evidence_file', 'published_on'];
    }

    public function body() { return $this->belongsTo(RankingBody::class, 'ranking_body_id'); }
    public function category() { return $this->belongsTo(RankingCategory::class, 'ranking_category_id'); }
    public function entity() { return $this->morphTo('entity', 'entity_type', 'entity_id'); }

    public function isBand(): bool
    {
        return $this->rank_exact === null && $this->rank_band_from !== null;
    }

    /** "#45" or "101-150". */
    public function getRankLabelAttribute(): string
    {
        if ($this->rank_exact !== null) return '#' . $this->rank_exact;
        if ($this->rank_band_from !== null) return $this->rank_band_from . '-' . $this->rank_band_to;
        return $this->score !== null ? 'Score ' . $this->score : '-';
    }

    /** Sortable position: a band sorts by its upper edge so it never outranks an exact rank. */
    public function getSortRankAttribute(): int
    {
        return $this->rank_exact ?? $this->rank_band_to ?? PHP_INT_MAX;
    }

    protected static function booted(): void
    {
        static::saving(function (EntityRanking $r) {
            if (!Levels::valid($r->entity_type)) {
                throw new \InvalidArgumentException('Unknown level "' . $r->entity_type . '".');
            }
            $hasExact = $r->rank_exact !== null;
            $hasBand = $r->rank_band_from !== null || $r->rank_band_to !== null;
            if ($hasExact && $hasBand) {
                throw new \InvalidArgumentException('Give either an exact rank or a band, not both.');
            }
            if ($hasBand && ($r->rank_band_from === null || $r->rank_band_to === null || $r->rank_band_to < $r->rank_band_from)) {
                throw new \InvalidArgumentException('A band needs both ends, and the end must not be below the start.');
            }
            if (!$hasExact && !$hasBand && $r->score === null) {
                throw new \InvalidArgumentException('Give a rank, a band or a score.');
            }
            // The category must belong to the chosen body.
            if (RankingCategory::where('id', $r->ranking_category_id)->where('ranking_body_id', $r->ranking_body_id)->doesntExist()) {
                throw new \InvalidArgumentException('That category does not belong to the chosen ranking body.');
            }
        });
    }
}
