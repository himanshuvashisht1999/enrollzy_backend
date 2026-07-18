<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterMenu extends Model
{
    protected $fillable = [
        'title', 'url', 'parent_id', 'sort_order', 'status',
        'show_view_all', 'view_all_link', 'bottom_badge_text',
        'bottom_badge_subtext', 'bottom_badge_icon', 'bottom_badge_rating'
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_view_all' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(FooterMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FooterMenu::class, 'parent_id')->orderBy('sort_order');
    }
}
