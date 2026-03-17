<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'site_name', 'logo', 'favicon', 'meta_title', 'meta_description', 
        'meta_keywords', 'contact_email', 'contact_phone', 'address', 'footer_text',
        'hero_title', 'hero_description', 'hero_features', 'hero_cta_1_text', 
        'hero_cta_1_link', 'hero_cta_1_new_tab', 'hero_cta_2_text', 'hero_cta_2_link', 
        'hero_cta_2_new_tab'
    ];
}
