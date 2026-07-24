<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'parent_id', 'image'];

    public function parent()
    {
        return $this->belongsTo(CommunityCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CommunityCategory::class, 'parent_id');
    }

    public function questions()
    {
        return $this->hasMany(CommunityQuestion::class, 'category_id');
    }
}
