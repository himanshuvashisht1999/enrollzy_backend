<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'status', 'sort_order'];

    protected $casts = [
        'status' => 'boolean',
    ];
}
