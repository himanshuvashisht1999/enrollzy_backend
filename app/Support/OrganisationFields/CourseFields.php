<?php

namespace App\Support\OrganisationFields;

/**
 * Which programme (organisation_courses) fields belong to which organisation type, and how the ones the hand-written edit blocks do not
 * show are drawn. One source of truth for three things:
 *   1. the field catalogue (a University is not asked for coaching-batch or school-board fields);
 *   2. the extra inputs on the programme edit page (everything relevant that the type's own block does not already contain);
 *   3. the save step (blank number boxes become NULL, not an invalid '').
 * Types: 1 University, 2 College, 3 Institute (coaching), 4 School, 9 E-Learning platform. The other types have no programmes.
 */
class CourseFields
{
    public const TYPES = [1, 2, 3, 4, 9];
    private const HIGHER = [1, 2, 3, 9];
    private const COACHING = [3, 9];

    /** field => types it applies to. A field that is not listed applies to every programme type. */
    private const ONLY = [
        'course_id' => self::HIGHER, 'program_level_id' => self::HIGHER, 'stream_offered_id' => self::HIGHER, 'discipline_id' => self::HIGHER,
        'duration' => self::HIGHER, 'delivery_mode' => self::HIGHER, 'mode' => self::HIGHER, 'average_course_fee_range' => self::HIGHER,
        'career_prospects' => self::HIGHER, 'provisional_admission' => [1, 2], 'placement_details' => [1, 2, 9], 'rating' => [1, 2, 9], 'roi' => [1, 2, 9],
        'industrial_collaboration' => [1, 2], 'internship_ranking' => [1, 2],
        'transport_fee' => [4], 'hostel_fee' => [4],
        'school_type' => [4], 'education_board' => [4], 'board_affiliation_number' => [4], 'medium_of_instruction' => [4], 'grade_range' => [4],
        'student_strength' => [4], 'total_teachers' => [4], 'student_teacher_ratio' => [4], 'average_class_size' => [4],
        'total_batches' => self::COACHING, 'average_batch_size' => self::COACHING, 'separate_batches_for_droppers' => self::COACHING, 'merit_based_batching' => self::COACHING,
        'total_faculty_count' => self::COACHING, 'senior_faculty_count' => self::COACHING, 'average_faculty_experience_years' => self::COACHING,
        'visiting_faculty_available' => self::COACHING, 'doubt_solving_mode' => self::COACHING, 'personal_mentorship_available' => self::COACHING,
        'extra_classes_for_weak_students' => self::COACHING, 'parent_counselling_available' => self::COACHING, 'study_material_type' => self::COACHING,
        'dpp_provided' => self::COACHING, 'test_series_available' => self::COACHING, 'tests_per_month' => self::COACHING, 'online_test_platform_available' => self::COACHING,
        'total_selections_all_time' => self::COACHING, 'selections_last_year' => self::COACHING, 'highest_rank_achieved' => self::COACHING, 'average_selection_rate' => self::COACHING,
    ];

    /** Fields the hand-written block of each type already contains (the extra-fields partial must not draw them twice). */
    private const HAND_CODED = [
        1 => ['course_id', 'mode', 'total_fees', 'fees_structure', 'eligibility', 'admission_process', 'provisional_admission', 'curriculum', 'career_prospects', 'placement_details', 'rating', 'roi', 'industrial_collaboration', 'internship_ranking', 'course_languages'],
        2 => ['course_id', 'mode', 'total_fees', 'fees_structure', 'eligibility', 'admission_process', 'provisional_admission', 'curriculum', 'career_prospects', 'placement_details', 'rating', 'roi', 'industrial_collaboration', 'internship_ranking', 'course_languages'],
        3 => ['academic_unit_name', 'course_id', 'delivery_mode', 'total_fees', 'course_languages', 'total_selections_all_time', 'highest_rank_achieved', 'average_course_fee_range'],
        4 => ['academic_unit_name', 'school_type', 'course_languages', 'total_fees', 'education_board', 'grade_range', 'medium_of_instruction', 'student_strength', 'total_teachers', 'average_class_size', 'transport_fee', 'hostel_fee'],
        9 => ['course_id', 'mode', 'duration', 'total_fees', 'course_languages', 'rating', 'roi', 'eligibility', 'curriculum', 'career_prospects', 'fees_structure'],
    ];

    /** How each extra input is drawn. kind: text | number | select | switch | textarea | master. */
    public const DEFS = [
        'academic_unit_name' => ['text', 'col' => 6, 'hint' => 'Shown instead of the master course name when filled'],
        'program_level_id' => ['master', 'model' => \App\Models\ProgramLevel::class, 'col' => 4],
        'stream_offered_id' => ['master', 'model' => \App\Models\StreamOffered::class, 'col' => 4],
        'discipline_id' => ['master', 'model' => \App\Models\Discipline::class, 'col' => 4],
        'duration' => ['text', 'ph' => 'e.g. 4 Years'],
        'mode' => ['select', 'options' => ['Regular', 'Online', 'Distance', 'Part-time']],
        'placement_details' => ['textarea'],
        'delivery_mode' => ['select', 'options' => ['Classroom', 'Online', 'Hybrid']],
        'fees' => ['text', 'ph' => 'e.g. 1,50,000 per year'],
        'annual_fee_range' => ['text', 'ph' => 'e.g. ₹1.5L - ₹2.5L'],
        'admission_fee' => ['text'],
        'fee_payment_frequency' => ['select', 'options' => ['Annual', 'Semester', 'Quarterly', 'Monthly', 'One-time']],
        'average_course_fee_range' => ['text'],
        'installment_available' => ['switch'], 'scholarship_available' => ['switch'], 'refund_policy_available' => ['switch'],
        'eligibility' => ['textarea'], 'admission_process' => ['textarea'], 'curriculum' => ['textarea'], 'fees_structure' => ['textarea'], 'career_prospects' => ['textarea'],
        'board_affiliation_number' => ['text'], 'student_teacher_ratio' => ['text', 'ph' => 'e.g. 20:1'],
        'total_batches' => ['number'], 'average_batch_size' => ['number'], 'separate_batches_for_droppers' => ['switch'], 'merit_based_batching' => ['switch'],
        'total_faculty_count' => ['number'], 'senior_faculty_count' => ['number'], 'average_faculty_experience_years' => ['number'],
        'visiting_faculty_available' => ['switch'], 'doubt_solving_mode' => ['select', 'options' => ['One-on-one', 'Doubt desk', 'Online chat', 'Mixed']],
        'personal_mentorship_available' => ['switch'], 'extra_classes_for_weak_students' => ['switch'], 'parent_counselling_available' => ['switch'],
        'study_material_type' => ['text', 'col' => 8, 'ph' => 'e.g. Printed modules + online'],
        'dpp_provided' => ['switch'], 'test_series_available' => ['switch'], 'tests_per_month' => ['number'], 'online_test_platform_available' => ['switch'],
        'selections_last_year' => ['text'], 'average_selection_rate' => ['text', 'ph' => 'e.g. 62%'],
        'student_strength' => ['text'], 'total_teachers' => ['text'], 'average_class_size' => ['text'], 'highest_rank_achieved' => ['text'], 'total_selections_all_time' => ['text'],
        'verified_reviews_only' => ['switch'],
        'meta_title' => ['text', 'col' => 12], 'meta_description' => ['textarea'],
    ];

    /** Plain integer columns (nullable): a blank box must be saved as NULL. */
    public const INT_COLUMNS = ['total_batches', 'average_batch_size', 'total_faculty_count', 'senior_faculty_count', 'average_faculty_experience_years', 'tests_per_month'];
    /** Columns whose blank value is saved as NULL (ids and the like). */
    public const NULLABLE_BLANK = ['program_level_id', 'stream_offered_id', 'discipline_id'];

    public static function appliesTo(string $field, int $typeId): bool
    {
        if (!in_array($typeId, self::TYPES, true)) return false;
        return !isset(self::ONLY[$field]) || in_array($typeId, self::ONLY[$field], true);
    }

    /** Catalogue field names relevant to this organisation type (null input = every type). Types without programmes get an empty list. */
    public static function filter(array $names, int $typeId): array
    {
        return array_values(array_filter($names, fn ($n) => self::appliesTo($n, $typeId)));
    }

    /** Relevant fields the type's own block does not draw: these are drawn by the extra-fields partial. In catalogue order (order of DEFS). */
    public static function extra(int $typeId): array
    {
        $hand = array_flip(self::HAND_CODED[$typeId] ?? []);
        $out = [];
        foreach (array_keys(self::DEFS) as $f) {
            if (self::appliesTo($f, $typeId) && !isset($hand[$f])) $out[] = $f;
        }
        return $out;
    }
}
