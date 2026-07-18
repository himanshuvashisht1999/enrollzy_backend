<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUsImpact extends Model
{
    protected $fillable = [
        'icon_image', 'count_text', 'label', 'sort_order'
    ];
}
