<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyDepartmentMapping extends Model
{
    use HasFactory;

    protected $table = 'faculty_department_mapping';

    protected $fillable = [
        'faculty_id',
        'organisation_id',
        'campus_id',
        'department_id',
        'designation_id',
        'joining_date',
        'is_primary_department',
        'status',
    ];

    protected $casts = [
        'is_primary_department' => 'boolean',
        'status' => 'boolean',
        'joining_date' => 'date',
    ];

    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(FacultyDesignation::class, 'designation_id');
    }
}
