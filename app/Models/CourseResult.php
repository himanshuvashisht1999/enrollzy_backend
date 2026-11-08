<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A coaching programme's results for one year. Saved by App\Support\Institutes\ProgramFields. */
class CourseResult extends Model
{
    protected $table = 'course_results';

    protected $fillable = ['organisation_course_id', 'year', 'selections', 'top_rank', 'notes'];

    protected $casts = ['year' => 'integer', 'selections' => 'integer', 'top_rank' => 'integer'];

    public function organisationCourse()
    {
        return $this->belongsTo(OrganisationCourse::class);
    }
}
