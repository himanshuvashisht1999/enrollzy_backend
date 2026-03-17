<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = ['section_key', 'name', 'sort_order', 'is_visible'];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];
}
