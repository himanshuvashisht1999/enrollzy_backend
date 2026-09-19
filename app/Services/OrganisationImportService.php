<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Campus;
use App\Models\Department;
use App\Models\Course;
use App\Models\OrganisationCourse;
use App\Models\ProgramLevel;
use App\Models\StreamOffered;
use App\Models\Discipline;
use App\Models\OrganisationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrganisationImportService
{
    /**
     * Common degree alias mappings for robust course lookup
     */
    protected array $courseAliases = [
        'diploma in pharmacy' => ['d.pharm', 'd pharm', 'dpharm', 'diploma pharmacy'],
        'bachelor of pharmacy' => ['b.pharm', 'b pharm', 'bpharm', 'b pharmacy'],
        'master of pharmacy' => ['m.pharm', 'm pharm', 'mpharm', 'm pharmacy'],
        'bachelor of technology' => ['b.tech', 'b tech', 'btech', 'b.e', 'be', 'bachelor of engineering'],
        'master of technology' => ['m.tech', 'm tech', 'mtech', 'm.e', 'me', 'master of engineering'],
        'master of business administration' => ['mba'],
        'bachelor of business administration' => ['bba'],
        'bachelor of computer applications' => ['bca'],
        'master of computer applications' => ['mca'],
        'bachelor of commerce' => ['b.com', 'bcom'],
        'master of commerce' => ['m.com', 'mcom'],
        'bachelor of science' => ['b.sc', 'bsc'],
        'master of science' => ['m.sc', 'msc'],
        'bachelor of arts' => ['b.a', 'ba'],
        'master of arts' => ['m.a', 'ma'],
        'bachelor of laws' => ['llb', 'll.b', 'bachelor of law'],
        'master of laws' => ['llm', 'll.m'],
        'bachelor of medicine and bachelor of surgery' => ['mbbs'],
        'bachelor of dental surgery' => ['bds'],
        'doctor of medicine' => ['md'],
        'master of surgery' => ['ms'],
        'bachelor of architecture' => ['b.arch', 'b arch', 'barch'],
        'bachelor of design' => ['b.des', 'b design', 'bdesign', 'b des'],
        'bachelor of education' => ['b.ed', 'b ed', 'bed'],
        'master of education' => ['m.ed', 'm ed', 'med'],
        'doctor of philosophy' => ['ph.d', 'phd', 'doctorate'],
    ];

    /**
     * Normalize a string for comparison by removing stop words and non-alphanumeric chars
     */
    public function normalizeString(?string $str): string
    {
        if (!$str) return '';
        $str = strtolower(trim($str));
        $str = preg_replace('/\b(in|of|and|&|the|for|with|a|an|program|course|degree|honors|hons)\b/i', '', $str);
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        return trim($str);
    }

    /**
     * Safely parse an integer, extracting numeric digits from strings like "29 in Engineering" or "Rank 101-150"
     */
    public function parseInteger($val, int $min = -2147483648, int $max = 2147483647): ?int
    {
        if ($val === null || $val === '') {
            return null;
        }
        if (is_int($val)) {
            return max($min, min($max, $val));
        }
        if (is_numeric($val)) {
            $intVal = (int) $val;
            return max($min, min($max, $intVal));
        }
        if (is_string($val)) {
            // Take the first number if range like "101-150"
            if (preg_match('/(\d+)/', $val, $matches)) {
                $intVal = (int) $matches[1];
                return max($min, min($max, $intVal));
            }
        }
        return null;
    }

    /**
     * Safely parse a float/decimal, extracting numbers from strings like "4.5 / 5" or "4.8 out of 5"
     */
    public function parseFloat($val, ?float $min = null, ?float $max = null): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }
        if (is_float($val) || is_int($val)) {
            $num = (float) $val;
            if ($min !== null && $num < $min) $num = $min;
            if ($max !== null && $num > $max) $num = $max;
            return round($num, 2);
        }
        if (is_numeric($val)) {
            $num = (float) $val;
            if ($min !== null && $num < $min) $num = $min;
            if ($max !== null && $num > $max) $num = $max;
            return round($num, 2);
        }
        if (is_string($val)) {
            $trimmed = trim($val);
            // Split by separator like " / ", " out of ", " - "
            $parts = preg_split('/\s*(?:\/|\bout of\b|-|–|\bto\b)\s*/i', $trimmed);
            $first = $parts[0] ?? $trimmed;
            if (preg_match('/(\d+(?:\.\d+)?)/', str_replace(',', '', $first), $matches)) {
                $num = (float) $matches[1];
                if ($min !== null && $num < $min) return $min;
                if ($max !== null && $num > $max) return $max;
                return round($num, 2);
            }
        }
        return null;
    }

    /**
     * Safely parse a fee/currency amount into a clean decimal(10,2) compatible float.
     * Handles Indian formats (Lakh, Crore, ₹), fee ranges ("₹ 1,36,000 - 3,50,000"),
     * and prevents MySQL 1264 Out of Range errors by strictly capping at 99,999,999.99.
     */
    public function parseFee($val, float $max = 99999999.99): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }
        if (is_float($val) || is_int($val)) {
            $num = (float) $val;
            return ($num > $max || $num < 0) ? null : round($num, 2);
        }
        if (is_numeric($val)) {
            $num = (float) $val;
            return ($num > $max || $num < 0) ? null : round($num, 2);
        }
        if (is_string($val)) {
            $trimmed = trim($val);
            $multiplier = 1;
            if (preg_match('/(?:lakh|lac)s?/i', $trimmed)) {
                $multiplier = 100000;
            } elseif (preg_match('/(?:crore|cr)s?/i', $trimmed)) {
                $multiplier = 10000000;
            } elseif (preg_match('/(?:k)\b/i', $trimmed)) {
                $multiplier = 1000;
            }

            // Split by range separators: "-", "–", "to", "/"
            $parts = preg_split('/\s*(?:-|–|\bto\b|\/)\s*/i', $trimmed);
            $firstPart = $parts[0] ?? $trimmed;

            if (preg_match('/(\d+(?:\.\d+)?)/', str_replace(',', '', $firstPart), $matches)) {
                $valNum = (float) $matches[1] * $multiplier;
                if ($valNum > $max || $valNum < 0) {
                    return null;
                }
                return round($valNum, 2);
            }
        }
        return null;
    }

    /**
     * Safely parse an array or comma-separated string into a clean array
     */
    public function parseArray($val): ?array
    {
        if (empty($val)) {
            return null;
        }
        if (is_array($val)) {
            $filtered = array_values(array_filter($val, fn($item) => $item !== null && $item !== ''));
            return !empty($filtered) ? $filtered : null;
        }
        if (is_string($val)) {
            $items = array_map('trim', explode(',', $val));
            $filtered = array_values(array_filter($items, fn($item) => $item !== ''));
            return !empty($filtered) ? $filtered : null;
        }
        return null;
    }

    /**
     * Resolve existing Master Course without ever creating a new one
     */
    public function resolveMasterCourse($courseId, ?string $courseName, ?string $shortName = null): ?Course
    {
        if (!empty($courseId)) {
            $c = Course::find($courseId);
            if ($c) {
                return $c;
            }
        }

        if (empty($courseName)) {
            return null;
        }

        $allCourses = Course::all();
        $normName = $this->normalizeString($courseName);
        $normShort = $this->normalizeString($shortName);

        // 1. Direct normalized match
        foreach ($allCourses as $c) {
            $normC = $this->normalizeString($c->name);
            if ($normName !== '' && $normName === $normC) {
                return $c;
            }
            if ($normShort !== '' && $normShort === $normC) {
                return $c;
            }
        }

        // 2. Alias match
        foreach ($this->courseAliases as $long => $aliasList) {
            $normLong = $this->normalizeString($long);
            $matchesLong = ($normName === $normLong || str_contains($normName, $normLong));
            $matchesShort = false;
            foreach ($aliasList as $al) {
                $normAl = $this->normalizeString($al);
                if (($normShort !== '' && $normShort === $normAl) || ($normName !== '' && $normName === $normAl)) {
                    $matchesShort = true;
                    break;
                }
            }

            if ($matchesLong || $matchesShort) {
                foreach ($allCourses as $c) {
                    $normC = $this->normalizeString($c->name);
                    if ($normC === $normLong) return $c;
                    foreach ($aliasList as $al) {
                        if ($normC === $this->normalizeString($al)) return $c;
                    }
                }
            }
        }

        // 3. Substring / Similarity match
        $best = null;
        $maxScore = 0;
        foreach ($allCourses as $c) {
            $normC = $this->normalizeString($c->name);
            similar_text($normName, $normC, $pct);
            if ($pct > $maxScore && $pct >= 55) {
                $maxScore = $pct;
                $best = $c;
            }
        }

        return $best;
    }

    /**
     * Persist reviewed AI-extracted data into the database
     *
     * @param array $data
     * @return Organisation
     * @throws \Exception
     */
    public function saveImportedData(array $data): Organisation
    {
        return DB::transaction(function () use ($data) {
            $orgInput = $data['organisation'] ?? [];
            $targetOrgId = $data['target_organisation_id'] ?? $orgInput['id'] ?? null;

            if ($targetOrgId) {
                $organisation = Organisation::with(['campuses', 'departments'])->findOrFail($targetOrgId);
            } else {
                if (empty($orgInput['name'])) {
                    throw new \Exception('Organisation name is missing.');
                }

                // 1. Resolve Organisation Type Master (NEVER CREATE)
                $orgTypeId = 1; // Default to 1 (University)
                if (!empty($orgInput['organisation_type_id'])) {
                    $ot = OrganisationType::find($orgInput['organisation_type_id']);
                    if ($ot) $orgTypeId = $ot->id;
                } elseif (!empty($orgInput['organisation_type'])) {
                    $orgTypeName = $orgInput['organisation_type'];
                    $orgType = OrganisationType::where('title', 'like', '%' . $orgTypeName . '%')->first();
                    if ($orgType) $orgTypeId = $orgType->id;
                }

                // 2. Prepare Organisation Record
                $slug = Str::slug($orgInput['name']);
                $originalSlug = $slug;
                $count = 1;
                while (Organisation::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                $organisation = Organisation::create([
                'name' => $orgInput['name'],
                'short_name' => $orgInput['short_name'] ?? null,
                'brand_name' => $orgInput['brand_name'] ?? ($orgInput['short_name'] ?? null),
                'slug' => $slug,
                'organisation_type_id' => $orgTypeId,
                'brand_type' => in_array($orgInput['brand_type'] ?? '', Organisation::BRAND_TYPES) ? $orgInput['brand_type'] : 'Independent',
                'central_authority' => $orgInput['central_authority'] ?? null,
                'head_office_location' => $orgInput['head_office_location'] ?? null,
                'official_website' => $orgInput['official_website'] ?? null,
                'admission_portal_url' => $orgInput['admission_portal_url'] ?? null,
                'student_portal_url' => $orgInput['student_portal_url'] ?? null,
                'parent_portal_url' => $orgInput['parent_portal_url'] ?? null,
                'established_year' => $this->parseInteger($orgInput['established_year'] ?? null),
                'ownership_type' => $orgInput['ownership_type'] ?? null,
                'university_type' => $orgInput['university_type'] ?? ($orgInput['ownership_type'] ?? null),
                'university_category' => $orgInput['university_category'] ?? null,
                'logo_url' => $this->handleRemoteOrLocalImage($orgInput['logo_url'] ?? null, 'logo', (int)$orgTypeId),
                'cover_image_url' => $this->handleRemoteOrLocalImage($orgInput['cover_image_url'] ?? null, 'cover', (int)$orgTypeId),
                'about_university' => $orgInput['about_university'] ?? ($orgInput['about_organisation'] ?? null),
                'about_organisation' => $orgInput['about_organisation'] ?? ($orgInput['about_university'] ?? null),
                'vision_mission' => $orgInput['vision_mission'] ?? null,
                'core_values' => $this->parseArray($orgInput['core_values'] ?? null),
                'chancellor_name' => $orgInput['chancellor_name'] ?? null,
                'vice_chancellor_name' => $orgInput['vice_chancellor_name'] ?? null,
                'governing_body_name' => $orgInput['governing_body_name'] ?? null,
                'autonomous_status' => !empty($orgInput['autonomous_status']),
                'degree_awarding_authority' => !empty($orgInput['degree_awarding_authority']),
                'ugc_recognized' => !empty($orgInput['ugc_recognized']),
                'ugc_approval_number' => $orgInput['ugc_approval_number'] ?? null,
                'aicte_approved' => !empty($orgInput['aicte_approved']),
                'naac_accredited' => !empty($orgInput['naac_accredited']),
                'naac_grade' => $orgInput['naac_grade'] ?? null,
                'nirf_rank_overall' => $this->parseInteger($orgInput['nirf_rank_overall'] ?? null),
                'nirf_rank_category' => $this->parseInteger($orgInput['nirf_rank_category'] ?? null),
                'international_accreditations' => $this->parseArray($orgInput['international_accreditations'] ?? null),
                'statutory_approvals' => $this->parseArray($orgInput['statutory_approvals'] ?? null),
                'levels_offered' => $this->parseArray($orgInput['levels_offered'] ?? null),
                'number_of_campuses' => $this->parseInteger($orgInput['number_of_campuses'] ?? null),
                'number_of_constituent_colleges' => $this->parseInteger($orgInput['number_of_constituent_colleges'] ?? null),
                'number_of_affiliated_colleges' => $this->parseInteger($orgInput['number_of_affiliated_colleges'] ?? null),
                'gst_registered' => !empty($orgInput['gst_registered']),
                'gst_number' => $orgInput['gst_number'] ?? null,
                'pan_number' => $orgInput['pan_number'] ?? null,
                'registered_entity_name' => $orgInput['registered_entity_name'] ?? null,
                'registration_number' => $orgInput['registration_number'] ?? null,
                'managing_trust_or_society_name' => $orgInput['managing_trust_or_society_name'] ?? null,
                'minority_status' => !empty($orgInput['minority_status']),
                'minority_type' => $orgInput['minority_type'] ?? null,
                // School Specific
                'education_boards_supported' => $this->parseArray($orgInput['education_boards_supported'] ?? null),
                'medium_of_instruction_supported' => $this->parseArray($orgInput['medium_of_instruction_supported'] ?? null),
                'international_curriculum_supported' => !empty($orgInput['international_curriculum_supported']),
                'education_levels_supported' => $this->parseArray($orgInput['education_levels_supported'] ?? null),
                'streams_supported' => $this->parseArray($orgInput['streams_supported'] ?? null),
                'pedagogy_model' => $orgInput['pedagogy_model'] ?? null,
                'focus_areas' => $this->parseArray($orgInput['focus_areas'] ?? null),
                'centralized_curriculum_framework' => !empty($orgInput['centralized_curriculum_framework']),
                'centralized_teacher_training' => !empty($orgInput['centralized_teacher_training']),
                'centralized_assessment_policy' => !empty($orgInput['centralized_assessment_policy']),
                'centralized_lms_available' => !empty($orgInput['centralized_lms_available']),
                'centralized_parent_communication_system' => !empty($orgInput['centralized_parent_communication_system']),
                'child_safety_policy_available' => !empty($orgInput['child_safety_policy_available']),
                'posco_compliance_policy' => !empty($orgInput['posco_compliance_policy']),
                'anti_bullying_policy' => !empty($orgInput['anti_bullying_policy']),
                'mental_health_policy' => !empty($orgInput['mental_health_policy']),
                'teacher_background_verification_policy' => !empty($orgInput['teacher_background_verification_policy']),
                'total_schools_count' => $this->parseInteger($orgInput['total_schools_count'] ?? null),
                'cities_present_in' => $this->parseArray($orgInput['cities_present_in'] ?? null),
                'states_present_in' => $this->parseArray($orgInput['states_present_in'] ?? null),
                'national_presence' => !empty($orgInput['national_presence']),
                'international_presence' => !empty($orgInput['international_presence']),
                'flagship_schools' => $this->parseArray($orgInput['flagship_schools'] ?? null),
                'mobile_app_available' => !empty($orgInput['mobile_app_available']),
                'average_rating' => $this->parseFloat($orgInput['average_rating'] ?? null, 0.0, 9.99),
                'total_reviews' => $this->parseInteger($orgInput['total_reviews'] ?? null),
                'awards_and_recognition' => $this->parseArray($orgInput['awards_and_recognition'] ?? null),
                'schema_type' => $orgInput['schema_type'] ?? null,
                'meta_title' => $orgInput['meta_title'] ?? null,
                'meta_description' => $orgInput['meta_description'] ?? null,
                'canonical_url' => $orgInput['canonical_url'] ?? null,
                'claimed_by_organization' => !empty($orgInput['claimed_by_organization']),
                // Exam Conducting Body Specific
                'abbreviation' => $orgInput['abbreviation'] ?? null,
                'mandate_description' => $orgInput['mandate_description'] ?? null,
                'authority_type' => $orgInput['authority_type'] ?? null,
                'parent_ministry' => $orgInput['parent_ministry'] ?? null,
                'parent_ministry_or_department' => $orgInput['parent_ministry_or_department'] ?? ($orgInput['parent_ministry'] ?? null),
                'established_by' => $orgInput['established_by'] ?? null,
                'legal_act_reference' => $orgInput['legal_act_reference'] ?? null,
                'headquarters_location' => $orgInput['headquarters_location'] ?? ($orgInput['head_office_location'] ?? null),
                'jurisdiction_scope' => $orgInput['jurisdiction_scope'] ?? null,
                'jurisdiction_states' => $this->parseArray($orgInput['jurisdiction_states'] ?? null),
                'functions' => $this->parseArray($orgInput['functions'] ?? null),
                'exam_types_conducted' => $this->parseArray($orgInput['exam_types_conducted'] ?? null),
                'evaluation_methods' => $this->parseArray($orgInput['evaluation_methods'] ?? null),
                'exams_conducted_ids' => $this->parseArray($orgInput['exams_conducted_ids'] ?? null),
                'annual_exam_volume_estimate' => $orgInput['annual_exam_volume_estimate'] ?? null,
                'average_candidates_per_year' => $orgInput['average_candidates_per_year'] ?? null,
                'exam_modes_supported' => $this->parseArray($orgInput['exam_modes_supported'] ?? null),
                'question_bank_managed' => !empty($orgInput['question_bank_managed']),
                'normalization_process_available' => !empty($orgInput['normalization_process_available']),
                'multi_language_support' => !empty($orgInput['multi_language_support']),
                'remote_proctoring_supported' => !empty($orgInput['remote_proctoring_supported']),
                'exam_centres_management_type' => $orgInput['exam_centres_management_type'] ?? null,
                'technology_partners' => $this->parseArray($orgInput['technology_partners'] ?? null),
                'logistics_partners' => $this->parseArray($orgInput['logistics_partners'] ?? null),
                'data_security_standards' => $orgInput['data_security_standards'] ?? null,
                'result_declaration_policy_summary' => $orgInput['result_declaration_policy_summary'] ?? null,
                'score_validity_period' => $orgInput['score_validity_period'] ?? null,
                're_evaluation_allowed' => !empty($orgInput['re_evaluation_allowed']),
                're_evaluation_process_summary' => $orgInput['re_evaluation_process_summary'] ?? null,
                'data_retention_policy' => $orgInput['data_retention_policy'] ?? null,
                'grievance_redressal_mechanism' => $orgInput['grievance_redressal_mechanism'] ?? null,
                'candidate_portal_url' => $orgInput['candidate_portal_url'] ?? null,
                'helpdesk_contact_number' => $orgInput['helpdesk_contact_number'] ?? null,
                'helpdesk_email' => $orgInput['helpdesk_email'] ?? null,
                'official_notifications_urls' => $this->parseArray($orgInput['official_notifications_urls'] ?? null),
                'faq_url' => $orgInput['faq_url'] ?? null,
                'rti_applicable' => !empty($orgInput['rti_applicable']),
                'audit_conducted' => !empty($orgInput['audit_conducted']),
                'exam_fairness_policy' => $orgInput['exam_fairness_policy'] ?? null,
                'anti_malpractice_measures' => $this->parseArray($orgInput['anti_malpractice_measures'] ?? null),
                'whistleblower_policy_available' => !empty($orgInput['whistleblower_policy_available']),
                'awards_or_recognition' => $this->parseArray($orgInput['awards_or_recognition'] ?? ($orgInput['awards_and_recognition'] ?? null)),
                'media_mentions' => $this->parseArray($orgInput['media_mentions'] ?? null),
                'public_trust_score' => $this->parseInteger($orgInput['public_trust_score'] ?? null),
                'focus_keywords' => $this->parseArray($orgInput['focus_keywords'] ?? null),
                'claimed_by_authority' => !empty($orgInput['claimed_by_authority']),
                'data_source' => $orgInput['data_source'] ?? null,
                'confidence_score' => $this->parseInteger($orgInput['confidence_score'] ?? null),
                // Counselling Body Specific
                'counselling_functions' => $this->parseArray($orgInput['counselling_functions'] ?? null),
                'counselling_types_supported' => $this->parseArray($orgInput['counselling_types_supported'] ?? null),
                'education_domains_supported' => $this->parseArray($orgInput['education_domains_supported'] ?? null),
                'counselling_levels_supported' => $this->parseArray($orgInput['counselling_levels_supported'] ?? null),
                'exams_used_for_counselling_ids' => $this->parseArray($orgInput['exams_used_for_counselling_ids'] ?? null),
                'allocation_basis' => $orgInput['allocation_basis'] ?? null,
                'rank_source_validation_required' => !empty($orgInput['rank_source_validation_required']),
                'multiple_exam_support' => !empty($orgInput['multiple_exam_support']),
                'seat_matrix_management' => !empty($orgInput['seat_matrix_management']),
                'seat_matrix_source' => $orgInput['seat_matrix_source'] ?? null,
                'quota_types_managed' => $this->parseArray($orgInput['quota_types_managed'] ?? null),
                'reservation_policy_reference' => $orgInput['reservation_policy_reference'] ?? null,
                'seat_conversion_rules_supported' => !empty($orgInput['seat_conversion_rules_supported']),
                'rounds_supported' => $orgInput['rounds_supported'] ?? null,
                'round_types' => $this->parseArray($orgInput['round_types'] ?? null),
                'choice_locking_mandatory' => !empty($orgInput['choice_locking_mandatory']),
                'seat_upgradation_allowed' => !empty($orgInput['seat_upgradation_allowed']),
                'withdrawal_rules_summary' => $orgInput['withdrawal_rules_summary'] ?? null,
                'exit_rules_summary' => $orgInput['exit_rules_summary'] ?? null,
                'counselling_fee_collection_supported' => !empty($orgInput['counselling_fee_collection_supported']),
                'fee_collection_mode' => $orgInput['fee_collection_mode'] ?? null,
                'refund_processing_responsibility' => $orgInput['refund_processing_responsibility'] ?? null,
                'security_deposit_handling' => !empty($orgInput['security_deposit_handling']),
                'candidate_login_system_available' => !empty($orgInput['candidate_login_system_available']),
                'choice_filling_system_available' => !empty($orgInput['choice_filling_system_available']),
                'auto_seat_allocation_engine' => !empty($orgInput['auto_seat_allocation_engine']),
                'api_integration_supported' => !empty($orgInput['api_integration_supported']),
                'institution_reporting_interface_available' => !empty($orgInput['institution_reporting_interface_available']),
                'document_verification_mode' => $orgInput['document_verification_mode'] ?? null,
                'institution_confirmation_process_summary' => $orgInput['institution_confirmation_process_summary'] ?? null,
                'mis_reporting_controls' => $orgInput['mis_reporting_controls'] ?? null,
                'appeal_process_summary' => $orgInput['appeal_process_summary'] ?? null,
                'grievance_contact_details' => $orgInput['grievance_contact_details'] ?? null,
                'candidate_guidelines_url' => $orgInput['candidate_guidelines_url'] ?? null,
                'years_of_operation' => $this->parseInteger($orgInput['years_of_operation'] ?? null),
                'annual_candidate_volume' => $orgInput['annual_candidate_volume'] ?? null,
                'institutions_covered_count' => $this->parseInteger($orgInput['institutions_covered_count'] ?? null),
                'states_covered_count' => $this->parseInteger($orgInput['states_covered_count'] ?? null),
                'counselling_portal_url' => $orgInput['counselling_portal_url'] ?? null,
                'candidate_support_url' => $orgInput['candidate_support_url'] ?? null,
                'candidate_handbook_url' => $orgInput['candidate_handbook_url'] ?? null,
                'helpdesk_toll_free_number' => $orgInput['helpdesk_toll_free_number'] ?? null,
                'helpdesk_operational_hours' => $orgInput['helpdesk_operational_hours'] ?? null,
                'status' => true,
                'is_top' => !empty($orgInput['is_top']),
            ]);
            }

            $mode = $data['mode'] ?? ($targetOrgId ? 'campuses_and_courses' : 'organisation');

            // --- MODE: CAMPUS ONLY ---
            if ($mode === 'campus') {
                $campusesInput = $data['campuses'] ?? [];
                foreach ($campusesInput as $index => $cInput) {
                    $campusName = !empty($cInput['campus_name']) ? $cInput['campus_name'] : ($organisation->name . ' - Campus ' . ($index + 1));
                    $campusSlug = Str::slug($campusName . '-' . Str::random(4));

                    Campus::create([
                        'id' => (string) Str::uuid(),
                        'organisation_id' => $organisation->id,
                        'campus_name' => $campusName,
                        'slug' => $campusSlug,
                        'campus_type' => in_array($cInput['campus_type'] ?? '', ['Main', 'Regional', 'Satellite']) ? $cInput['campus_type'] : ($index === 0 ? 'Main' : 'Regional'),
                        'established_year' => $this->parseInteger($cInput['established_year'] ?? $organisation->established_year),
                        'city' => $cInput['city'] ?? null,
                        'state' => $cInput['state'] ?? null,
                        'country' => $cInput['country'] ?? 'India',
                        'pincode' => $cInput['pincode'] ?? null,
                        'full_address' => $cInput['full_address'] ?? null,
                        'google_map_url' => $cInput['google_map_url'] ?? null,
                        'nearest_transport_hub' => $cInput['nearest_transport_hub'] ?? null,
                        'campus_area_acres' => $this->parseFloat($cInput['campus_area_acres'] ?? null, 0.0, 999999.99),
                        'academic_blocks_count' => $this->parseInteger($cInput['academic_blocks_count'] ?? 0) ?? 0,
                        'classrooms_count' => $this->parseInteger($cInput['classrooms_count'] ?? 0) ?? 0,
                        'smart_classrooms' => !empty($cInput['smart_classrooms']),
                        'laboratories_count' => $this->parseInteger($cInput['laboratories_count'] ?? 0) ?? 0,
                        'research_centers_count' => $this->parseInteger($cInput['research_centers_count'] ?? 0) ?? 0,
                        'library_available' => !empty($cInput['library_available']),
                        'library_books_count' => $this->parseInteger($cInput['library_books_count'] ?? 0) ?? 0,
                        'digital_library_access' => !empty($cInput['digital_library_access']),
                        'hostel_available' => !empty($cInput['hostel_available']),
                        'hostel_type' => in_array($cInput['hostel_type'] ?? '', ['Boys', 'Girls', 'Both', 'None']) ? $cInput['hostel_type'] : null,
                        'hostel_capacity' => $this->parseInteger($cInput['hostel_capacity'] ?? 0) ?? 0,
                        'food_facility' => $cInput['food_facility'] ?? null,
                        'medical_facility_available' => !empty($cInput['medical_facility_available']),
                        'sports_facilities' => $this->parseArray($cInput['sports_facilities'] ?? null),
                        'transport_available' => !empty($cInput['transport_available']),
                        'bus_routes_count' => $this->parseInteger($cInput['bus_routes_count'] ?? 0) ?? 0,
                        'parking_available' => !empty($cInput['parking_available']),
                        'cctv_coverage' => !empty($cInput['cctv_coverage']),
                        'security_staff_count' => $this->parseInteger($cInput['security_staff_count'] ?? 0) ?? 0,
                        'fire_safety_certified' => !empty($cInput['fire_safety_certified']),
                        'disaster_management_plan' => !empty($cInput['disaster_management_plan']),
                        'campus_email' => $cInput['campus_email'] ?? ($organisation->email ?? null),
                        'campus_website' => $cInput['campus_website'] ?? ($organisation->official_website ?? null),
                        'campus_contact_numbers' => $this->parseArray($cInput['campus_contact_numbers'] ?? null),
                        'status' => true,
                    ]);
                }
                return $organisation;
            }

            // --- MODE: DEPARTMENT ONLY ---
            if ($mode === 'department') {
                $targetCampusId = $data['target_campus_id'] ?? null;
                $deptsInput = $data['departments'] ?? [];
                foreach ($deptsInput as $index => $dInput) {
                    $deptName = $dInput['department_name'] ?? ('Department ' . ($index + 1));
                    $deptSlug = Str::slug($deptName . '-' . Str::random(4));

                    Department::create([
                        'id' => (string) Str::uuid(),
                        'organisation_id' => $organisation->id,
                        'campus_id' => $targetCampusId,
                        'department_name' => $deptName,
                        'department_code' => $dInput['department_code'] ?? null,
                        'department_type' => in_array($dInput['department_type'] ?? '', ['Academic', 'Clinical', 'Research', 'Interdisciplinary']) ? $dInput['department_type'] : 'Academic',
                        'established_year' => $this->parseInteger($dInput['established_year'] ?? null),
                        'slug' => $deptSlug,
                        'about_department' => $dInput['about_department'] ?? null,
                        'discipline_area' => $dInput['discipline_area'] ?? null,
                        'specializations_supported' => $this->parseArray($dInput['specializations_supported'] ?? null),
                        'education_levels_supported' => $this->parseArray($dInput['education_levels_supported'] ?? null),
                        'is_interdisciplinary' => !empty($dInput['is_interdisciplinary']),
                        'head_of_department_name' => $dInput['head_of_department_name'] ?? null,
                        'head_of_department_designation' => $dInput['head_of_department_designation'] ?? null,
                        'hod_appointment_type' => in_array($dInput['hod_appointment_type'] ?? '', ['Permanent', 'Acting']) ? $dInput['hod_appointment_type'] : null,
                        'hod_email' => $dInput['hod_email'] ?? null,
                        'department_office_contact' => $dInput['department_office_contact'] ?? null,
                        'faculty_count' => $this->parseInteger($dInput['faculty_count'] ?? null),
                        'curriculum_design_responsibility' => !empty($dInput['curriculum_design_responsibility']),
                        'exam_setting_responsibility' => !empty($dInput['exam_setting_responsibility']),
                        'research_programs_managed' => !empty($dInput['research_programs_managed']),
                        'phd_supervision_available' => !empty($dInput['phd_supervision_available']),
                        'industry_collaboration_supported' => !empty($dInput['industry_collaboration_supported']),
                        'department_labs_count' => (string)($this->parseInteger($dInput['department_labs_count'] ?? 0) ?? 0),
                        'specialized_labs_available' => !empty($dInput['specialized_labs_available']),
                        'research_centers_under_department' => $dInput['research_centers_under_department'] ?? null,
                        'department_library_section' => !empty($dInput['department_library_section']),
                        'classrooms_count' => (string)($this->parseInteger($dInput['classrooms_count'] ?? 0) ?? 0),
                        'research_publications_count' => $this->parseInteger($dInput['research_publications_count'] ?? 0) ?? 0,
                        'funded_projects_count' => $this->parseInteger($dInput['funded_projects_count'] ?? 0) ?? 0,
                        'patents_filed_count' => $this->parseInteger($dInput['patents_filed_count'] ?? 0) ?? 0,
                        'industry_projects_count' => $this->parseInteger($dInput['industry_projects_count'] ?? 0) ?? 0,
                        'department_website_url' => $dInput['department_website_url'] ?? null,
                        'department_email' => $dInput['department_email'] ?? null,
                        'department_notice_board_url' => $dInput['department_notice_board_url'] ?? null,
                        'status' => 'Active',
                        'visibility' => 'Public',
                    ]);
                }
                return $organisation;
            }

            // --- MODE: COURSE ONLY ---
            if ($mode === 'course') {
                $targetCampusId = $data['target_campus_id'] ?? null;
                $targetDeptId = $data['target_department_id'] ?? null;
                $coursesInput = $data['courses'] ?? [];

                foreach ($coursesInput as $cData) {
                    $courseName = $cData['academic_unit_name'] ?? $cData['course_name'] ?? $cData['name'] ?? null;
                    $shortName = $cData['short_name'] ?? null;
                    $courseId = !empty($cData['course_id']) ? (int)$cData['course_id'] : null;

                    $masterCourse = $this->resolveMasterCourse($courseId, $courseName, $shortName);

                    $programLevelId = null;
                    if (!empty($cData['program_level_id'])) {
                        $pl = ProgramLevel::find($cData['program_level_id']);
                        if ($pl) $programLevelId = $pl->id;
                    } elseif (!empty($cData['program_level'])) {
                        $pl = ProgramLevel::where('title', 'like', '%' . trim($cData['program_level']) . '%')->first();
                        if ($pl) $programLevelId = $pl->id;
                    }
                    if (!$programLevelId && $masterCourse && $masterCourse->program_level_id) {
                        $programLevelId = $masterCourse->program_level_id;
                    }

                    $streamId = null;
                    if (!empty($cData['stream_offered_id'])) {
                        $st = StreamOffered::find($cData['stream_offered_id']);
                        if ($st) $streamId = $st->id;
                    } elseif (!empty($cData['stream'])) {
                        $st = StreamOffered::where('title', 'like', '%' . trim($cData['stream']) . '%')->first();
                        if ($st) $streamId = $st->id;
                    }
                    if (!$streamId && $masterCourse && $masterCourse->stream_offered_id) {
                        $streamId = $masterCourse->stream_offered_id;
                    }

                    $disciplineId = null;
                    if (!empty($cData['discipline_id'])) {
                        $disc = Discipline::find($cData['discipline_id']);
                        if ($disc) $disciplineId = $disc->id;
                    } elseif (!empty($cData['discipline'])) {
                        $disc = Discipline::where('title', 'like', '%' . trim($cData['discipline']) . '%')->first();
                        if ($disc) $disciplineId = $disc->id;
                    }
                    if (!$disciplineId && $masterCourse && $masterCourse->discipline_id) {
                        $disciplineId = $masterCourse->discipline_id;
                    }

                    $displayCourseName = $masterCourse ? $masterCourse->name : ($courseName ?: 'Academic Program');
                    $rawTotalFees = !empty($cData['total_fees']) ? $cData['total_fees'] : ($cData['fees'] ?? null);

                    OrganisationCourse::create([
                        'organisation_id' => $organisation->id,
                        'campus_id' => $targetCampusId,
                        'department_id' => $targetDeptId,
                        'course_id' => $masterCourse ? $masterCourse->id : null,
                        'academic_unit_name' => Str::limit($cData['academic_unit_name'] ?? $displayCourseName, 250, ''),
                        'slug' => Str::slug(Str::limit($displayCourseName, 100, '') . '-' . Str::random(4)),
                        'mode' => in_array($cData['mode'] ?? '', ['Regular', 'Online', 'Distance', 'Part-time']) ? $cData['mode'] : 'Regular',
                        'duration' => Str::limit($cData['duration'] ?? ($masterCourse->duration ?? '3 Years'), 250, ''),
                        'fees' => !empty($cData['fees']) ? Str::limit((string)$cData['fees'], 250, '') : null,
                        'total_fees' => $this->parseFee($rawTotalFees),
                        'fees_structure' => $cData['fees_structure'] ?? null,
                        'annual_fee_range' => !empty($cData['annual_fee_range']) ? Str::limit((string)$cData['annual_fee_range'], 250, '') : null,
                        'admission_fee' => !empty($cData['admission_fee']) ? Str::limit((string)$cData['admission_fee'], 250, '') : null,
                        'eligibility' => $cData['eligibility'] ?? null,
                        'admission_process' => $cData['admission_process'] ?? null,
                        'provisional_admission' => !empty($cData['provisional_admission']),
                        'installment_available' => !empty($cData['installment_available']),
                        'scholarship_available' => !empty($cData['scholarship_available']),
                        'refund_policy_available' => !empty($cData['refund_policy_available']),
                        'roi' => in_array($cData['roi'] ?? '', ['Low', 'Medium', 'High']) ? $cData['roi'] : null,
                        'curriculum' => $cData['curriculum'] ?? null,
                        'career_prospects' => $cData['career_prospects'] ?? null,
                        'placement_details' => $cData['placement_details'] ?? null,
                        'rating' => $this->parseFloat($cData['rating'] ?? null, 0.0, 9.9),
                        'industrial_collaboration' => $cData['industrial_collaboration'] ?? null,
                        'internship_ranking' => $cData['internship_ranking'] ?? null,
                        'program_level_id' => $programLevelId,
                        'stream_offered_id' => $streamId,
                        'discipline_id' => $disciplineId,
                        'status' => true,
                    ]);
                }
                return $organisation;
            }

            // --- MODE: ORGANISATION ONLY ---
            if ($mode === 'organisation' || empty($data['campuses'])) {
                return $organisation;
            }

            // 3. Create Campuses (Legacy / Campuses and Courses Mode)
            $campusMap = []; // Name -> Campus instance
            if ($targetOrgId && $organisation->relationLoaded('campuses')) {
                foreach ($organisation->campuses as $existingCampus) {
                    $campusMap[$existingCampus->campus_name] = $existingCampus;
                }
            }

            $campusesInput = $data['campuses'] ?? [];

            if (!empty($campusesInput)) {
                foreach ($campusesInput as $index => $cInput) {
                    $campusName = !empty($cInput['campus_name']) ? $cInput['campus_name'] : ($organisation->name . ' - Campus ' . ($index + 1));
                    if (isset($campusMap[$campusName])) {
                        continue;
                    }
                    $campusSlug = Str::slug($campusName . '-' . Str::random(4));

                    $campus = Campus::create([
                        'id' => (string) Str::uuid(),
                        'organisation_id' => $organisation->id,
                        'campus_name' => $campusName,
                        'slug' => $campusSlug,
                        'campus_type' => in_array($cInput['campus_type'] ?? '', ['Main', 'Regional', 'Satellite']) ? $cInput['campus_type'] : ($index === 0 ? 'Main' : 'Regional'),
                        'established_year' => $this->parseInteger($cInput['established_year'] ?? $organisation->established_year),
                        'city' => $cInput['city'] ?? null,
                        'state' => $cInput['state'] ?? null,
                        'country' => $cInput['country'] ?? 'India',
                        'pincode' => $cInput['pincode'] ?? null,
                        'full_address' => $cInput['full_address'] ?? null,
                        'google_map_url' => $cInput['google_map_url'] ?? null,
                        'nearest_transport_hub' => $cInput['nearest_transport_hub'] ?? null,
                        'campus_area_acres' => $this->parseFloat($cInput['campus_area_acres'] ?? null, 0.0, 999999.99),
                        'academic_blocks_count' => $this->parseInteger($cInput['academic_blocks_count'] ?? 0) ?? 0,
                        'classrooms_count' => $this->parseInteger($cInput['classrooms_count'] ?? 0) ?? 0,
                        'smart_classrooms' => !empty($cInput['smart_classrooms']),
                        'laboratories_count' => $this->parseInteger($cInput['laboratories_count'] ?? 0) ?? 0,
                        'research_centers_count' => $this->parseInteger($cInput['research_centers_count'] ?? 0) ?? 0,
                        'library_available' => !empty($cInput['library_available']),
                        'library_books_count' => $this->parseInteger($cInput['library_books_count'] ?? 0) ?? 0,
                        'digital_library_access' => !empty($cInput['digital_library_access']),
                        'hostel_available' => !empty($cInput['hostel_available']),
                        'hostel_type' => in_array($cInput['hostel_type'] ?? '', ['Boys', 'Girls', 'Both', 'None']) ? $cInput['hostel_type'] : null,
                        'hostel_capacity' => $this->parseInteger($cInput['hostel_capacity'] ?? 0) ?? 0,
                        'food_facility' => $cInput['food_facility'] ?? null,
                        'medical_facility_available' => !empty($cInput['medical_facility_available']),
                        'sports_facilities' => $this->parseArray($cInput['sports_facilities'] ?? null),
                        'transport_available' => !empty($cInput['transport_available']),
                        'bus_routes_count' => $this->parseInteger($cInput['bus_routes_count'] ?? 0) ?? 0,
                        'parking_available' => !empty($cInput['parking_available']),
                        'cctv_coverage' => !empty($cInput['cctv_coverage']),
                        'security_staff_count' => $this->parseInteger($cInput['security_staff_count'] ?? 0) ?? 0,
                        'fire_safety_certified' => !empty($cInput['fire_safety_certified']),
                        'disaster_management_plan' => !empty($cInput['disaster_management_plan']),
                        'campus_email' => $cInput['campus_email'] ?? ($organisation->email ?? null),
                        'campus_website' => $cInput['campus_website'] ?? ($organisation->official_website ?? null),
                        'campus_contact_numbers' => $this->parseArray($cInput['campus_contact_numbers'] ?? null),
                        'status' => true,
                    ]);

                    $campusMap[$campusName] = $campus;
                    $campusMap[$index] = $campus;
                }
            }

            // Primary campus reference
            $primaryCampus = !empty($campusMap) ? reset($campusMap) : ($organisation->campuses()->first() ?? null);

            // 4. Create Departments
            $deptMap = []; // Name -> Department instance
            if ($targetOrgId && $organisation->relationLoaded('departments')) {
                foreach ($organisation->departments as $existingDept) {
                    $deptMap[$existingDept->name] = $existingDept;
                }
            }

            $deptsInput = $data['departments'] ?? [];

            if (!empty($deptsInput)) {
                foreach ($deptsInput as $index => $dInput) {
                    $deptName = $dInput['department_name'] ?? ('Department ' . ($index + 1));
                    if (isset($deptMap[$deptName])) {
                        continue;
                    }
                    $deptSlug = Str::slug($deptName . '-' . Str::random(4));

                    $department = Department::create([
                        'id' => (string) Str::uuid(),
                        'organisation_id' => $organisation->id,
                        'campus_id' => $primaryCampus ? $primaryCampus->id : null,
                        'department_name' => $deptName,
                        'department_code' => $dInput['department_code'] ?? null,
                        'department_type' => in_array($dInput['department_type'] ?? '', ['Academic', 'Clinical', 'Research', 'Interdisciplinary']) ? $dInput['department_type'] : 'Academic',
                        'established_year' => $this->parseInteger($dInput['established_year'] ?? null),
                        'slug' => $deptSlug,
                        'about_department' => $dInput['about_department'] ?? null,
                        'discipline_area' => $dInput['discipline_area'] ?? null,
                        'specializations_supported' => $this->parseArray($dInput['specializations_supported'] ?? null),
                        'education_levels_supported' => $this->parseArray($dInput['education_levels_supported'] ?? null),
                        'is_interdisciplinary' => !empty($dInput['is_interdisciplinary']),
                        'head_of_department_name' => $dInput['head_of_department_name'] ?? null,
                        'head_of_department_designation' => $dInput['head_of_department_designation'] ?? null,
                        'hod_appointment_type' => in_array($dInput['hod_appointment_type'] ?? '', ['Permanent', 'Acting']) ? $dInput['hod_appointment_type'] : null,
                        'hod_email' => $dInput['hod_email'] ?? null,
                        'department_office_contact' => $dInput['department_office_contact'] ?? null,
                        'faculty_count' => $this->parseInteger($dInput['faculty_count'] ?? null),
                        'curriculum_design_responsibility' => !empty($dInput['curriculum_design_responsibility']),
                        'exam_setting_responsibility' => !empty($dInput['exam_setting_responsibility']),
                        'research_programs_managed' => !empty($dInput['research_programs_managed']),
                        'phd_supervision_available' => !empty($dInput['phd_supervision_available']),
                        'industry_collaboration_supported' => !empty($dInput['industry_collaboration_supported']),
                        'department_labs_count' => (string)($this->parseInteger($dInput['department_labs_count'] ?? 0) ?? 0),
                        'specialized_labs_available' => !empty($dInput['specialized_labs_available']),
                        'research_centers_under_department' => $dInput['research_centers_under_department'] ?? null,
                        'department_library_section' => !empty($dInput['department_library_section']),
                        'classrooms_count' => (string)($this->parseInteger($dInput['classrooms_count'] ?? 0) ?? 0),
                        'research_publications_count' => $this->parseInteger($dInput['research_publications_count'] ?? 0) ?? 0,
                        'funded_projects_count' => $this->parseInteger($dInput['funded_projects_count'] ?? 0) ?? 0,
                        'patents_filed_count' => $this->parseInteger($dInput['patents_filed_count'] ?? 0) ?? 0,
                        'industry_projects_count' => $this->parseInteger($dInput['industry_projects_count'] ?? 0) ?? 0,
                        'department_website_url' => $dInput['department_website_url'] ?? null,
                        'department_email' => $dInput['department_email'] ?? null,
                        'department_notice_board_url' => $dInput['department_notice_board_url'] ?? null,
                        'status' => 'Active',
                        'visibility' => 'Public',
                    ]);

                    $deptMap[$deptName] = $department;
                }
            }

            // 5. Create Courses & OrganisationCourses (Strictly link to existing Master records, NEVER create new masters)
            $coursesInput = $data['courses'] ?? [];

            if (!empty($coursesInput)) {
                foreach ($coursesInput as $cData) {
                    $courseName = $cData['course_name'] ?? $cData['name'] ?? null;
                    $shortName = $cData['short_name'] ?? null;
                    $courseId = !empty($cData['course_id']) ? (int)$cData['course_id'] : null;

                    // Resolve Master Course from existing records
                    $masterCourse = $this->resolveMasterCourse($courseId, $courseName, $shortName);

                    // Resolve Program Level Master (NEVER CREATE)
                    $programLevelId = null;
                    if (!empty($cData['program_level_id'])) {
                        $pl = ProgramLevel::find($cData['program_level_id']);
                        if ($pl) $programLevelId = $pl->id;
                    } elseif (!empty($cData['program_level'])) {
                        $plTitle = trim($cData['program_level']);
                        $pl = ProgramLevel::where('title', 'like', '%' . $plTitle . '%')->first();
                        if ($pl) $programLevelId = $pl->id;
                    }
                    if (!$programLevelId && $masterCourse && $masterCourse->program_level_id) {
                        $programLevelId = $masterCourse->program_level_id;
                    }

                    // Resolve Stream Offered Master (NEVER CREATE)
                    $streamId = null;
                    if (!empty($cData['stream_offered_id'])) {
                        $st = StreamOffered::find($cData['stream_offered_id']);
                        if ($st) $streamId = $st->id;
                    } elseif (!empty($cData['stream'])) {
                        $stTitle = trim($cData['stream']);
                        $st = StreamOffered::where('title', 'like', '%' . $stTitle . '%')->first();
                        if ($st) $streamId = $st->id;
                    }
                    if (!$streamId && $masterCourse && $masterCourse->stream_offered_id) {
                        $streamId = $masterCourse->stream_offered_id;
                    }

                    // Resolve Discipline Master (NEVER CREATE)
                    $disciplineId = null;
                    if (!empty($cData['discipline_id'])) {
                        $disc = Discipline::find($cData['discipline_id']);
                        if ($disc) $disciplineId = $disc->id;
                    } elseif (!empty($cData['discipline'])) {
                        $discTitle = trim($cData['discipline']);
                        $disc = Discipline::where('title', 'like', '%' . $discTitle . '%')->first();
                        if ($disc) $disciplineId = $disc->id;
                    }
                    if (!$disciplineId && $masterCourse && $masterCourse->discipline_id) {
                        $disciplineId = $masterCourse->discipline_id;
                    }

                    // Map to Department
                    $deptId = null;
                    if (!empty($cData['department_name']) && isset($deptMap[$cData['department_name']])) {
                        $deptId = $deptMap[$cData['department_name']]->id;
                    } elseif (!empty($deptMap)) {
                        $firstDept = reset($deptMap);
                        $deptId = $firstDept->id;
                    }

                    // Map to Campus
                    $campusId = null;
                    if (!empty($cData['campus_name']) && isset($campusMap[$cData['campus_name']])) {
                        $campusId = $campusMap[$cData['campus_name']]->id;
                    } else {
                        $campusId = $primaryCampus ? $primaryCampus->id : null;
                    }

                    $displayCourseName = $masterCourse ? $masterCourse->name : ($courseName ?: 'Academic Program');

                    $rawTotalFees = !empty($cData['total_fees']) ? $cData['total_fees'] : ($cData['fees'] ?? null);

                    // Create Organisation Course
                    OrganisationCourse::create([
                        'organisation_id' => $organisation->id,
                        'campus_id' => $campusId,
                        'department_id' => $deptId,
                        'course_id' => $masterCourse ? $masterCourse->id : null,
                        'academic_unit_name' => Str::limit($cData['academic_unit_name'] ?? $displayCourseName, 250, ''),
                        'slug' => Str::slug(Str::limit($displayCourseName, 100, '') . '-' . Str::random(4)),
                        'mode' => in_array($cData['mode'] ?? '', ['Regular', 'Online', 'Distance', 'Part-time']) ? $cData['mode'] : 'Regular',
                        'duration' => Str::limit($cData['duration'] ?? ($masterCourse->duration ?? '3 Years'), 250, ''),
                        'fees' => !empty($cData['fees']) ? Str::limit((string)$cData['fees'], 250, '') : null,
                        'total_fees' => $this->parseFee($rawTotalFees),
                        'fees_structure' => $cData['fees_structure'] ?? null,
                        'annual_fee_range' => !empty($cData['annual_fee_range']) ? Str::limit((string)$cData['annual_fee_range'], 250, '') : null,
                        'admission_fee' => !empty($cData['admission_fee']) ? Str::limit((string)$cData['admission_fee'], 250, '') : null,
                        'eligibility' => $cData['eligibility'] ?? null,
                        'admission_process' => $cData['admission_process'] ?? null,
                        'provisional_admission' => !empty($cData['provisional_admission']),
                        'installment_available' => !empty($cData['installment_available']),
                        'scholarship_available' => !empty($cData['scholarship_available']),
                        'refund_policy_available' => !empty($cData['refund_policy_available']),
                        'roi' => in_array($cData['roi'] ?? '', ['Low', 'Medium', 'High']) ? $cData['roi'] : null,
                        'curriculum' => $cData['curriculum'] ?? null,
                        'career_prospects' => $cData['career_prospects'] ?? null,
                        'placement_details' => $cData['placement_details'] ?? null,
                        'rating' => $this->parseFloat($cData['rating'] ?? null, 0.0, 9.9),
                        'industrial_collaboration' => $cData['industrial_collaboration'] ?? null,
                        'internship_ranking' => $cData['internship_ranking'] ?? null,
                        'program_level_id' => $programLevelId,
                        'stream_offered_id' => $streamId,
                        'discipline_id' => $disciplineId,
                        'status' => true,
                    ]);
                }
            }

            return $organisation;
        });
    }

    /**
     * Download and store remote image locally or return existing path
     */
    protected function handleRemoteOrLocalImage(?string $url, string $type = 'logo', int $orgTypeId = 1): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        try {
            $path = match ($orgTypeId) {
                3 => 'media/institutes',
                4 => 'media/schools',
                6 => 'media/counselling_bodies',
                7 => 'media/regulatory_bodies',
                default => 'media/universities'
            };

            $dir = public_path($path);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $ext = 'png';
            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH) ?? '');
            if (!empty($pathInfo['extension']) && in_array(strtolower($pathInfo['extension']), ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'])) {
                $ext = strtolower($pathInfo['extension']);
            }

            $fileName = time() . '_' . uniqid() . '_' . $type . '.' . $ext;
            $response = \Illuminate\Support\Facades\Http::timeout(15)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ])->get($url);

            if ($response->successful() && strlen($response->body()) > 100) {
                file_put_contents($dir . '/' . $fileName, $response->body());
                return $path . '/' . $fileName;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Could not download remote image ({$url}): " . $e->getMessage());
        }

        return $url;
    }
}
