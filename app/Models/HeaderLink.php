<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderLink extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'title', 'url', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(HeaderLink::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HeaderLink::class, 'parent_id');
    }
}

