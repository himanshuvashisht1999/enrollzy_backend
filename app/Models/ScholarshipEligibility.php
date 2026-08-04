<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScholarshipEligibility extends Model
{
    use HasFactory;

    protected $table = 'scholarship_eligibilities';

    protected $fillable = [
        'scholarship_id', 'minimum_class', 'maximum_class', 'minimum_percentage', 'maximum_age',
        'gender', 'nationality', 'state', 'city', 'category', 'annual_family_income',
        'course_level', 'course_type', 'academic_stream', 'entrance_exam', 'minimum_exam_score',
        'currently_studying', 'graduation_required', 'work_experience', 'other_conditions'
    ];

    protected $casts = [
        'minimum_percentage' => 'decimal:2',
        'minimum_exam_score' => 'decimal:2',
        'maximum_age' => 'integer',
        'graduation_required' => 'integer'
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
