<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasRoles;

    protected $guard_name = 'admin'; // Important line

    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'staff_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }
}
