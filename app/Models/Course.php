<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'status', 'is_show_on_website', 'program_level_id', 'stream_offered_id', 
        'discipline_id', 'duration', 'sort_order',
        'full_form', 'course_type_id', 'available_modes', 'overview',
        'generic_eligibility', 'common_entrance_exams', 'core_curriculum',
        'common_specializations', 'skills_gained', 'career_scope',
        'average_salary_range', 'higher_education_options', 'course_comparison',
        'pros_cons', 'faqs', 'related_courses'
    ];

    protected $casts = [
        'available_modes' => 'array',
        'common_entrance_exams' => 'array',
        'common_specializations' => 'array',
        'faqs' => 'array',
        'related_courses' => 'array',
        'status' => 'boolean',
        'is_show_on_website' => 'boolean',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->name);
            }
        });

        static::deleting(function ($course) {
            if (method_exists($course, 'documents')) {
                $course->documents->each(function ($doc) {
                    if ($doc->file_path && file_exists(public_path($doc->file_path))) {
                        @unlink(public_path($doc->file_path));
                    }
                });
                $course->documents()->delete();
            }
        });
    }

    public function documents()
    {
        return $this->hasMany(CourseDocument::class)->orderBy('sort_order', 'asc');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    public function universityCourses()
    {
        return $this->hasMany(UniversityCourse::class);
    }

    public function organisationCourses()
    {
        return $this->hasMany(OrganisationCourse::class)->orderBy('sort_order', 'asc');
    }

    public function programLevel()
    {
        return $this->belongsTo(ProgramLevel::class, 'program_level_id');
    }

    public function streamOffered()
    {
        return $this->belongsTo(StreamOffered::class, 'stream_offered_id');
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    public function programTypes()
    {
        return $this->belongsToMany(ProgramType::class, 'course_program_type');
    }

    public function courseType()
    {
        return $this->belongsTo(CourseType::class, 'course_type_id');
    }

    public function facultyMappings()
    {
        return $this->hasMany(FacultyCourseMapping::class, 'course_id');
    }

    public function faculties()
    {
        return $this->belongsToMany(FacultyProfile::class, 'faculty_course_mapping', 'course_id', 'faculty_id')
                    ->withPivot('subject_name', 'academic_year', 'semester', 'role', 'status');
    }
}

