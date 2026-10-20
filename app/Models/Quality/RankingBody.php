<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class RankingBody extends Model
{
    use Auditable;

    protected $table = 'ranking_bodies';
    protected $fillable = ['name', 'short_name', 'type', 'country', 'website', 'trust_level', 'logo', 'status', 'sort_order'];
    protected $casts = ['status' => 'boolean'];

    public const TYPES = ['government' => 'Government', 'international' => 'International', 'media' => 'Media'];
    public const TRUST = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'];

    public function categories() { return $this->hasMany(RankingCategory::class, 'ranking_body_id')->orderBy('sort_order')->orderBy('name'); }
    public function rankings() { return $this->hasMany(EntityRanking::class, 'ranking_body_id'); }
}
