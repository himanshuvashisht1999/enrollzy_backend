<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class RankingCategory extends Model
{
    use Auditable;

    protected $table = 'ranking_categories';
    protected $fillable = ['ranking_body_id', 'name', 'applicable_levels', 'discipline_id', 'status', 'sort_order'];
    protected $casts = ['applicable_levels' => 'array', 'status' => 'boolean'];

    public function body() { return $this->belongsTo(RankingBody::class, 'ranking_body_id'); }
    public function rankings() { return $this->hasMany(EntityRanking::class, 'ranking_category_id'); }

    /** null = recordable at any level. */
    public function allowsLevel(string $level): bool
    {
        return empty($this->applicable_levels) || in_array($level, $this->applicable_levels, true);
    }

    public function getBodyLabelAttribute(): string
    {
        return $this->body?->short_name ?: ($this->body?->name ?? '-');
    }

    public function getLevelsLabelAttribute(): string
    {
        return empty($this->applicable_levels) ? 'Any level' : implode(', ', array_map(fn ($l) => \App\Support\Quality\Levels::LABELS[$l] ?? $l, $this->applicable_levels));
    }

    public function getFullNameAttribute(): string
    {
        return ($this->body?->short_name ?: $this->body?->name) . ' · ' . $this->name;
    }
}
