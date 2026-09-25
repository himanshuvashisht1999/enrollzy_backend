<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationElearning extends Model
{
    protected $table = 'organisation_elearnings';

    protected $fillable = [
        'organisation_id',
        'tagline',
        'legal_name',
        'parent_organisation_name',
        'organisation_model',
        'platform_types',
        'target_audiences',
        'credential_types_offered',
        'provides_own_programs',
        'program_provider_model',
        'delivery_modes',
        'learning_features',
        'platform_features',
        'career_services',
        'career_stats',
        'learner_stats',
        'geographic_reach',
        'instruction_languages',
        'business_model',
        'pricing_options',
        'has_enterprise_offering',
        'corporate_services',
        'third_party_ratings',
        'industry_recognitions',
        'promotional_video_url',
        'contact_details',
        'social_media_links',
        'app_details',
        'community_details',
        'financial_aid_details',
        'enrollment_details',
        'document_urls',
    ];

    protected $casts = [
        'platform_types' => 'array',
        'target_audiences' => 'array',
        'credential_types_offered' => 'array',
        'delivery_modes' => 'array',
        'learning_features' => 'array',
        'platform_features' => 'array',
        'career_services' => 'array',
        'career_stats' => 'array',
        'learner_stats' => 'array',
        'geographic_reach' => 'array',
        'instruction_languages' => 'array',
        'pricing_options' => 'array',
        'has_enterprise_offering' => 'boolean',
        'corporate_services' => 'array',
        'third_party_ratings' => 'array',
        'industry_recognitions' => 'array',
        'contact_details' => 'array',
        'social_media_links' => 'array',
        'app_details' => 'array',
        'community_details' => 'array',
        'financial_aid_details' => 'array',
        'enrollment_details' => 'array',
        'document_urls' => 'array',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
