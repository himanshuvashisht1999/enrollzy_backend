<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/** Shared category master: used by exam fees/eligibility and by counselling fees, seats and cutoffs. */
class Category extends Model
{
    use Auditable;

    protected $table = 'cns_categories';
    protected $fillable = ['name', 'short_name', 'slug', 'scope', 'states', 'is_pwd_subcategory', 'sort_order', 'status'];
    protected $casts = ['states' => 'array', 'is_pwd_subcategory' => 'boolean', 'status' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Category $c) {
            if ($c->scope !== 'state') {
                $c->states = null;
            }
            if (blank($c->slug)) {
                $base = \Illuminate\Support\Str::slug($c->name, '_') ?: 'category';
                $slug = $base;
                for ($i = 2; static::where('slug', $slug)->where('id', '!=', $c->id ?? 0)->exists(); $i++) {
                    $slug = $base . '_' . $i;
                }
                $c->slug = $slug;
            }
        });
    }
}
