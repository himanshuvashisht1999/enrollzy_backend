<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicExam extends Model
{
    protected $fillable = [
        'name', 'slug', 'status', 'visibility',
        'short_name', 'exam_type', 'exam_category', 'conducting_body_type',
        'exam_frequency', 'conducting_authority_name', 'logo', 'cover_image',
        'exam_source_type', 'owning_organisation_id', 'about_exam',
        'official_website', 'featured_exam', 'has_stages', 'selected_stages'
    ];

    protected $casts = [
        'exam_category' => 'array',
        'featured_exam' => 'boolean',
        'has_stages' => 'boolean',
        'selected_stages' => 'array',
    ];

    public function sections()
    {
        return $this->hasMany(DynamicExamSection::class)->orderBy('order', 'asc');
    }

    public function counsellings()
    {
        return $this->hasMany(Counselling::class);
    }
}
