<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrDepartment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'department';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function parent()
    {
        return $this->belongsTo(HrDepartment::class, 'parent_id');
    }

    public function staff()
    {
        return $this->hasMany(Admin::class, 'department_id');
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'department_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'department_id');
    }
}
