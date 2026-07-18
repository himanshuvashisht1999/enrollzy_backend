<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUsTeam extends Model
{
    protected $fillable = ['name', 'job_profile', 'image', 'sort_order'];
}
