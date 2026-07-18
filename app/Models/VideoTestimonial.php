<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTestimonial extends Model
{
    protected $fillable = ['name', 'course', 'thumbnail', 'video_url', 'autoplay', 'muted', 'sort_order', 'is_active'];

    protected $casts = [
        'autoplay' => 'boolean',
        'muted' => 'boolean',
    ];
}
