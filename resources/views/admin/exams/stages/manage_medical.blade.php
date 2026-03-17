@extends('admin.layouts.master')

@section('title', 'Manage Medical Stage - ' . $exam->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Manage Medical Stage</h4>
            <p class="text-muted mb-0">{{ $exam->name }}</p>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Exams
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.exams.medical.update', $exam->id) }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <ul class="nav nav-pills card-header-pills" id="medicalTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="identity-tab" data-bs-toggle="tab" data-bs-target="#identity"
                            type="button">1. Identity & Authority</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="standards-tab" data-bs-toggle="tab" data-bs-target="#standards"
                            type="button">2. Physical & Vision Standards</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="procedure-tab" data-bs-toggle="tab" data-bs-target="#procedure"
                            type="button">3. Checks & Procedures</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="outcome-tab" data-bs-toggle="tab" data-bs-target="#outcome"
                            type="button">4. Review & Outcome</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="support-tab" data-bs-toggle="tab" data-bs-target="#support"
                            type="button">5. Support & relaxations</button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="medicalTabsContent">

                    {{-- TAB 1: IDENTITY & AUTHORITY --}}
                    <div class="tab-pane fade show active" id="identity" role="tabpanel">
                        <!-- 1️⃣ CORE MEDICAL STAGE IDENTITY -->
                        <h6 class="fw-bold text-primary mb-3">1. Core Medical Stage Identity</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Stage Name</label>
                                <select name="stage_name" class="form-select">
                                    <option value="Medical Examination" {{ old('stage_name', $medical->stage_name ?? 'Medical Examination') == 'Medical Examination' ? 'selected' : '' }}>Medical
                                        Examination</option>
                                    <option value="Physical & Medical Test" {{ old('stage_name', $medical->stage_name ?? '') == 'Physical & Medical Test' ? 'selected' : '' }}>Physical & Medical Test</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Stage Order</label>
                                <input type="number" name="stage_order" class="form-control"
                                    value="{{ old('stage_order', $medical->stage_order ?? 4) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Mandatory?</label>
                                <select name="mandatory" class="form-select">
                                    <option value="1" {{ old('mandatory', $medical->mandatory ?? true) ? 'selected' : '' }}>
                                        Yes</option>
                                    <option value="0" {{ !old('mandatory', $medical->mandatory ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contribution Type</label>
                                <select name="stage_contribution_type" class="form-select">
                                    <option value="qualifying_only" {{ old('stage_contribution_type', $medical->stage_contribution_type ?? 'qualifying_only') == 'qualifying_only' ? 'selected' : '' }}>Qualifying Only ✅</option>
                                    <option value="merit_deciding" {{ old('stage_contribution_type', $medical->stage_contribution_type ?? '') == 'merit_deciding' ? 'selected' : '' }}>
                                        Merit Deciding (rare)</option>
                                    <option value="verification_only" {{ old('stage_contribution_type', $medical->stage_contribution_type ?? '') == 'verification_only' ? 'selected' : '' }}>
                                        Verification Only</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2️⃣ MEDICAL AUTHORITY & CONDUCT -->
                        <h6 class="fw-bold text-primary mb-3">2. Medical Authority & Conduct</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Conducting Authority</label>
                                <select name="medical_conducting_authority" class="form-select">
                                    <option value="Armed Forces Medical Board" {{ old('medical_conducting_authority', $medical->medical_conducting_authority ?? '') == 'Armed Forces Medical Board' ? 'selected' : '' }}>Armed Forces Medical Board</option>
                                    <option value="Government Hospital" {{ old('medical_conducting_authority', $medical->medical_conducting_authority ?? '') == 'Government Hospital' ? 'selected' : '' }}>Government Hospital</option>
                                    <option value="Designated Medical Board" {{ old('medical_conducting_authority', $medical->medical_conducting_authority ?? '') == 'Designated Medical Board' ? 'selected' : '' }}>Designated Medical Board</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Board Type</label>
                                <select name="medical_board_type" class="form-select">
                                    <option value="Central" {{ old('medical_board_type', $medical->medical_board_type ?? '') == 'Central' ? 'selected' : '' }}>Central</option>
                                    <option value="State" {{ old('medical_board_type', $medical->medical_board_type ?? '') == 'State' ? 'selected' : '' }}>State</option>
                                    <option value="Service-specific" {{ old('medical_board_type', $medical->medical_board_type ?? '') == 'Service-specific' ? 'selected' : '' }}>
                                        Service-specific</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Centres Scope</label>
                                <select name="medical_centres_scope" class="form-select">
                                    <option value="National" {{ old('medical_centres_scope', $medical->medical_centres_scope ?? '') == 'National' ? 'selected' : '' }}>National</option>
                                    <option value="Zonal" {{ old('medical_centres_scope', $medical->medical_centres_scope ?? '') == 'Zonal' ? 'selected' : '' }}>Zonal</option>
                                    <option value="State-wise" {{ old('medical_centres_scope', $medical->medical_centres_scope ?? '') == 'State-wise' ? 'selected' : '' }}>State-wise
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Centres List URL</label>
                                <input type="url" name="medical_centres_list_url" class="form-control"
                                    value="{{ old('medical_centres_list_url', $medical->medical_centres_list_url ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Guidelines URL</label>
                                <input type="url" name="official_medical_guidelines_url" class="form-control"
                                    value="{{ old('official_medical_guidelines_url', $medical->official_medical_guidelines_url ?? '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: PHYSICAL & VISION STANDARDS --}}
                    <div class="tab-pane fade" id="standards" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-0">3A. General Health Requirements</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gen Health Req?</label>
                                <select name="general_health_required" class="form-select">
                                    <option value="1" {{ old('general_health_required', $medical->general_health_required ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('general_health_required', $medical->general_health_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Free from Chronic Diseases?</label>
                                <select name="free_from_chronic_diseases" class="form-select">
                                    <option value="1" {{ old('free_from_chronic_diseases', $medical->free_from_chronic_diseases ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('free_from_chronic_diseases', $medical->free_from_chronic_diseases ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Physical Fitness Required?</label>
                                <select name="physical_fitness_required" class="form-select">
                                    <option value="1" {{ old('physical_fitness_required', $medical->physical_fitness_required ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('physical_fitness_required', $medical->physical_fitness_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-0">3B. Height / Weight / Chest Standards</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Height Requirements (Category/Gender wise)</label>
                                <textarea name="height_requirement" class="form-control"
                                    rows="2">{{ old('height_requirement', $medical->height_requirement ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weight Standard Reference</label>
                                <textarea name="weight_standard_reference" class="form-control"
                                    rows="2">{{ old('weight_standard_reference', $medical->weight_standard_reference ?? '') }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Chest Measurement Req?</label>
                                <select name="chest_measurement_required" class="form-select">
                                    <option value="1" {{ old('chest_measurement_required', $medical->chest_measurement_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('chest_measurement_required', $medical->chest_measurement_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Chest Expansion Req?</label>
                                <select name="chest_expansion_required" class="form-select">
                                    <option value="1" {{ old('chest_expansion_required', $medical->chest_expansion_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('chest_expansion_required', $medical->chest_expansion_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-0">3C. Vision Standards</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vision Test Req?</label>
                                <select name="vision_test_required" class="form-select">
                                    <option value="1" {{ old('vision_test_required', $medical->vision_test_required ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('vision_test_required', $medical->vision_test_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Color Vision Req?</label>
                                <select name="color_vision_required" class="form-select">
                                    <option value="1" {{ old('color_vision_required', $medical->color_vision_required ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('color_vision_required', $medical->color_vision_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Night Blindness Disq?</label>
                                <select name="night_blindness_disqualifying" class="form-select">
                                    <option value="1" {{ old('night_blindness_disqualifying', $medical->night_blindness_disqualifying ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('night_blindness_disqualifying', $medical->night_blindness_disqualifying ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Spectacles Allowed?</label>
                                <select name="spectacles_allowed" class="form-select">
                                    <option value="1" {{ old('spectacles_allowed', $medical->spectacles_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('spectacles_allowed', $medical->spectacles_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Visual Acuity Standards</label>
                                <textarea name="visual_acuity_standards" class="form-control"
                                    rows="2">{{ old('visual_acuity_standards', $medical->visual_acuity_standards ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-0">3D. Hearing & Speech</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hearing Standard Req?</label>
                                <select name="hearing_standard_required" class="form-select">
                                    <option value="1" {{ old('hearing_standard_required', $medical->hearing_standard_required ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('hearing_standard_required', $medical->hearing_standard_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Speech Standard Req?</label>
                                <select name="speech_standard_required" class="form-select">
                                    <option value="1" {{ old('speech_standard_required', $medical->speech_standard_required ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('speech_standard_required', $medical->speech_standard_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: CHECKS & PROCEDURES --}}
                    <div class="tab-pane fade" id="procedure" role="tabpanel">
                        <h6 class="fw-bold text-primary mb-3">3E. System-wise Medical Checks</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-check form-switch p-2 border rounded">
                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                        name="cardiovascular_system_check" value="1" {{ old('cardiovascular_system_check', $medical->cardiovascular_system_check ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Cardiovascular Check</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-2 border rounded">
                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                        name="respiratory_system_check" value="1" {{ old('respiratory_system_check', $medical->respiratory_system_check ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Respiratory Check</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-2 border rounded">
                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                        name="neurological_system_check" value="1" {{ old('neurological_system_check', $medical->neurological_system_check ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Neurological Check</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-2 border rounded">
                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                        name="musculoskeletal_system_check" value="1" {{ old('musculoskeletal_system_check', $medical->musculoskeletal_system_check ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Musculoskeletal Check</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-2 border rounded">
                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                        name="mental_health_evaluation_required" value="1" {{ old('mental_health_evaluation_required', $medical->mental_health_evaluation_required ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Mental Health Eval</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">4. Disqualifying Conditions</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Temp Disqualifications (Comma separated)</label>
                                <input type="text" name="temporary_disqualifications_raw" class="form-control"
                                    value="{{ old('temporary_disqualifications_raw', isset($medical->temporary_disqualifications) ? implode(',', (array) $medical->temporary_disqualifications) : '') }}"
                                    placeholder="Underweight, Recent surgery">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Perm Disqualifications (Comma separated)</label>
                                <input type="text" name="permanent_disqualifications_raw" class="form-control"
                                    value="{{ old('permanent_disqualifications_raw', isset($medical->permanent_disqualifications) ? implode(',', (array) $medical->permanent_disqualifications) : '') }}"
                                    placeholder="Color blindness, Chronic illness">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tattoo Policy</label>
                                <input type="text" name="tattoo_policy" class="form-control"
                                    value="{{ old('tattoo_policy', $medical->tattoo_policy ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Surgical History Rules</label>
                                <input type="text" name="surgical_history_rules" class="form-control"
                                    value="{{ old('surgical_history_rules', $medical->surgical_history_rules ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pregnancy Rules</label>
                                <input type="text" name="pregnancy_rules" class="form-control"
                                    value="{{ old('pregnancy_rules', $medical->pregnancy_rules ?? '') }}">
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">5. Medical Test Procedure</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Procedure Steps (Comma separated)</label>
                                <input type="text" name="medical_exam_procedure_steps_raw" class="form-control"
                                    value="{{ old('medical_exam_procedure_steps_raw', isset($medical->medical_exam_procedure_steps) ? implode(',', (array) $medical->medical_exam_procedure_steps) : '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tests Conducted (Comma separated)</label>
                                <input type="text" name="tests_conducted_raw" class="form-control"
                                    value="{{ old('tests_conducted_raw', isset($medical->tests_conducted) ? implode(',', (array) $medical->tests_conducted) : '') }}"
                                    placeholder="Blood Test, Urine Test, X-Ray, ECG">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fasting Required?</label>
                                <select name="fasting_required" class="form-select">
                                    <option value="1" {{ old('fasting_required', $medical->fasting_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('fasting_required', $medical->fasting_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Duration</label>
                                <input type="text" name="medical_exam_duration" class="form-control"
                                    value="{{ old('medical_exam_duration', $medical->medical_exam_duration ?? '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- TAB 4: REVIEW & OUTCOME --}}
                    <div class="tab-pane fade" id="outcome" role="tabpanel">
                        <h6 class="fw-bold text-primary mb-3">6. Review / Appeal Process</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Review Allowed?</label>
                                <select name="medical_review_allowed" class="form-select">
                                    <option value="1" {{ old('medical_review_allowed', $medical->medical_review_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('medical_review_allowed', $medical->medical_review_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Appeal Limit (Days)</label>
                                <input type="number" name="appeal_time_limit_days" class="form-control"
                                    value="{{ old('appeal_time_limit_days', $medical->appeal_time_limit_days ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Appeal Fee Req?</label>
                                <select name="appeal_fee_required" class="form-select">
                                    <option value="1" {{ old('appeal_fee_required', $medical->appeal_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('appeal_fee_required', $medical->appeal_fee_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fee Amount</label>
                                <input type="number" name="appeal_fee_amount" class="form-control"
                                    value="{{ old('appeal_fee_amount', $medical->appeal_fee_amount ?? '') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Review Board Details</label>
                                <input type="text" name="review_medical_board_details" class="form-control"
                                    value="{{ old('review_medical_board_details', $medical->review_medical_board_details ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Final Decision Authority</label>
                                <input type="text" name="final_decision_authority" class="form-control"
                                    value="{{ old('final_decision_authority', $medical->final_decision_authority ?? '') }}">
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">7. Result & Outcome</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Result Type</label>
                                <select name="medical_result_type" class="form-select">
                                    <option value="Fit" {{ old('medical_result_type', $medical->medical_result_type ?? '') == 'Fit' ? 'selected' : '' }}>Fit</option>
                                    <option value="Temporarily Unfit" {{ old('medical_result_type', $medical->medical_result_type ?? '') == 'Temporarily Unfit' ? 'selected' : '' }}>
                                        Temporarily Unfit</option>
                                    <option value="Permanently Unfit" {{ old('medical_result_type', $medical->medical_result_type ?? '') == 'Permanently Unfit' ? 'selected' : '' }}>
                                        Permanently Unfit</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Temp Unfit Retest?</label>
                                <select name="temporary_unfit_retest_allowed" class="form-select">
                                    <option value="1" {{ old('temporary_unfit_retest_allowed', $medical->temporary_unfit_retest_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('temporary_unfit_retest_allowed', $medical->temporary_unfit_retest_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Retest Timeline (Days)</label>
                                <input type="number" name="retest_timeline_days" class="form-control"
                                    value="{{ old('retest_timeline_days', $medical->retest_timeline_days ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Result Visibility</label>
                                <select name="medical_result_visibility" class="form-select">
                                    <option value="Candidate Login" {{ old('medical_result_visibility', $medical->medical_result_visibility ?? '') == 'Candidate Login' ? 'selected' : '' }}>
                                        Candidate Login</option>
                                    <option value="Public" {{ old('medical_result_visibility', $medical->medical_result_visibility ?? '') == 'Public' ? 'selected' : '' }}>Public
                                    </option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">9. Date & Scheduling</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="medical_exam_start_date" class="form-control"
                                    value="{{ old('medical_exam_start_date', isset($medical->medical_exam_start_date) ? $medical->medical_exam_start_date->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="medical_exam_end_date" class="form-control"
                                    value="{{ old('medical_exam_end_date', isset($medical->medical_exam_end_date) ? $medical->medical_exam_end_date->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Slot Booking Req?</label>
                                <select name="slot_booking_required" class="form-select">
                                    <option value="1" {{ old('slot_booking_required', $medical->slot_booking_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('slot_booking_required', $medical->slot_booking_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Reporting Guidelines</label>
                                <textarea name="reporting_time_guidelines" class="form-control"
                                    rows="2">{{ old('reporting_time_guidelines', $medical->reporting_time_guidelines ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 5: SUPPORT & RELAXATIONS --}}
                    <div class="tab-pane fade" id="support" role="tabpanel">
                        <h6 class="fw-bold text-primary mb-3">8. Documents Required</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label">Documents (Comma separated)</label>
                                <input type="text" name="medical_documents_required_raw" class="form-control"
                                    value="{{ old('medical_documents_required_raw', isset($medical->medical_documents_required) ? implode(',', (array) $medical->medical_documents_required) : '') }}"
                                    placeholder="Medical History Form, Photo ID, Admit Card">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cert Format URL</label>
                                <input type="url" name="medical_certificate_format_url" class="form-control"
                                    value="{{ old('medical_certificate_format_url', $medical->medical_certificate_format_url ?? '') }}">
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">10. Fees</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Fee Required?</label>
                                <select name="medical_fee_required" class="form-select">
                                    <option value="1" {{ old('medical_fee_required', $medical->medical_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('medical_fee_required', $medical->medical_fee_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fee Amount</label>
                                <input type="number" name="medical_fee_amount" class="form-control"
                                    value="{{ old('medical_fee_amount', $medical->medical_fee_amount ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Refundable?</label>
                                <select name="fee_refundable" class="form-select">
                                    <option value="1" {{ old('fee_refundable', $medical->fee_refundable ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('fee_refundable', $medical->fee_refundable ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Payment Mode (Comma separated)</label>
                                <input type="text" name="payment_mode_raw" class="form-control"
                                    value="{{ old('payment_mode_raw', isset($medical->payment_mode) ? implode(',', (array) $medical->payment_mode) : '') }}">
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">11. Special Category Relaxations</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Gender Relaxation</label>
                                <textarea name="gender_based_relaxation_rules" class="form-control"
                                    rows="2">{{ old('gender_based_relaxation_rules', $medical->gender_based_relaxation_rules ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category Relaxation</label>
                                <textarea name="category_based_relaxation_rules" class="form-control"
                                    rows="2">{{ old('category_based_relaxation_rules', $medical->category_based_relaxation_rules ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ex-Servicemen Relaxation</label>
                                <textarea name="ex_servicemen_relaxation" class="form-control"
                                    rows="2">{{ old('ex_servicemen_relaxation', $medical->ex_servicemen_relaxation ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PWD Medical Rules</label>
                                <textarea name="pwd_medical_rules" class="form-control"
                                    rows="2">{{ old('pwd_medical_rules', $medical->pwd_medical_rules ?? '') }}</textarea>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">12. Disclaimers & Admin</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Disclaimer Text</label>
                                <textarea name="medical_disclaimer_text" class="form-control"
                                    rows="2">{{ old('medical_disclaimer_text', $medical->medical_disclaimer_text ?? '') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Source</label>
                                <input type="text" name="information_source" class="form-control"
                                    value="{{ old('information_source', $medical->information_source ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stage Status</label>
                                <select name="stage_status" class="form-select">
                                    <option value="Active" {{ old('stage_status', $medical->stage_status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Completed" {{ old('stage_status', $medical->stage_status ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Archived" {{ old('stage_status', $medical->stage_status ?? '') == 'Archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="Public" {{ old('visibility', $medical->visibility ?? '') == 'Public' ? 'selected' : '' }}>Public</option>
                                    <option value="Private" {{ old('visibility', $medical->visibility ?? '') == 'Private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control"
                                    rows="2">{{ old('remarks', $medical->remarks ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-end py-3 border-top">
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> Save Medical Stage Details
                </button>
            </div>
        </div>
    </form>
@endsection

@push('css')
    <style>
        .nav-pills .nav-link {
            color: #6c757d;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s;
        }

        .nav-pills .nav-link:hover {
            background-color: #f8f9fa;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        }

        .tab-content {
            padding: 10px 0;
        }

        h6.text-primary {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
    </style>
@endpush