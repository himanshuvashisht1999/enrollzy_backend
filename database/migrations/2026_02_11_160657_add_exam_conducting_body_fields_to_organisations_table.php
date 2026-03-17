<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new organisation types
        DB::table('organisation_types')->insertOrIgnore([
            ['id' => 5, 'title' => 'Exam Conducting Body', 'status' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'title' => 'Counselling Body', 'status' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'title' => 'Regulatory Body', 'status' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('organisations', function (Blueprint $table) {
            // Core Identity
            $table->uuid('exam_conducting_body_id')->nullable()->after('school_id');
            $table->string('abbreviation')->nullable()->after('short_name');
            $table->text('mandate_description')->nullable();

            // Legal & Constitutional Status
            $table->string('authority_type')->nullable();
            $table->string('parent_ministry')->nullable();
            $table->string('established_by')->nullable();
            $table->string('legal_act_reference')->nullable();
            $table->string('headquarters_location')->nullable();
            $table->string('jurisdiction_scope')->nullable();

            // Functions & Responsibilities
            $table->json('functions')->nullable();
            $table->json('exam_types_conducted')->nullable();
            $table->json('evaluation_methods')->nullable();

            // Exams Owned / Conducted
            $table->json('exams_conducted_ids')->nullable();
            $table->string('annual_exam_volume_estimate')->nullable();
            $table->string('average_candidates_per_year')->nullable();

            // Operational Capabilities
            $table->json('exam_modes_supported')->nullable();
            $table->boolean('question_bank_managed')->default(false);
            $table->boolean('normalization_process_available')->default(false);
            $table->boolean('multi_language_support')->default(false);
            $table->boolean('remote_proctoring_supported')->default(false);

            // Infrastructure & Partners
            $table->string('exam_centres_management_type')->nullable();
            $table->json('technology_partners')->nullable();
            $table->json('logistics_partners')->nullable();
            $table->string('data_security_standards')->nullable();

            // Result, Score & Data Policies
            $table->text('result_declaration_policy_summary')->nullable();
            $table->string('score_validity_period')->nullable();
            $table->boolean('re_evaluation_allowed')->default(false);
            $table->text('re_evaluation_process_summary')->nullable();
            $table->text('data_retention_policy')->nullable();
            $table->text('grievance_redressal_mechanism')->nullable();

            // Candidate Support & Communication
            $table->string('candidate_portal_url')->nullable();
            $table->string('helpdesk_contact_number')->nullable();
            $table->string('helpdesk_email')->nullable();
            $table->json('official_notifications_urls')->nullable();
            $table->string('faq_url')->nullable();

            // Transparency & Compliance
            $table->boolean('rti_applicable')->default(false);
            $table->boolean('audit_conducted')->default(false);
            $table->text('exam_fairness_policy')->nullable();
            $table->json('anti_malpractice_measures')->nullable();
            $table->boolean('whistleblower_policy_available')->default(false);

            // Public Trust & Reputation
            $table->json('awards_or_recognition')->nullable();
            $table->json('media_mentions')->nullable();
            $table->integer('public_trust_score')->nullable();

            // Digital & SEO
            $table->json('focus_keywords')->nullable();

            // Admin & Platform Control
            $table->boolean('claimed_by_authority')->default(false);
            $table->string('data_source')->nullable();
            $table->integer('confidence_score')->nullable();
            $table->timestamp('last_updated_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'exam_conducting_body_id',
                'abbreviation',
                'mandate_description',
                'authority_type',
                'parent_ministry',
                'established_by',
                'legal_act_reference',
                'headquarters_location',
                'jurisdiction_scope',
                'functions',
                'exam_types_conducted',
                'evaluation_methods',
                'exams_conducted_ids',
                'annual_exam_volume_estimate',
                'average_candidates_per_year',
                'exam_modes_supported',
                'question_bank_managed',
                'normalization_process_available',
                'multi_language_support',
                'remote_proctoring_supported',
                'exam_centres_management_type',
                'technology_partners',
                'logistics_partners',
                'data_security_standards',
                'result_declaration_policy_summary',
                'score_validity_period',
                're_evaluation_allowed',
                're_evaluation_process_summary',
                'data_retention_policy',
                'grievance_redressal_mechanism',
                'candidate_portal_url',
                'helpdesk_contact_number',
                'helpdesk_email',
                'official_notifications_urls',
                'faq_url',
                'rti_applicable',
                'audit_conducted',
                'exam_fairness_policy',
                'anti_malpractice_measures',
                'whistleblower_policy_available',
                'awards_or_recognition',
                'media_mentions',
                'public_trust_score',
                'focus_keywords',
                'claimed_by_authority',
                'data_source',
                'confidence_score',
                'last_updated_on'
            ]);
        });

        DB::table('organisation_types')->whereIn('id', [5, 6, 7])->delete();
    }
};
