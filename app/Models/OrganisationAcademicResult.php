<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationAcademicResult extends Model
{
    protected $fillable = [
        'organisation_id', 'exam_year', 'class', 'students_appeared',
        'pass_percentage', 'distinction_percentage', 'average_score',
        'highest_score', 'topper_names'
    ];

    protected $casts = [
        'topper_names' => 'array',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
