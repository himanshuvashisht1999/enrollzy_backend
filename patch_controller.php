<?php
$file = 'app/Http/Controllers/Admin/OrganisationController.php';
$content = file_get_contents($file);

// Add missing fields to base validation rules
$baseRulesSearch = <<<EOT
        \$rules = [
            'name' => 'required|string|max:255',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'organisation_id_number' => 'nullable|string|max:255',
            'brand_type' => 'nullable|string|in:' . implode(',', Organisation::BRAND_TYPES),
            'central_authority' => 'nullable|string|max:255',
            'head_office_location' => 'nullable|string|max:255',
        ];
EOT;

$baseRulesReplace = <<<EOT
        \$rules = [
            'name' => 'required|string|max:255',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'organisation_id_number' => 'nullable|string|max:255',
            'brand_type' => 'nullable|string|in:' . implode(',', Organisation::BRAND_TYPES),
            'central_authority' => 'nullable|string|max:255',
            'head_office_location' => 'nullable|string|max:255',
            'ownership_type' => 'nullable|string',
            'university_type' => 'nullable|string',
            'short_name' => 'nullable|string',
            'brand_name' => 'nullable|string',
            'about_university' => 'nullable|string',
            'vision_mission' => 'nullable|string',
            'about_organisation' => 'nullable|string',
        ];
EOT;

$content = str_replace($baseRulesSearch, $baseRulesReplace, $content);

// Add preprocess call to store
$content = preg_replace('/public function store\(Request \$request\)\s*\{/', "public function store(Request \$request)\n    {\n        \$this->preprocessBooleans(\$request);", $content);

// Add preprocess call to update
$content = preg_replace('/public function update\(Request \$request, Organisation \$organisation\)\s*\{/', "public function update(Request \$request, Organisation \$organisation)\n    {\n        \$this->preprocessBooleans(\$request);", $content);

// Remove boolean loop block from store
$boolBlockStore = '/\/\/ Handle Booleans\s*\$booleans = \[[^\]]+\];\s*foreach \(\$booleans as \$boolField\) \{\s*\$data\[\$boolField\] = \$request->has\(\$boolField\);\s*\}/';
$content = preg_replace($boolBlockStore, '', $content);

// Remove boolean loop block from update
$boolBlockUpdate = '/\/\/ Handle Booleans\s*\$booleans = \[[^\]]+\];\s*foreach \(\$booleans as \$field\) \{\s*if \(\$request->has\(\$field\)\) \{\s*\$data\[\$field\] = \$request->\$field == \'on\' \? 1 : 0;\s*\} else \{\s*\$data\[\$field\] = 0;\s*\}\s*\}/';
$content = preg_replace($boolBlockUpdate, '', $content);

// Add preprocessBooleans method at the end
$preprocessMethod = <<<EOT
    protected function preprocessBooleans(Request \$request)
    {
        \$booleans = [
            'degree_awarding_authority', 'ugc_recognized', 'aicte_approved', 'naac_accredited',
            'autonomous_status', 'gst_registered', 'minority_status', 'international_curriculum_supported',
            'centralized_curriculum_framework', 'centralized_teacher_training', 'centralized_assessment_policy',
            'centralized_lms_available', 'centralized_parent_communication_system', 'child_safety_policy_available',
            'posco_compliance_policy', 'anti_bullying_policy', 'mental_health_policy',
            'teacher_background_verification_policy', 'national_presence', 'international_presence',
            'mobile_app_available', 'claimed_by_organization', 'question_bank_managed',
            'normalization_process_available', 'multi_language_support', 'remote_proctoring_supported',
            're_evaluation_allowed', 'rti_applicable', 'audit_conducted', 'whistleblower_policy_available',
            'claimed_by_authority', 'rank_source_validation_required', 'multiple_exam_support',
            'seat_matrix_management', 'seat_conversion_rules_supported', 'choice_locking_mandatory',
            'seat_upgradation_allowed', 'counselling_fee_collection_supported', 'security_deposit_handling',
            'candidate_login_system_available', 'choice_filling_system_available', 'auto_seat_allocation_engine',
            'api_integration_supported', 'institution_reporting_interface_available'
        ];

        foreach (\$booleans as \$field) {
            if (\$request->has(\$field)) {
                \$val = \$request->\$field;
                \$request->merge([\$field => (in_array(\$val, ['on', 1, '1', true, 'true', 'yes'], true))]);
            } else {
                \$request->merge([\$field => false]);
            }
        }
    }
}
EOT;

$content = preg_replace('/}\s*$/', "\n$preprocessMethod\n", $content);

file_put_contents($file, $content);
echo "Patched successfully.\n";
