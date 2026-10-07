<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FacultyDesignation extends Model
{
    use HasFactory;

    protected $table = 'faculty_designations';

    protected $fillable = [
        'name',
        'slug',
        'institution_category',
        'hierarchy_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'hierarchy_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name . '-' . ($model->institution_category ?? 'all'));
            }
        });
    }

    public function profiles()
    {
        return $this->hasMany(FacultyProfile::class, 'designation_id');
    }

    public function departmentMappings()
    {
        return $this->hasMany(FacultyDepartmentMapping::class, 'designation_id');
    }
}
