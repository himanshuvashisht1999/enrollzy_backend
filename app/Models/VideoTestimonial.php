<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTestimonial extends Model
{
    protected $fillable = ['name', 'course', 'thumbnail', 'video_url', 'sort_order', 'is_active'];
}
