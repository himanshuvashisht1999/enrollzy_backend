<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendingSkill extends Model
{
    protected $fillable = ['name', 'url', 'sort_order', 'status'];
    
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
