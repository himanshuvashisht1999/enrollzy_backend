<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderMenu extends Model
{
    protected $fillable = ['title', 'url', 'parent_id', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(HeaderMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HeaderMenu::class, 'parent_id')->orderBy('sort_order');
    }
}
