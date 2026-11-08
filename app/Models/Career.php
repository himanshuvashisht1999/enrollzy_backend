<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A career (doctor, chartered accountant, engineer…): what it is, the route to it from school, its exams, courses and
 * salary. Stream roadmaps list the careers they open; classes show their popular careers (show_for); the website has
 * a page for each (/career-roadmap/careers/{slug}).
 */
class Career extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'category_id', 'summary', 'about', 'stream', 'route', 'exams', 'course_ids', 'salary', 'recruiters', 'faqs',
        'show_for', 'sort_order', 'status', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'route' => 'array',
        'exams' => 'array',
        'course_ids' => 'array',
        'show_for' => 'array',
        'faqs' => 'array',
        'status' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(CareerCategory::class, 'category_id');
    }

    /** Icons the website draws for a career (the roadmap icons). */
    public const ICONS = [
        'med' => 'Medical', 'eng' => 'Engineering / science', 'biz' => 'Business / management', 'ca' => 'Finance / accounts', 'law' => 'Law',
        'des' => 'Design / creative', 'gov' => 'Government', 'def' => 'Defence', 'data' => 'Data / tech', 'arch' => 'Architecture', 'arts' => 'People / sports / teaching',
    ];

    protected static function booted(): void
    {
        static::saving(function (Career $c) {
            if (blank($c->slug)) $c->slug = Str::slug($c->name);
            $base = $c->slug;
            for ($i = 2; static::where('slug', $c->slug)->where('id', '!=', $c->id ?? 0)->exists(); $i++) $c->slug = $base . '-' . $i;
        });
    }
}
