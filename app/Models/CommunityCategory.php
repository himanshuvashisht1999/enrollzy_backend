<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function questions()
    {
        return $this->hasMany(CommunityQuestion::class, 'category_id');
    }
}
