<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Expert extends Authenticatable
{
    use Notifiable;

    public function availability_slots()
    {
        return $this->morphMany(AvailabilitySlot::class, 'provider');
    }
    /** The website's experts list: filter choices, fixed so the filters match (the site keeps the same lists). */
    public const EXPERTISE_AREAS = ['Career Guidance', 'School Admissions', 'University Admissions', 'Competitive Exams', 'Study Abroad', 'Scholarships', 'Online Education'];
    public const LEVELS = ['Class 1–4', 'Class 5–8', 'Class 9–10', 'Class 11–12', 'UG / PG'];
    public const CONSULTATION = ['Online', 'In-person'];
    // suggestions in the Identity tab (any other value can be typed); LANGUAGES is the website application's list
    public const DESIGNATIONS = ['Career counsellor', 'Senior faculty', 'Faculty', 'HOD', 'Mentor', 'Principal', 'Admissions consultant', 'Psychologist', 'Industry professional'];
    public const SUBJECTS = ['Physics', 'Chemistry', 'Biology', 'Maths', 'English', 'Computer Science', 'Commerce', 'Economics', 'Humanities', 'Psychology'];
    public const LANGUAGES = ['English', 'Hindi', 'Bengali', 'Marathi', 'Telugu', 'Tamil', 'Gujarati', 'Urdu', 'Kannada', 'Odia', 'Malayalam', 'Punjabi'];
    // the Content tab's social profiles: field => label
    public const SOCIAL = ['linkedin_profile_url' => 'LinkedIn', 'youtube_channel_url' => 'YouTube', 'instagram_profile_url' => 'Instagram', 'telegram_channel_url' => 'Telegram'];

    // the website's expert profile ("Profile page" tab of the form): yes/no fields => how sessions work
    public const APPROACH = [
        'one_on_one_counseling' => '1:1 counselling', 'group_counseling' => 'Group sessions', 'psychometric_based_counseling' => 'Psychometric assessment',
        'data_driven_career_mapping' => 'Data-based career mapping', 'goal_oriented_planning' => 'Goal-based planning', 'flexible_scheduling' => 'Flexible timings',
    ];
    // text fields => [label, example]
    public const TRACK_RECORD = [
        'exam_success_rate' => ['Exam success rate', 'e.g. 92% qualified JEE Main'],
        'students_admitted_to_top_university' => ['Admitted to top colleges', 'e.g. 140+ at NITs and IIITs'],
        'scholarship_conversion_rate' => ['Scholarships won', 'e.g. 35 students, ₹40 lakh in total'],
        'career_placement_outcomes' => ['Career outcomes', 'e.g. 60+ now at core engineering firms'],
        'repeat_counseling_rate' => ['Families who came back', 'e.g. 1 in 3 book a second session'],
    ];
    public const BEYOND = [
        'conference_talks' => ['Talks & events', 'e.g. Speaker, Bihar Career Fair 2023'],
        'research_publications' => ['Research & publications', 'e.g. Paper on JEE preparation habits, 2022'],
        'consulting_advisory_roles' => ['Advisory roles', 'e.g. Admissions advisor, Vishal Academy'],
        'curriculum_design_experience' => ['Curriculum work', 'e.g. Designed the 2-year JEE Physics plan'],
        'patents' => ['Patents', 'e.g. Patent no., title, year'],
    ];
    // (university_admission_office_access, "Admission offices", is no longer asked or shown)
    public const NETWORK = [
        'alumni_recruiter_connections' => ['Alumni & recruiters', 'e.g. Alumni network of 300+ engineers'],
        'industry_connection' => ['Industry', 'e.g. Power sector, core electrical firms'],
    ];

    protected $fillable = [
        'career_category_ids', 'career_class_ids', 'show_on_career_pages',
        'city', 'state', 'helps_with', 'expertise_areas', 'levels', 'offers_free_intro', 'free_intro_minutes',
        'name',
        'email',
        'price_per_min',
        'password',
        'role',
        'expert_category_id',
        'degree',
        'exp',
        'rating',
        'count',
        'img',
        'highest_qualification',
        'domain_certification',
        'teaching_credentials',
        'industry_licenses',
        'primary_domain',
        'sub_specialization',
        'years_of_domain_experience',
        'academic_vs_industry_expertise',
        'total_counseling_experience',
        'no_of_students_counseled',
        'counseling_specialization',
        'students_admitted_to_top_university',
        'exam_success_rate',
        'scholarship_conversion_rate',
        'career_placement_outcomes',
        'years_of_industry_experience',
        'current_past_employer_quality',
        'consulting_advisory_roles',
        'live_industry_project_exposure',
        'one_on_one_counseling',
        'group_counseling',
        'psychometric_based_counseling',
        'data_driven_career_mapping',
        'goal_oriented_planning',
        'session_modes',
        'languages_supported',
        'average_wait_time',
        'session_duration',
        'flexible_scheduling',
        'academic_network_reach',
        'industry_connection',
        'university_admission_office_access',
        'alumni_recruiter_connections',
        'feedback_sentiment_score',
        'verified_counseling_reviews',
        'repeat_counseling_rate',
        'research_publications',
        'patents',
        'conference_talks',
        'curriculum_design_experience',
        // New Fields
        'faculty_id', 'slug', 'profile_photo_url', 'cover_photo_url', 'gender', 'date_of_birth', 'short_bio', 'detailed_bio',
        'designation', 'subject_specialization',
        'other_qualifications', 'certifications', 'years_of_experience_total', 'years_of_experience_current_institute',
        'previous_institutes', 'industry_experience', 'exams_cleared', 'notable_achievements',
        'current_institute_id', 'current_institute_name', 'faculty_type', 'joining_year', 'courses_taught', 'target_batches',
        'average_batch_size_handled',
        'teaching_style', 'language_of_teaching', 'lecture_mode', 'weekly_classes_count', 'doubt_solving_sessions', 'one_to_one_mentoring',
        'years_with_results', 'students_selected_count', 'top_rank_students', 'best_result_year', 'result_verification_source', 'average_student_feedback_rating',
        'intro_video_url', 'demo_lecture_videos', 'articles_written', 'youtube_channel_url', 'linkedin_profile_url', 'instagram_profile_url', 'telegram_channel_url', 'website_url',
        'total_reviews', 'verified_student_reviews_only', 'student_testimonials', 'peer_reviews', 'awards_recognition',
        'contact_number', 'public_contact_allowed', 'profile_visibility', 'profile_claimed', 'verification_status',
        'meta_title', 'meta_description', 'focus_keywords', 'schema_type', 'canonical_url', 'indexing_status',
        'data_source', 'confidence_score', 'last_updated_on', 'status', 'is_featured'
    ];

    protected $casts = [
        'career_category_ids' => 'array',
        'career_class_ids' => 'array',
        'show_on_career_pages' => 'boolean',
        'counseling_specialization' => 'array',
        'session_modes' => 'array',
        'expertise_areas' => 'array',
        'levels' => 'array',
        'offers_free_intro' => 'boolean',
        'languages_supported' => 'array',
        'one_on_one_counseling' => 'boolean',
        'group_counseling' => 'boolean',
        'psychometric_based_counseling' => 'boolean',
        'data_driven_career_mapping' => 'boolean',
        'goal_oriented_planning' => 'boolean',
        'flexible_scheduling' => 'boolean',
        'password' => 'hashed',
        // New Casts
        'subject_specialization' => 'array',
        'other_qualifications' => 'array',
        'certifications' => 'array',
        'previous_institutes' => 'array',
        'exams_cleared' => 'array',
        'notable_achievements' => 'array',
        'courses_taught' => 'array',
        'target_batches' => 'array',
        'language_of_teaching' => 'array',
        'years_with_results' => 'array',
        'top_rank_students' => 'array',
        'demo_lecture_videos' => 'array',
        'articles_written' => 'array',
        'student_testimonials' => 'array',
        'peer_reviews' => 'array',
        'awards_recognition' => 'array',
        'focus_keywords' => 'array',
        
        'industry_experience' => 'boolean',
        'doubt_solving_sessions' => 'boolean',
        'one_to_one_mentoring' => 'boolean', // Overrides previous 'one_on_one_counseling' or separate? 'one_on_one_counseling' is existing. User asked for 'one_to_one_mentoring'. I added 'one_to_one_mentoring' in migration.
        'verified_student_reviews_only' => 'boolean',
        'public_contact_allowed' => 'boolean',
        'profile_claimed' => 'boolean',

        'date_of_birth' => 'date',
        'last_updated_on' => 'datetime',
    ];
    
    // Boot method to auto-generate UUID if needed
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->faculty_id)) {
                $model->faculty_id = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
             if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
            }
        });
    }

    public function expertCategory()
    {
        return $this->belongsTo(ExpertCategory::class);
    }

    public function slots()
    {
        return $this->hasMany(ExpertSlot::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payouts()
    {
        // expert payouts (payouts table); Payout is staff payroll
        return $this->hasMany(ExpertPayout::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
