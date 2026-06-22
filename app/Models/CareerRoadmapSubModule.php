<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CareerRoadmapSubModule extends Model
{
    protected $table = 'career_roadmap_sub_modules';

    protected $fillable = [
        'stage_id',
        'parent_id',
        'title',
        'slug',
        'image',
        'description',
        'custom_fields',
        'status'
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $slug = Str::slug($model->title);
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
            if ($model->isDirty('title') && empty($model->slug)) {
                $slug = Str::slug($model->title);
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

    public function stage()
    {
        return $this->belongsTo(CareerRoadmapStage::class, 'stage_id');
    }

    public function parent()
    {
        return $this->belongsTo(CareerRoadmapSubModule::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CareerRoadmapSubModule::class, 'parent_id');
    }
}
