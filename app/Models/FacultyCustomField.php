<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyCustomField extends Model
{
    use HasFactory;

    protected $table = 'faculty_custom_fields';

    protected $fillable = [
        'faculty_id',
        'institution_type',
        'field_key',
        'field_label',
        'field_type',
        'field_value',
    ];

    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_id');
    }
}
