<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoHomepage extends Model
{
    use HasFactory;

    protected $table = 'seo_homepage';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'focus_keyword',
        'secondary_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'breadcrumb_title',
        'ai_summary',
        'hero_title',
        'hero_subtitle',
        'hero_description',
        'hero_cta_text',
        'hero_cta_link',
        'featured_image',
        'schema_type',
        'custom_schema_json',
        'allow_index',
        'allow_snippet',
        'allow_image_preview',
        'allow_video_preview',
        'sitemap_priority',
        'change_frequency',
    ];

    protected $casts = [
        'secondary_keywords' => 'array',
        'custom_schema_json' => 'array',
        'allow_index' => 'boolean',
        'allow_snippet' => 'boolean',
        'allow_image_preview' => 'boolean',
        'allow_video_preview' => 'boolean',
    ];
}
