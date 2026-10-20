<?php

namespace App\Models\Quality;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** A brand that owns several legally separate universities (Amity, SRM...). Grouping never merges their records. */
class OrganisationGroup extends Model
{
    use Auditable;

    protected $table = 'organisation_groups';
    protected $fillable = ['name', 'slug', 'website', 'notes', 'status'];
    protected $casts = ['status' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (OrganisationGroup $g) {
            if (!$g->slug || $g->isDirty('name') && !$g->isDirty('slug')) {
                $base = Str::slug($g->name) ?: 'group';
                $slug = $base; $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $g->id ?? 0)->exists()) $slug = $base . '-' . $i++;
                $g->slug = $slug;
            }
        });
    }

    public function organisations() { return $this->hasMany(Organisation::class, 'group_id'); }
}
