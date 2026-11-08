<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** A group of careers (Medical & healthcare, Engineering & technology…): the filter and groups of the careers page. */
class CareerCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'description', 'sort_order', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function careers()
    {
        return $this->hasMany(Career::class, 'category_id');
    }

    protected static function booted(): void
    {
        static::saving(function (CareerCategory $c) {
            if (blank($c->slug)) $c->slug = Str::slug($c->name);
            $base = $c->slug;
            for ($i = 2; static::where('slug', $c->slug)->where('id', '!=', $c->id ?? 0)->exists(); $i++) $c->slug = $base . '-' . $i;
        });
    }
}
