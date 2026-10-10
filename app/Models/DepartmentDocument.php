<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'sort_order',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset($this->file_path) : null;
    }
}
