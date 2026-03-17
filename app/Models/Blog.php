<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'excerpt',
        'content',
        'author',
        'image',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'published_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            $blog->slug = Str::slug($blog->title);
        });

        static::updating(function ($blog) {
            $blog->slug = Str::slug($blog->title);
        });
    }
}
