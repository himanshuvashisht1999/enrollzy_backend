<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_types';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function projects()
    {
        return $this->hasMany(Project::class, 'project_type_id');
    }

    public function getBadgeClass()
    {
        $colorMap = [
            'primary' => 'bg-soft-primary text-primary border-primary',
            'success' => 'bg-soft-success text-success border-success',
            'info' => 'bg-soft-info text-info border-info',
            'warning' => 'bg-soft-warning text-warning border-warning',
            'danger' => 'bg-soft-danger text-danger border-danger',
            'dark' => 'bg-soft-dark text-dark border-dark',
            'secondary' => 'bg-soft-secondary text-secondary border-secondary',
        ];

        return $colorMap[$this->color] ?? 'bg-soft-primary text-primary border-primary';
    }
}
