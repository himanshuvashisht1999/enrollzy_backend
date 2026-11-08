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
        'long_description',
        'custom_fields',
        'status',
        'sort_order', 'roadmap_type', 'item_type', 'url_slug', 'summary', 'eligibility', 'conducted_by', 'when_text', 'duration', 'fee', 'salary',
        'official_url', 'exam_id', 'course_id', 'scholarship_id', 'career_id', 'subjects', 'who_should', 'closes', 'career_ids', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'career_ids' => 'array',
    ];

    /** Roadmap types (top-level modules): how the website groups and lays out a roadmap. */
    public const ROADMAP_TYPES = [
        'stream' => 'Stream (PCM, PCB, Commerce…)', 'career_path' => 'Career paths (courses after this class)', 'careers' => 'Career options (jobs it leads to)',
        'exams' => 'Entrance / competitive exams', 'scholarships' => 'Scholarships & olympiads', 'school_entrance' => 'School & defence entrance',
        'govt_jobs' => 'Government jobs', 'skills' => 'Skills & courses', 'sports' => 'Sports & activities', 'study_abroad' => 'Study abroad',
        'study_mode' => 'Study mode (regular, online…)', 'switch_stream' => 'Switching streams', 'timeline' => 'Year-by-year plan',
    ];

    /** Item types (everything under a roadmap): which fields and links an item has. */
    public const ITEM_TYPES = [
        'step' => 'Step / phase / path', 'course' => 'Course', 'exam' => 'Exam', 'scholarship' => 'Scholarship', 'career' => 'Career / job',
        'subject' => 'Subject', 'activity' => 'Activity / programme', 'info' => 'Information',
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
        return $this->hasMany(CareerRoadmapSubModule::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }
}
