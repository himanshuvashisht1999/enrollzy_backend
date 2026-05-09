<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlider extends Model
{
    protected $fillable = [
        'image_path', 'image_type', 'heading', 'subheading', 
        'button_text', 'button_url', 'sort_order', 'is_active'
    ];
}
