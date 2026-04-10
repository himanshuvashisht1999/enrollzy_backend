<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'policy', 'department_ids', 'staff_ids','designation_ids','organization_id'];
}
