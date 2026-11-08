<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A topper of a coaching programme (shown on the website only with consent). Saved by App\Support\Institutes\ProgramFields. */
class CourseTopper extends Model
{
    protected $table = 'course_toppers';

    protected $fillable = ['organisation_course_id', 'year', 'name', 'rank', 'exam', 'photo_path', 'consent', 'sort_order'];

    protected $casts = ['consent' => 'boolean', 'rank' => 'integer', 'year' => 'integer'];

    public function organisationCourse()
    {
        return $this->belongsTo(OrganisationCourse::class);
    }
}
