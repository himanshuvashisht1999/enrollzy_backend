<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NoteworthyCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'status'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
        static::updating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(NoteworthyMention::class);
    }
}
