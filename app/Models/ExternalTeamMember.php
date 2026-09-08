<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalTeamMember extends Model
{
    use HasFactory;

    protected $table = 'external_team_members';
    protected $guarded = ['id'];

    public function team()
    {
        return $this->belongsTo(ExternalTeam::class, 'external_team_id');
    }

    public function contact()
    {
        return $this->belongsTo(ExternalContact::class, 'external_contact_id');
    }
}
