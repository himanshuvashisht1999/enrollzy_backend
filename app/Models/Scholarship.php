<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scholarship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'short_name', 'display_title', 'scholarship_code',
        'short_description', 'overview', 'about_scholarship', 'why_apply',
        'selection_process', 'terms_conditions', 'important_notes', 'additional_information',
        'featured_image', 'banner_image', 'banner_title', 'banner_subtitle',
        'card_icon', 'card_background_color', 'card_text_color',
        'scholarship_type', 'category', 'max_amount', 'amount_prefix', 'amount_suffix',
        'provider_name', 'provider_logo', 'provider_url',
        'application_mode', 'status', 'featured', 'featured_on_homepage',
        'sort_order', 'cta_text', 'cta_url', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'status' => 'integer',
        'featured' => 'integer',
        'featured_on_homepage' => 'integer',
        'sort_order' => 'integer'
    ];

    public function eligibility()
    {
        return $this->hasOne(ScholarshipEligibility::class);
    }

    public function benefits()
    {
        return $this->hasMany(ScholarshipBenefit::class)->orderBy('sort_order');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'scholarship_courses');
    }

    public function universities()
    {
        return $this->belongsToMany(Organisation::class, 'scholarship_universities', 'scholarship_id', 'organisation_id');
    }

    public function documents()
    {
        return $this->hasMany(ScholarshipDocument::class);
    }

    public function dates()
    {
        return $this->hasOne(ScholarshipDate::class);
    }

    public function faqs()
    {
        return $this->hasMany(ScholarshipFaq::class)->orderBy('sort_order');
    }

    public function gallery()
    {
        return $this->hasMany(ScholarshipGallery::class)->orderBy('sort_order');
    }

    public function highlights()
    {
        return $this->hasMany(ScholarshipHighlight::class)->orderBy('sort_order');
    }

    // Auto-derive application status from dates
    public function getApplicationStatusAttribute(): string
    {
        $dates = $this->dates;
        if (!$dates) return 'Unknown';

        $today = now()->startOfDay();

        if ($dates->result_date && $today->gt($dates->result_date)) {
            return 'Result Declared';
        }
        if ($dates->application_end_date && $today->gt($dates->application_end_date)) {
            return 'Closed';
        }
        if ($dates->application_end_date && $today->diffInDays($dates->application_end_date, false) <= 7 && $today->lte($dates->application_end_date)) {
            return 'Closing Soon';
        }
        if ($dates->application_start_date && $today->gte($dates->application_start_date)) {
            return 'Open';
        }
        if ($dates->application_start_date && $today->lt($dates->application_start_date)) {
            return 'Upcoming';
        }

        return 'Open';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function seo()
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}

