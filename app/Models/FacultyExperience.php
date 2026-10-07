<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyExperience extends Model
{
    use HasFactory;

    protected $table = 'faculty_experiences';

    protected $fillable = [
        'faculty_id',
        'organization',
        'designation',
        'start_date',
        'end_date',
        'currently_working',
        'description',
        'display_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'currently_working' => 'boolean',
        'display_order' => 'integer',
    ];

    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_id');
    }
}
