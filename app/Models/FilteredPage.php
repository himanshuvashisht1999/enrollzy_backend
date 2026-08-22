<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilteredPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'sub_title',
        'image',
        'slug',
        'category',
        'ownership_type',
        'school_type_id',
        'curriculum',
        'university_type',
        'degree',
        'stream_id',
        'state',
        'city',
        'coaching_category_id',
        'program_type_id',
    ];

    public function schoolType()
    {
        return $this->belongsTo(CampusTypeNew::class, 'school_type_id');
    }

    public function stream()
    {
        return $this->belongsTo(StreamOffered::class, 'stream_id');
    }
}
