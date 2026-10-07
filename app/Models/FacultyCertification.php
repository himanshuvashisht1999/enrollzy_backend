<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyCertification extends Model
{
    use HasFactory;

    protected $table = 'faculty_certifications';

    protected $fillable = [
        'faculty_id',
        'certification_name',
        'issuing_organization',
        'issue_date',
        'expiry_date',
        'credential_id',
        'certificate_url',
        'certificate_upload',
        'display_order',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'display_order' => 'integer',
    ];

    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_id');
    }
}
