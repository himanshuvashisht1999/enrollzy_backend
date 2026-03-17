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
        Schema::table('organisations', function (Blueprint $table) {
            // Legal & Administrative Status
            if (!Schema::hasColumn('organisations', 'parent_ministry_or_department')) {
                $table->text('parent_ministry_or_department')->nullable()->after('parent_ministry');
            }
            if (!Schema::hasColumn('organisations', 'legal_reference_document_url')) {
                $table->text('legal_reference_document_url')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'jurisdiction_states')) {
                $table->json('jurisdiction_states')->nullable();
            }

            // Counselling Functions & Scope
            if (!Schema::hasColumn('organisations', 'counselling_functions')) {
                $table->json('counselling_functions')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'counselling_types_supported')) {
                $table->json('counselling_types_supported')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'education_domains_supported')) {
                $table->json('education_domains_supported')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'counselling_levels_supported')) {
                $table->json('counselling_levels_supported')->nullable();
            }

            // Exams & Allocation Basis
            if (!Schema::hasColumn('organisations', 'exams_used_for_counselling_ids')) {
                $table->json('exams_used_for_counselling_ids')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'allocation_basis')) {
                $table->text('allocation_basis')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'rank_source_validation_required')) {
                $table->boolean('rank_source_validation_required')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'multiple_exam_support')) {
                $table->boolean('multiple_exam_support')->default(false);
            }

            // Seat Management Capabilities
            if (!Schema::hasColumn('organisations', 'seat_matrix_management')) {
                $table->boolean('seat_matrix_management')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'seat_matrix_source')) {
                $table->text('seat_matrix_source')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'quota_types_managed')) {
                $table->json('quota_types_managed')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'reservation_policy_reference')) {
                $table->text('reservation_policy_reference')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'seat_conversion_rules_supported')) {
                $table->boolean('seat_conversion_rules_supported')->default(false);
            }

            // Counselling Process Governance
            if (!Schema::hasColumn('organisations', 'rounds_supported')) {
                $table->text('rounds_supported')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'round_types')) {
                $table->json('round_types')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'choice_locking_mandatory')) {
                $table->boolean('choice_locking_mandatory')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'seat_upgradation_allowed')) {
                $table->boolean('seat_upgradation_allowed')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'withdrawal_rules_summary')) {
                $table->text('withdrawal_rules_summary')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'exit_rules_summary')) {
                $table->text('exit_rules_summary')->nullable();
            }

            // Fees & Financial Handling
            if (!Schema::hasColumn('organisations', 'counselling_fee_collection_supported')) {
                $table->boolean('counselling_fee_collection_supported')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'fee_collection_mode')) {
                $table->text('fee_collection_mode')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'refund_processing_responsibility')) {
                $table->text('refund_processing_responsibility')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'security_deposit_handling')) {
                $table->boolean('security_deposit_handling')->default(false);
            }

            // Technology & Infrastructure
            if (!Schema::hasColumn('organisations', 'candidate_login_system_available')) {
                $table->boolean('candidate_login_system_available')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'choice_filling_system_available')) {
                $table->boolean('choice_filling_system_available')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'auto_seat_allocation_engine')) {
                $table->boolean('auto_seat_allocation_engine')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'api_integration_supported')) {
                $table->boolean('api_integration_supported')->default(false);
            }

            // Reporting, Verification & Institution Interface
            if (!Schema::hasColumn('organisations', 'institution_reporting_interface_available')) {
                $table->boolean('institution_reporting_interface_available')->default(false);
            }
            if (!Schema::hasColumn('organisations', 'document_verification_mode')) {
                $table->text('document_verification_mode')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'institution_confirmation_process_summary')) {
                $table->text('institution_confirmation_process_summary')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'mis_reporting_controls')) {
                $table->text('mis_reporting_controls')->nullable();
            }

            // Grievance, Appeals & Transparency
            if (!Schema::hasColumn('organisations', 'appeal_process_summary')) {
                $table->text('appeal_process_summary')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'grievance_contact_details')) {
                $table->text('grievance_contact_details')->nullable();
            }

            // Communication & Support
            if (!Schema::hasColumn('organisations', 'candidate_guidelines_url')) {
                $table->text('candidate_guidelines_url')->nullable();
            }

            // Trust, History & Scale
            if (!Schema::hasColumn('organisations', 'years_of_operation')) {
                $table->integer('years_of_operation')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'annual_candidate_volume')) {
                $table->text('annual_candidate_volume')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'institutions_covered_count')) {
                $table->integer('institutions_covered_count')->nullable();
            }
            if (!Schema::hasColumn('organisations', 'states_covered_count')) {
                $table->integer('states_covered_count')->nullable();
            }
        });
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
