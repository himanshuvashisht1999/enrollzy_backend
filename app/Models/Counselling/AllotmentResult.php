<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class AllotmentResult extends Model
{
    use Auditable;

    protected $table = 'cns_allotment_results';
    protected $fillable = ['round_id', 'title', 'result_type', 'file', 'url', 'published_at', 'source_url'];
    protected $casts = ['published_at' => 'datetime'];

    public const TYPES = ['provisional' => 'Provisional', 'final' => 'Final', 'revised' => 'Revised'];

    public function round() { return $this->belongsTo(Round::class, 'round_id'); }
}
