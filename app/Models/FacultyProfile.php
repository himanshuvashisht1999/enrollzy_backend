<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FacultyProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'faculty_profiles';

    protected $fillable = [
        // 1. Basic Identity
        'faculty_code',
        'full_name',
        'display_name',
        'profile_photo',
        'designation_id',
        'designation_name',
        'faculty_type_id',
        'gender',
        'date_of_birth',
        'short_bio',
        'detailed_biography',
        'date_of_joining',
        'experience_years',
        'status',

        // 2. Contact & Privacy (Internal)
        'official_email',
        'official_phone',
        'extension',
        'office_location',
        'office_room_number',

        // 3. Research Overview
        'research_interests',
        'research_areas',
        'total_publications',
        'books_published',
        'patents',
        'research_projects',
        'grants_received',

        // 4. Specializations & Areas
        'specializations',
        'research_areas_tags',
        'subjects_taught',

        // 5. Social & Professional Profiles
        'linkedin_url',
        'google_scholar_url',
        'orcid_id',
        'researchgate_url',
        'personal_website_url',
        'scopus_id',
        'vidwan_id',
        'academia_url',

        // 6. SEO & Visibility
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_image',
        'profile_status',
        'is_indexed',

        // 7. Verification Workflow
        'verification_status',
        'verified_by',
        'verified_date',
        'verification_source',
        'last_verification_date',
        'verification_notes',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_indexed' => 'boolean',
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'verified_date' => 'datetime',
        'last_verification_date' => 'datetime',
        'experience_years' => 'decimal:1',
        'total_publications' => 'integer',
        'books_published' => 'integer',
        'patents' => 'integer',
        'research_projects' => 'integer',
        'specializations' => 'array',
        'research_areas_tags' => 'array',
        'subjects_taught' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($faculty) {
            if (empty($faculty->faculty_code)) {
                $maxId = (int) self::max('id') + 1;
                $faculty->faculty_code = 'FAC-' . str_pad($maxId, 4, '0', STR_PAD_LEFT);
            }
            if (empty($faculty->slug)) {
                $faculty->slug = Str::slug($faculty->full_name) . '-' . strtolower(Str::random(5));
            }
        });
    }

    // Relationships
    public function designation()
    {
        return $this->belongsTo(FacultyDesignation::class, 'designation_id');
    }

    public function facultyType()
    {
        return $this->belongsTo(FacultyType::class, 'faculty_type_id');
    }

    public function verifier()
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function departmentMappings()
    {
        return $this->hasMany(FacultyDepartmentMapping::class, 'faculty_id');
    }

    public function courseMappings()
    {
        return $this->hasMany(FacultyCourseMapping::class, 'faculty_id');
    }

    public function qualifications()
    {
        return $this->hasMany(FacultyQualification::class, 'faculty_id')->orderBy('display_order', 'asc');
    }

    public function experiences()
    {
        return $this->hasMany(FacultyExperience::class, 'faculty_id')->orderBy('display_order', 'asc')->orderBy('start_date', 'desc');
    }

    public function publications()
    {
        return $this->hasMany(FacultyPublication::class, 'faculty_id')->orderBy('display_order', 'asc')->orderBy('publication_date', 'desc');
    }

    public function achievements()
    {
        return $this->hasMany(FacultyAchievement::class, 'faculty_id')->orderBy('display_order', 'asc')->orderBy('year', 'desc');
    }

    public function certifications()
    {
        return $this->hasMany(FacultyCertification::class, 'faculty_id')->orderBy('display_order', 'asc');
    }

    public function customFields()
    {
        return $this->hasMany(FacultyCustomField::class, 'faculty_id');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'faculty_department_mapping', 'faculty_id', 'organisation_id')
                    ->withPivot('campus_id', 'department_id', 'designation_id', 'is_primary_department', 'status')
                    ->withTimestamps();
    }

    public function primaryDepartmentMapping()
    {
        return $this->hasOne(FacultyDepartmentMapping::class, 'faculty_id')->where('is_primary_department', true);
    }

    // Helper Accessor
    public function getEffectiveDesignationAttribute()
    {
        return $this->designation?->name ?? $this->designation_name ?? 'Faculty';
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset($this->profile_photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=0D8ABC&color=fff&size=150';
    }
}
