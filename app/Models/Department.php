<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organisation_id',
        'campus_id',
        'department_name',
        'department_code',
        'slug',
        'department_type',
        'established_year',
        'about_department',
        'discipline_area',
        'specializations_supported',
        'education_levels_supported',
        'is_interdisciplinary',
        'collaborating_departments',
        'head_of_department_name',
        'head_of_department_designation',
        'hod_appointment_type',
        'hod_email',
        'department_office_contact',
        'faculty_count',
        'curriculum_design_responsibility',
        'exam_setting_responsibility',
        'research_programs_managed',
        'phd_supervision_available',
        'industry_collaboration_supported',
        'department_labs_count',
        'specialized_labs_available',
        'research_centers_under_department',
        'department_library_section',
        'classrooms_count',
        'research_publications_count',
        'funded_projects_count',
        'patents_filed_count',
        'industry_projects_count',
        'department_website_url',
        'department_email',
        'department_notice_board_url',
        'online_meeting_tools_used',
        'schema_type',
        'meta_title',
        'meta_description',
        'focus_keywords',
        'canonical_url',
        'status',
        'visibility',
        'data_source',
        'confidence_score',
        'created_by',
        'last_updated_on',
    ];

    protected $casts = [
        'specializations_supported' => 'array',
        'education_levels_supported' => 'array',
        'collaborating_departments' => 'array',
        'online_meeting_tools_used' => 'array',
        'focus_keywords' => 'array',
        'is_interdisciplinary' => 'boolean',
        'curriculum_design_responsibility' => 'boolean',
        'exam_setting_responsibility' => 'boolean',
        'research_programs_managed' => 'boolean',
        'phd_supervision_available' => 'boolean',
        'industry_collaboration_supported' => 'boolean',
        'specialized_labs_available' => 'boolean',
        'department_library_section' => 'boolean',
        'last_updated_on' => 'datetime',
        'established_year' => 'integer',
        'confidence_score' => 'decimal:2',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->department_name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('department_name') && empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->department_name, $model->id);
            }
        });
    }

    /**
     * Generate a unique slug for the department
     */
    protected static function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists
     */
    protected static function slugExists($slug, $ignoreId = null)
    {
        $query = static::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }


    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function courses()
    {
        return $this->hasMany(OrganisationCourse::class);
    }

    public function collaboratingDepartments()
    {
        // This is a self-referencing relationship, stored as JSON array of IDs
        // We can't use standard Eloquent relation for JSON array directly, 
        // but we can define an accessor if needed or handle manually.
        // For now, we'll leave it as json cast.
        return $this->attributes['collaborating_departments'];
    }
}
