<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasteCategory extends Model
{
    protected $fillable = ['name', 'slug', 'status'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            $category->slug = \Illuminate\Support\Str::slug($category->name);
        });
        static::updating(function ($category) {
            $category->slug = \Illuminate\Support\Str::slug($category->name);
        });
    }
}
