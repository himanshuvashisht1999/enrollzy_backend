<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $table = 'declared_holidays';
    protected $fillable = [
        'name',
        'date',
        'description',
        'department_ids',
        'designation_ids',
        'staff_ids',
        'organization_id',
    ];
}
