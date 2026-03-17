<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'status', 'program_level_id', 'stream_offered_id', 'discipline_id', 'duration', 'sort_order'];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->name);
            }
        });
    }

    public function universityCourses()
    {
        return $this->hasMany(UniversityCourse::class);
    }

    public function programLevel()
    {
        return $this->belongsTo(ProgramLevel::class, 'program_level_id');
    }

    public function streamOffered()
    {
        return $this->belongsTo(StreamOffered::class, 'stream_offered_id');
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }
}
