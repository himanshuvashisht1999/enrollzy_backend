<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CareerRoadmapCategory extends Model
{
    protected $table = 'career_roadmap_categories';

    protected $fillable = ['name', 'slug', 'status'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $slug = Str::slug($model->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $model->slug = $slug;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $slug = Str::slug($model->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->where('id', '!=', $model->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $model->slug = $slug;
            }
        });
    }

    public function stages()
    {
        return $this->hasMany(CareerRoadmapStage::class, 'category_id');
    }
}
