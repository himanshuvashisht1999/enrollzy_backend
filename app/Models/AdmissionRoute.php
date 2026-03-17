<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'course_id',
        'exam_id',
        'exam_source_type',
        'min_eligibility_qualification',
        'min_eligibility_marks',
        'min_exam_rank',
        'min_exam_score',
        'cutoff_year_wise',
        'seat_matrix',
        'admission_process_note',
        'application_url',
        'counselling_authority',
        'is_primary_route',
        'priority_order',
        'status'
    ];

    protected $casts = [
        'cutoff_year_wise' => 'array',
        'seat_matrix' => 'array',
        'is_primary_route' => 'boolean',
        'status' => 'boolean',
    ];

    // Relationships

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
