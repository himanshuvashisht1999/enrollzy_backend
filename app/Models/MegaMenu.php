<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MegaMenu extends Model
{
    protected $fillable = [
        'parent_id',
        'header_link_id',
        'title',
        'url',
        'column_title',
        'sort_order',
        'status',
        'is_highlighted',
    ];

    public function parent()
    {
        return $this->belongsTo(MegaMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MegaMenu::class, 'parent_id')->orderBy('sort_order');
    }

    public function headerLink()
    {
        return $this->belongsTo(HeaderLink::class, 'header_link_id');
    }

    // Scope: only sub-items (linked to a header_link, with a parent_id)
    public function scopeSubItems($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
