<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUsFeature extends Model
{
    protected $fillable = [
        'icon_image', 'title', 'description', 'sort_order'
    ];
}
