<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUsPage extends Model
{
    protected $fillable = [
        'hero_title', 'hero_subtitle', 'hero_description', 'hero_image',
        'hero_tagline', 'simplify_decisions_image',
        'story_title', 'story_subtitle', 'story_description', 'story_purpose_text', 'story_image',
        'offers_title', 'offers_subtitle', 'offers_description',
        'features_title', 'features_subtitle',
        'cta_title', 'cta_description', 'cta_button_1_text', 'cta_button_1_link',
        'cta_button_2_text', 'cta_button_2_link', 'cta_image',
        'mission_text', 'vision_text', 'philosophy_text',
        'mission_image', 'vision_image', 'philosophy_image',
        'founder_1_name', 'founder_1_title', 'founder_1_image', 'founder_1_facebook', 'founder_1_linkedin', 'founder_1_twitter', 'founder_1_message',
        'founder_2_name', 'founder_2_title', 'founder_2_image', 'founder_2_facebook', 'founder_2_linkedin', 'founder_2_twitter', 'founder_2_message',
        'founders_common_message', 'section_orders',
        'impacts_title', 'founders_title', 'team_title', 'team_subtitle', 'advisory_title', 'advisory_subtitle'
    ];

    protected $casts = [
        'section_orders' => 'array',
    ];
}
