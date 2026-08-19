<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageStreamTab extends Model
{
    use HasFactory;

    protected $table = 'homepage_stream_tabs';

    protected $fillable = [
        'key',
        'name',
        'keywords',
        'default_exams',
        'default_states',
        'default_courses',
        'feature_colleges',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'keywords' => 'array',
        'default_exams' => 'array',
        'default_states' => 'array',
        'default_courses' => 'array',
        'feature_colleges' => 'array',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
