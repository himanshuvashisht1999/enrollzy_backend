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
        Schema::table('organisations', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'parent_ministry_or_department',
                'legal_reference_document_url',
                'jurisdiction_states',
                'counselling_functions',
                'counselling_types_supported',
                'education_domains_supported',
                'counselling_levels_supported',
                'exams_used_for_counselling_ids',
                'allocation_basis',
                'rank_source_validation_required',
                'multiple_exam_support',
                'seat_matrix_management',
                'seat_matrix_source',
                'quota_types_managed',
                'reservation_policy_reference',
                'seat_conversion_rules_supported',
                'rounds_supported',
                'round_types',
                'choice_locking_mandatory',
                'seat_upgradation_allowed',
                'withdrawal_rules_summary',
                'exit_rules_summary',
                'counselling_fee_collection_supported',
                'fee_collection_mode',
                'refund_processing_responsibility',
                'security_deposit_handling',
                'candidate_login_system_available',
                'choice_filling_system_available',
                'auto_seat_allocation_engine',
                'api_integration_supported',
                'institution_reporting_interface_available',
                'document_verification_mode',
                'institution_confirmation_process_summary',
                'mis_reporting_controls',
                'appeal_process_summary',
                'grievance_contact_details',
                'candidate_guidelines_url',
                'years_of_operation',
                'annual_candidate_volume',
                'institutions_covered_count',
                'states_covered_count',
            ]);
        });
    }
};
