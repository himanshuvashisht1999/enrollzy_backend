<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScholarshipDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id', 'application_start_date', 'application_end_date', 'exam_date',
        'result_date', 'document_verification_date', 'admission_date'
    ];

    protected $casts = [
        'application_start_date' => 'date',
        'application_end_date' => 'date',
        'exam_date' => 'date',
        'result_date' => 'date',
        'document_verification_date' => 'date',
        'admission_date' => 'date'
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
