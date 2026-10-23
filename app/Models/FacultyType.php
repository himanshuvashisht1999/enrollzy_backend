<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyType extends Model
{
    use HasFactory;

    protected $table = 'faculty_types';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function profiles()
    {
        return $this->hasMany(FacultyProfile::class, 'faculty_type_id');
    }
}
