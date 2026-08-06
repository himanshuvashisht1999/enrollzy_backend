<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlider extends Model
{
    protected $fillable = [
        'image_path', 'image_type', 'heading', 'subheading', 
        'button_text', 'button_url', 'sort_order', 'is_active',
        'badge_text', 'stat_1_count', 'stat_1_label', 'stat_2_count', 
        'stat_2_label', 'stat_3_count', 'stat_3_label',
        'pill_1_label', 'pill_1_url', 'pill_2_label', 'pill_2_url',
        'pill_3_label', 'pill_3_url', 'pill_4_label', 'pill_4_url'
    ];
}
