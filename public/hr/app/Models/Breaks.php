<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Breaks extends Model
{
    protected $table = 'breaks';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    public function Attendance()
    {
        return $this->hasOne(Attendance::class, 'attendance_id');
    }
}
