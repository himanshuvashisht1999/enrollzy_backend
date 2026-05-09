<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMarquee extends Model
{
    protected $fillable = ['logo', 'name', 'heading', 'subheading', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
