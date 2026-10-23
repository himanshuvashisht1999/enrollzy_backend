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

        Schema::table('organisations', function (Blueprint $table) {});
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
