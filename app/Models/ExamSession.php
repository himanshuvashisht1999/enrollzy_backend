<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'academic_year', 'session_name',
        'application_start_date', 'application_end_date', 'correction_window_dates',
        'admit_card_release_date', 'admit_card_url', 'exam_start_date', 'exam_end_date',
        'exam_date', 'answer_key_release_date',
        'result_declaration_date', 'result_url', 'counselling_start_date', 'counselling_end_date',
        'is_current_session', 'status'
    ];

    protected $casts = [
        'application_start_date' => 'date',
        'application_end_date' => 'date',
        'admit_card_release_date' => 'date',
        'exam_start_date' => 'date',
        'exam_end_date' => 'date',
        'exam_date' => 'date',
        'answer_key_release_date' => 'date',
        'result_declaration_date' => 'date',
        'counselling_start_date' => 'date',
        'counselling_end_date' => 'date',
        'is_current_session' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
