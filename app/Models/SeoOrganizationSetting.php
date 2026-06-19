<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoOrganizationSetting extends Model
{
    use HasFactory;

    protected $table = 'seo_organization_settings';

    protected $fillable = [
        'organization_name', 'legal_name', 'alternate_name',
        'short_description', 'long_description', 'website',
        'logo', 'white_logo', 'dark_logo', 'favicon', 'apple_touch_icon', 'og_image',
        'email', 'phone', 'whatsapp_number', 'support_email',
        'founding_date', 'founder_name', 'organization_type',
        'tax_number', 'gst_number',
        'address_line_1', 'address_line_2', 'city', 'state', 'country', 'postal_code',
        'latitude', 'longitude',
        'opening_hours', 'price_range', 'default_currency',
        'google_map_embed', 'copyright_text', 'copyright_year',
        'facebook_url', 'instagram_url', 'linkedin_url', 'twitter_url', 'youtube_url',
        'same_as', 'search_url',
        'default_og_title', 'default_og_description', 'default_og_image',
        'default_twitter_title', 'default_twitter_description', 'default_twitter_image',
        'ga4_id', 'gtm_id', 'meta_pixel_id', 'linkedin_insight_tag', 'clarity_id', 'schema_enabled',
        'google_site_verification', 'bing_site_verification', 'yandex_verification', 'pinterest_verification', 'facebook_domain_verification',
        'default_robots', 'default_sitemap_priority', 'default_change_frequency',
        'organization_schema', 'search_action_schema', 'website_schema', 'breadcrumb_schema', 'logo_schema', 'social_profile_schema'
    ];

    protected $casts = [
        'same_as' => 'array',
        'founding_date' => 'date',
        'schema_enabled' => 'boolean',
        'organization_schema' => 'boolean',
        'search_action_schema' => 'boolean',
        'website_schema' => 'boolean',
        'breadcrumb_schema' => 'boolean',
        'logo_schema' => 'boolean',
        'social_profile_schema' => 'boolean',
    ];
}
