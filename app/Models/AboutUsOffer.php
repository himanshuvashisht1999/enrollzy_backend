<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUsOffer extends Model
{
    protected $fillable = [
        'icon_image', 'title', 'description', 'sort_order'
    ];
}
