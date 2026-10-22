<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\ExamModule\ConductingBody;
use App\Models\ExamModule\Exam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/** Permanent identity of an admission counselling (MCC NEET-UG, JoSAA...). Years live in CounsellingCycle. */
class Counselling extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'cns_counsellings';

    protected $fillable = [
        'name', 'short_name', 'slug', 'aliases', 'conducting_body_id', 'conducting_organisation_id', 'level', 'states', 'owning_organisation_id',
        'program_levels', 'course_categories', 'mode', 'official_website', 'registration_portal_url', 'helpline',
        'logo', 'cover_image', 'about', 'visibility', 'is_active', 'is_archived', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'aliases' => 'array', 'states' => 'array', 'program_levels' => 'array', 'course_categories' => 'array',
        'is_active' => 'boolean', 'is_archived' => 'boolean', 'is_featured' => 'boolean',
    ];

    public const LEVELS = ['national' => 'National', 'state' => 'State', 'university' => 'University', 'institute' => 'Institute'];
    public const MODES = ['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'];

    protected static function booted(): void
    {
        static::saving(function (Counselling $c) {
            // Backend rules from the spec: states only for state level, owner only for university/institute.
            if ($c->level !== 'state') {
                $c->states = null;
            }
            if (!in_array($c->level, ['university', 'institute'])) {
                $c->owning_organisation_id = null;
            }
            if (blank($c->slug)) {
                $c->slug = static::uniqueSlug($c->name, $c->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'counselling';
        $slug = $base;
        for ($i = 2; static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists(); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }

    /** "State: Karnataka · Karnataka Examinations Authority" for admin lists; parts that are not set are left out (never "-"). */
    public function summaryLine(): string
    {
        $level = self::LEVELS[$this->level] ?? ucfirst((string) $this->level);
        if ($this->level === 'state' && $this->states) {
            $level .= ': ' . implode(', ', $this->states);
        }
        return implode(' · ', array_filter([$level, $this->conductingBody?->name]));
    }

    /** What is still missing before this counselling may be Public. */
    public function missingForPublish(): array
    {
        $missing = [];
        foreach (['Slug' => $this->slug, 'Conducting body' => $this->conducting_organisation_id, 'Program level' => $this->program_levels,
            'About' => $this->about, 'Official website' => $this->official_website] as $label => $value) {
            if (blank($value)) $missing[] = $label;
        }
        if ($this->level === 'state' && blank($this->states)) $missing[] = 'States';
        if (in_array($this->level, ['university', 'institute']) && blank($this->owning_organisation_id)) $missing[] = 'Owning organisation';
        if ($this->exists && !$this->mappings()->exists()) $missing[] = 'At least one mapped exam';
        return $missing;
    }

    /** The conducting body: an organisation of type "Counselling Body" (Organisation::TYPE_COUNSELLING_BODY). */
    public function conductingBody()
    {
        return $this->belongsTo(\App\Models\Organisation::class, 'conducting_organisation_id');
    }

    /** The value from the old separate conducting-body master (kept, shown only as a hint to re-pick). */
    public function legacyConductingBody()
    {
        return $this->belongsTo(ConductingBody::class, 'conducting_body_id');
    }

    public function owningOrganisation()
    {
        return $this->belongsTo(\App\Models\Organisation::class, 'owning_organisation_id');
    }

    public function mappings()
    {
        return $this->hasMany(CounsellingExam::class, 'counselling_id')->orderBy('sort_order');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'cns_counselling_exams', 'counselling_id', 'exam_id')
            ->withPivot(['role', 'valid_from_year', 'valid_to_year', 'seat_scope_note', 'sort_order']);
    }

    public function cycles()
    {
        return $this->hasMany(CounsellingCycle::class, 'counselling_id')->orderByDesc('year')->orderByDesc('id');
    }

    public function currentCycle()
    {
        return $this->hasOne(CounsellingCycle::class, 'counselling_id')->where('is_current', true);
    }
}
