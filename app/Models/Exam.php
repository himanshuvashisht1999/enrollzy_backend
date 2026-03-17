<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'name',
        'short_name',
        'slug',
        'logo',
        'cover_image',
        'exam_type',
        'exam_category',
        'conducting_authority_name',
        'conducting_body_type',
        'official_website',
        'about_exam',
        'exam_purpose',
        'exam_source_type',
        'owning_organisation_id',
        'owning_organisation_name',
        'minimum_qualification',
        'minimum_marks_required',
        'subjects_required',
        'minimum_age',
        'maximum_age',
        'attempt_limit',
        'gap_year_allowed',
        'nationality_criteria',
        'reservation_applicable',
        'eligibility_notes',
        'exam_mode',
        'exam_format',
        'total_questions',
        'total_marks',
        'negative_marking',
        'negative_marking_scheme',
        'sections',
        'duration_minutes',
        'languages_available',
        'syllabus_source',
        'syllabus_url',
        'subjects_covered',
        'difficulty_level',
        'recommended_classes',
        'previous_year_question_papers_available',
        'score_type',
        'rank_type',
        'normalization_applied',
        'tie_breaking_rules',
        'score_validity_period',
        'result_format_url',
        'cutoff_type',
        'cutoff_year_wise',
        'cutoff_reference_note',
        'counselling_conducted',
        'counselling_authority',
        'counselling_mode',
        'number_of_rounds',
        'seat_allocation_basis',
        'reservation_policy_reference',
        'official_counselling_website',
        'accepting_organization_count',
        'accepting_organizations_sample',
        'course_types_supported',
        'exam_frequency',
        'first_conducted_year',
        'years_active',
        'exam_discontinued',
        'replaced_by_exam_name',
        'admit_card_download_procedure',
        'result_check_procedure',
        'meta_title',
        'meta_description',
        'focus_keywords',
        'schema_type',
        'canonical_url',
        'indexing_status',
        'breadcrumb_category',
        'official_notification_urls',
        'information_source',
        'last_verified_on',
        'data_confidence_score',
        'disclaimer_text',
        'created_by',
        'updated_by',
        'status',
        'visibility',
        'featured_exam',
        'has_stages',
        'entrance_exam_id',
        'registration_fee_required',
        'registration_fee_structure',
        'late_registration_allowed',
        'late_fee_rules',
        'security_deposit_required',
        'security_deposit_structure',
        'round_specific_fee_rules',
        'refund_policy_summary',
        'refund_timeline',
        'refund_mode',
        'forfeiture_scenarios',
        'payment_modes_allowed',
        'transaction_charges_applicable',
        'transaction_charge_borne_by',
        'payment_gateway_name',
        'partial_refund_allowed',
    ];

    protected $casts = [
        'exam_category' => 'array',
        'subjects_required' => 'array',
        'nationality_criteria' => 'array',
        'sections' => 'array',
        'languages_available' => 'array',
        'subjects_covered' => 'array',
        'recommended_classes' => 'array',
        'cutoff_year_wise' => 'array',
        'accepting_organizations_sample' => 'array',
        'course_types_supported' => 'array',
        'years_active' => 'array',
        'focus_keywords' => 'array',
        'official_notification_urls' => 'array',
        'gap_year_allowed' => 'boolean',
        'reservation_applicable' => 'boolean',
        'negative_marking' => 'boolean',
        'previous_year_question_papers_available' => 'boolean',
        'normalization_applied' => 'boolean',
        'counselling_conducted' => 'boolean',
        'exam_discontinued' => 'boolean',
        'featured_exam' => 'boolean',
        'last_verified_on' => 'date',
        'registration_fee_required' => 'boolean',
        'registration_fee_structure' => 'array',
        'late_registration_allowed' => 'boolean',
        'late_fee_rules' => 'array',
        'security_deposit_required' => 'boolean',
        'security_deposit_structure' => 'array',
        'round_specific_fee_rules' => 'array',
        'forfeiture_scenarios' => 'array',
        'payment_modes_allowed' => 'array',
        'transaction_charges_applicable' => 'boolean',
        'has_stages' => 'boolean',
        'partial_refund_allowed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($exam) {
            if (empty($exam->exam_id)) {
                $exam->exam_id = (string) Str::uuid();
            }
            if (empty($exam->slug)) {
                $slug = Str::slug($exam->name);
                $originalSlug = $slug;
                $count = 1;
                while (Exam::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $exam->slug = $slug;
            }
        });
    }

    // Relationships
    public function sessions()
    {
        return $this->hasMany(ExamSession::class);
    }

    public function owningOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'owning_organisation_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function counsellings()
    {
        return $this->hasMany(Counselling::class);
    }

    public function selectedStages()
    {
        return $this->hasMany(ExamSelectedStage::class)->orderBy('sort_order');
    }

    public function interviewStage()
    {
        return $this->hasOne(ExamStageInterview::class);
    }
}
