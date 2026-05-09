<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    public function breaks()
    {
        return $this->hasMany(Breaks::class, 'attendance_id');
    }

    public function staff()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }
}
