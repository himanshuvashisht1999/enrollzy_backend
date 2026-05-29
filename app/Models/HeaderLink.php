<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderLink extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'url', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];
}
