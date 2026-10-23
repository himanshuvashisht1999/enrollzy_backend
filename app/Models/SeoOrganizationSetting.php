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

    public function founders()
    {
        return $this->hasMany(OrganizationFounder::class, 'seo_organization_setting_id')->orderBy('sort_order', 'asc');
    }

    public function generateOrganizationSchema()
    {
        if (!$this->schema_enabled || !$this->organization_schema) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->organization_type ?: 'Organization',
            'name' => $this->organization_name ?: 'Enrollzy',
            'url' => $this->website ?: config('app.url', 'https://enrollzy.com'),
        ];

        if ($this->legal_name) {
            $schema['legalName'] = $this->legal_name;
        }
        if ($this->alternate_name) {
            $schema['alternateName'] = $this->alternate_name;
        }
        if ($this->short_description || $this->long_description) {
            $schema['description'] = $this->short_description ?: $this->long_description;
        }
        if ($this->logo) {
            $schema['logo'] = asset($this->logo);
        }
        if ($this->email) {
            $schema['email'] = $this->email;
        }
        if ($this->phone) {
            $schema['telephone'] = $this->phone;
        }
        if ($this->founding_date) {
            $schema['foundingDate'] = is_string($this->founding_date) ? $this->founding_date : $this->founding_date->format('Y-m-d');
        }

        // Founders list as Person schema array
        $foundersList = [];
        $activeFounders = $this->founders()->where('is_active', true)->orderBy('sort_order')->get();
        
        foreach ($activeFounders as $founder) {
            $person = [
                '@type' => 'Person',
                'name' => $founder->name,
            ];
            if (!empty($founder->job_title)) {
                $person['jobTitle'] = $founder->job_title;
            }
            if (!empty($founder->image)) {
                $person['image'] = asset($founder->image);
            }
            if (!empty($founder->profile_url)) {
                $person['url'] = $founder->profile_url;
            }

            $sameAs = [];
            if (!empty($founder->linkedin_url)) {
                $sameAs[] = $founder->linkedin_url;
            }
            if (!empty($founder->same_as)) {
                $sameAsArray = is_array($founder->same_as) ? $founder->same_as : (json_decode($founder->same_as, true) ?: []);
                $sameAs = array_merge($sameAs, $sameAsArray);
            }
            $sameAs = array_values(array_unique(array_filter($sameAs)));
            if (!empty($sameAs)) {
                $person['sameAs'] = count($sameAs) === 1 ? $sameAs[0] : $sameAs;
            }

            $foundersList[] = $person;
        }

        if (!empty($foundersList)) {
            $schema['founder'] = count($foundersList) === 1 ? $foundersList[0] : $foundersList;
        } elseif (!empty($this->founder_name)) {
            $schema['founder'] = [
                '@type' => 'Person',
                'name' => $this->founder_name,
            ];
        }

        // Address schema
        if ($this->address_line_1 || $this->city || $this->state || $this->country) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => trim($this->address_line_1 . ' ' . $this->address_line_2),
                'addressLocality' => $this->city,
                'addressRegion' => $this->state,
                'postalCode' => $this->postal_code,
                'addressCountry' => $this->country ?: 'IN',
            ];
        }

        // SameAs social links
        $sameAsLinks = array_filter([
            $this->facebook_url,
            $this->instagram_url,
            $this->linkedin_url,
            $this->twitter_url,
            $this->youtube_url,
        ]);
        if (!empty($this->same_as) && is_array($this->same_as)) {
            $sameAsLinks = array_merge($sameAsLinks, $this->same_as);
        }
        $sameAsLinks = array_values(array_unique(array_filter($sameAsLinks)));
        if (!empty($sameAsLinks)) {
            $schema['sameAs'] = $sameAsLinks;
        }

        return $schema;
    }
}
