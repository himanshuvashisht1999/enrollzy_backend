<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampusDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'sort_order',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset($this->file_path) : null;
    }
}
