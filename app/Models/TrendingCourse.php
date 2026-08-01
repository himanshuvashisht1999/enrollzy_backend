<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendingCourse extends Model
{
    use HasFactory;

    protected $table = 'trending_courses';

    protected $fillable = [
        'name',
        'instructor',
        'price',
        'rating',
        'image',
        'url',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
