<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyPublication extends Model
{
    use HasFactory;

    protected $table = 'faculty_publications';

    protected $fillable = [
        'faculty_id',
        'title',
        'publication_type',
        'journal_conference',
        'publisher',
        'publication_date',
        'doi',
        'url',
        'authors',
        'citation_count',
        'document_pdf',
        'verified',
        'display_order',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'citation_count' => 'integer',
        'verified' => 'boolean',
        'display_order' => 'integer',
    ];

    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_id');
    }
}
