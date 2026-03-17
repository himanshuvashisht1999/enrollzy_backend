<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSelectedStage extends Model
{
    protected $fillable = ['exam_id', 'exam_stage_id', 'sort_order', 'status'];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function masterStage()
    {
        return $this->belongsTo(ExamStage::class, 'exam_stage_id');
    }
}
