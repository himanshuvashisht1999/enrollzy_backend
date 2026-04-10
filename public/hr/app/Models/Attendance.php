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
        return $this->hasMany(Breaks::class); // or whatever relationship type applies
    }
}
