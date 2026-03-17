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

    protected $fillable = [
        'organisation_id',
        'campus_name',
        'slug',
        'campus_type',
        'established_year',
        'city',
        'state',
        'country',
        'pincode',
        'full_address',
        'google_map_url',
        'nearest_transport_hub',
        'campus_area_acres',
        'academic_blocks_count',
        'classrooms_count',
        'smart_classrooms',
        'laboratories_count',
        'library_available',
        'library_books_count',
        'digital_library_access',
        'research_centers_count',
        'hostel_available',
        'hostel_type',
        'hostel_capacity',
        'food_facility',
        'medical_facility_available',
        'sports_facilities',
        'transport_available',
        'bus_routes_count',
        'parking_available',
        'cctv_coverage',
        'security_staff_count',
        'fire_safety_certified',
        'disaster_management_plan',
        'campus_contact_numbers',
        'campus_email',
        'campus_website',
        'verification_status',
        'status',
        'last_updated_on',
        // Type 3 Fields
        'ownership_model',
        'brand_type',
        'franchise_partner_name',
        'franchise_start_year',
        'brand_compliance_verified',
        // Type 4 Fields (School)
        'nearest_landmark',
        'science_labs_available',
        'computer_labs_available',
        'playground_available',
        'bus_fleet_size',
        'gps_enabled_buses',
        'visitor_management_system',
        'bus_routes',
        'campus_area_unit',
        'exams_prepared_for',
        'target_classes',
        'about_institute',
    ];

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
