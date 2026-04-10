<?php

namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'milestones';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
