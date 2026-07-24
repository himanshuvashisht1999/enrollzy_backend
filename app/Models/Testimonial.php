<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $guarded = [];

    public function mentorProfile()
    {
        return $this->belongsTo(MentorProfile::class, 'mentor_profile_id');
    }
}
