<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUsAdvisoryBoard extends Model
{
    protected $fillable = ['name', 'designation', 'image', 'linkedin_url', 'sort_order'];
}
