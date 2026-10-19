<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Exam extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'exm_exams';

    protected $fillable = [
        'name', 'short_name', 'slug', 'aliases', 'source_type', 'owning_organisation_id',
        'conducting_body_id', 'exam_type', 'exam_categories', 'frequency', 'program_levels',
        'states', 'feeder_exam_id', 'languages', 'about', 'official_website', 'logo',
        'has_stages', 'visibility', 'status', 'cover_image', 'featured', 'sort_order', 'is_archived',
    ];

    protected $casts = [
        'aliases' => 'array', 'exam_categories' => 'array', 'program_levels' => 'array',
        'states' => 'array', 'languages' => 'array', 'has_stages' => 'boolean', 'status' => 'boolean', 'featured' => 'boolean', 'is_archived' => 'boolean',
    ];

    public const TYPES = ['national' => 'National', 'state' => 'State', 'university' => 'University',
        'international' => 'International', 'school' => 'School', 'internal' => 'Internal (college)'];

    public const FREQUENCIES = ['yearly' => 'Yearly', 'twice_a_year' => 'Twice a year', 'quarterly' => 'Quarterly',
        'monthly' => 'Monthly', 'on_demand' => 'On demand'];

    public const STATES = ['Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana',
        'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry'];

    protected static function booted(): void
    {
        static::saving(function (Exam $exam) {
            // Backend rule: an external exam never has an owning organisation.
            if ($exam->source_type === 'external') {
                $exam->owning_organisation_id = null;
            }
            if ($exam->exam_type !== 'state') {
                $exam->states = null;
            }
            if (empty($exam->slug)) {
                $exam->slug = static::uniqueSlug($exam->name, $exam->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'exam';
        $slug = $base;
        $i = 2;
        while (static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /** Fields still missing before this exam may be set Public. */
    public function missingForPublish(): array
    {
        $missing = [];
        $checks = [
            'Slug' => $this->slug, 'Exam type' => $this->exam_type, 'Category' => $this->exam_categories,
            'Program level' => $this->program_levels, 'About' => $this->about, 'Official website' => $this->official_website,
        ];
        foreach ($checks as $label => $value) {
            if (blank($value)) {
                $missing[] = $label;
            }
        }
        if ($this->source_type === 'internal' ? blank($this->owning_organisation_id) : blank($this->conducting_body_id)) {
            $missing[] = $this->source_type === 'internal' ? 'Owning organisation' : 'Conducting body';
        }
        return $missing;
    }

    public function conductingBody()
    {
        return $this->belongsTo(ConductingBody::class, 'conducting_body_id');
    }

    public function owningOrganisation()
    {
        return $this->belongsTo(\App\Models\Organisation::class, 'owning_organisation_id');
    }

    public function feederExam()
    {
        return $this->belongsTo(self::class, 'feeder_exam_id');
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class, 'exam_id')->orderBy('sort_order');
    }

    public function cycles()
    {
        return $this->hasMany(Cycle::class, 'exam_id')->orderByDesc('year')->orderByDesc('id');
    }

    public function currentCycle()
    {
        return $this->hasOne(Cycle::class, 'exam_id')->where('is_current', true);
    }
}
