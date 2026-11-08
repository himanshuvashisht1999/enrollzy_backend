<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An expertise an expert can have (Career Guidance, Study Abroad...): the list is managed in the CRM. */
class ExpertiseArea extends Model
{
    protected $table = 'expert_expertise_areas';
    protected $fillable = ['name', 'sort_order', 'status'];
    protected $casts = ['status' => 'boolean'];

    /** active names in the admin's order (what forms offer and the website filters by) */
    public static function names(): array
    {
        return static::where('status', true)->orderBy('sort_order')->orderBy('name')->pluck('name')->all();
    }
}
