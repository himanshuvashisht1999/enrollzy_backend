<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'site_name', 'logo', 'favicon', 'meta_title', 'meta_description', 'meta_keywords',
        'contact_email', 'contact_phone', 'address', 'footer_text',
        'hero_title', 'hero_description', 'hero_features', 'hero_cta_1_text', 'hero_cta_1_link', 'hero_cta_1_new_tab',
        'hero_cta_2_text', 'hero_cta_2_link', 'hero_cta_2_new_tab', 'is_show_full_banner',
        'footer_description', 'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 'footer_general_title',
        'youtube_url', 'play_store_link', 'app_store_link', 'toll_free_number', 'whatsapp_number',
        'footer_qr_image'
    ];
}
