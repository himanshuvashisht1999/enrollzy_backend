<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Campus extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'established_year' => 'integer',
        'smart_classrooms' => 'boolean',
        'library_available' => 'boolean',
        'digital_library_access' => 'boolean',
        'hostel_available' => 'boolean',
        'medical_facility_available' => 'boolean',
        'transport_available' => 'boolean',
        'parking_available' => 'boolean',
        'cctv_coverage' => 'boolean',
        'fire_safety_certified' => 'boolean',
        'disaster_management_plan' => 'boolean',
        'verification_status' => 'boolean',
        'status' => 'boolean',
        'sports_facilities' => 'array',
        'campus_contact_numbers' => 'array',
        'last_updated_on' => 'datetime',
        'brand_compliance_verified' => 'boolean',
        'science_labs_available' => 'boolean',
        'computer_labs_available' => 'boolean',
        'playground_available' => 'boolean',
        'gps_enabled_buses' => 'boolean',
        'visitor_management_system' => 'boolean',
        'bus_routes' => 'array',
        'exams_prepared_for' => 'array',
        'target_classes' => 'array',
        'class_profile' => 'array',
        'facilities' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->campus_name . '-' . Str::random(4));
            }
        });

        static::saving(function ($campus) {
            // Basic verification logic if needed
        });
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function courses()
    {
        return $this->hasMany(OrganisationCourse::class);
    }
}
