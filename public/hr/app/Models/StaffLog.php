<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffLog extends Model
{
    protected $table = 'staff_logs';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
}
