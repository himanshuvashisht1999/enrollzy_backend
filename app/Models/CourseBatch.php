<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A batch of a coaching programme (start date, timing, medium, seats). Saved by App\Support\Institutes\ProgramFields. */
class CourseBatch extends Model
{
    protected $table = 'course_batches';

    protected $fillable = ['organisation_course_id', 'name', 'start_date', 'timing', 'timing_details', 'medium', 'total_seats', 'seats_left', 'status', 'sort_order'];

    protected $casts = ['start_date' => 'date:Y-m-d', 'total_seats' => 'integer', 'seats_left' => 'integer'];

    public function organisationCourse()
    {
        return $this->belongsTo(OrganisationCourse::class);
    }
}
