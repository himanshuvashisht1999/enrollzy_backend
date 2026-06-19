<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoHomepageSchemaBlock extends Model
{
    use HasFactory;

    protected $table = 'seo_homepage_schema_blocks';

    protected $fillable = [
        'schema_type',
        'json_data',
        'status',
    ];

    protected $casts = [
        'json_data' => 'array',
        'status' => 'boolean',
    ];
}
