<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyAchievement extends Model
{
    use HasFactory;

    protected $table = 'faculty_achievements';

    protected $fillable = [
        'faculty_id',
        'title',
        'achievement_type',
        'organization',
        'year',
        'description',
        'certificate_image',
        'verification_status',
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
