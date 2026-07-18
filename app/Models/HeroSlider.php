<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlider extends Model
{
    protected $fillable = [
        'image_path', 'image_type', 'heading', 'subheading', 
        'button_text', 'button_url', 'sort_order', 'is_active',
        'badge_text', 'stat_1_count', 'stat_1_label', 'stat_2_count', 
        'stat_2_label', 'stat_3_count', 'stat_3_label'
    ];
}
