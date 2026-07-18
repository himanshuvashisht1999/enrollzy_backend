<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorVerification extends Model
{
    protected $fillable = [
        'mentor_profile_id', 'gov_id_path', 'gov_id_status', 'gov_id_comment',
        'linkedin_status', 'linkedin_comment', 'background_check_status', 'background_check_comment',
        'degree_status', 'degree_comment', 'platform_agreement_signed'
    ];

    public function profile()
    {
        return $this->belongsTo(MentorProfile::class, 'mentor_profile_id');
    }
}
