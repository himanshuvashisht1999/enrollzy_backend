<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\ExamModule\Exam;
use Illuminate\Database\Eloquent\Model;

class Cutoff extends Model
{
    use Auditable;

    protected $table = 'cns_cutoffs';
    protected $fillable = ['round_id', 'exam_id', 'organisation_id', 'course_id', 'quota_id', 'category_id', 'gender', 'pwd',
        'rank_type', 'opening_value', 'closing_value', 'import_batch_id'];
    protected $casts = ['pwd' => 'boolean'];

    public const RANK_TYPES = ['air' => 'All India Rank', 'category_rank' => 'Category rank', 'state_merit_rank' => 'State merit rank', 'score' => 'Score', 'percentile' => 'Percentile'];
    /** Ranks get worse as they grow, scores and percentiles get worse as they shrink. */
    public const HIGHER_IS_WORSE = ['air', 'category_rank', 'state_merit_rank'];

    public function round() { return $this->belongsTo(Round::class, 'round_id'); }
    public function exam() { return $this->belongsTo(Exam::class, 'exam_id'); }
    public function organisation() { return $this->belongsTo(\App\Models\Organisation::class, 'organisation_id'); }
    public function course() { return $this->belongsTo(\App\Models\Course::class, 'course_id'); }
    public function quota() { return $this->belongsTo(Quota::class, 'quota_id'); }
    public function category() { return $this->belongsTo(Category::class, 'category_id'); }
}
