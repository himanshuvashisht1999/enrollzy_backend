<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyQualification extends Model
{
    use HasFactory;

    protected $table = 'faculty_qualifications';

    protected $fillable = [
        'faculty_id',
        'degree',
        'specialization',
        'institution',
        'university',
        'year',
        'grade_percentage',
        'certificate_document',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_id');
    }
}
