<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicClass extends Model
{
    protected $table = 'academic_classes';

    protected $fillable = ['name', 'title', 'status', 'sort_order'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->name) && !empty($model->title)) {
                $model->name = $model->title;
            }
            if (empty($model->title) && !empty($model->name)) {
                $model->title = $model->name;
            }
        });
    }

    public function getTitleAttribute($value)
    {
        return $value ?: $this->name;
    }
}
