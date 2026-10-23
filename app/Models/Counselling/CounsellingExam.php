<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\ExamModule\Exam;
use Illuminate\Database\Eloquent\Model;

/** The single exam <-> counselling mapping, read from both sides. */
class CounsellingExam extends Model
{
    use Auditable;

    protected $table = 'cns_counselling_exams';
    protected $fillable = ['counselling_id', 'exam_id', 'role', 'valid_from_year', 'valid_to_year', 'seat_scope_note', 'sort_order'];

    public function counselling()
    {
        return $this->belongsTo(Counselling::class, 'counselling_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
