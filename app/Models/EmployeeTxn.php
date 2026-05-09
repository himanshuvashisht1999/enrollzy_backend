<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTxn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_transaction';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function staff()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }
}
