<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyCourseMapping extends Model
{
    use HasFactory;

    protected $table = 'faculty_course_mapping';

    protected $fillable = [
        'faculty_id',
        'organisation_course_id',
        'course_id',
        'subject_id',
        'subject_name',
        'academic_year',
        'semester',
        'role',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_id');
    }

    public function organisationCourse()
    {
        return $this->belongsTo(OrganisationCourse::class, 'organisation_course_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
