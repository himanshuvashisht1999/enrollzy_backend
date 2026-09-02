<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationFounder extends Model
{
    use HasFactory;

    protected $table = 'organization_founders';

    protected $fillable = [
        'seo_organization_setting_id',
        'organization_id',
        'name',
        'job_title',
        'image',
        'profile_url',
        'linkedin_url',
        'same_as',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'same_as' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function seoOrganizationSetting()
    {
        return $this->belongsTo(SeoOrganizationSetting::class, 'seo_organization_setting_id');
    }
}