<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoDefault extends Model
{
    use HasFactory;

    protected $table = 'seo_defaults';

    protected $fillable = [
        'default_meta_title',
        'default_meta_description',
        'default_og_image',
        'default_twitter_image',
        'default_robots',
        'default_schema_type',
        'default_author',
        'default_publisher',
        'default_language',
        'default_country',
        'separator',
        'title_format',
    ];
}
