@extends('admin.layouts.master')

@section('title', 'Manage Interview Stage - ' . $exam->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Manage Interview Stage</h4>
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

    <form action="{{ route('admin.exams.interview.update', $exam->id) }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <ul class="nav nav-pills card-header-pills" id="interviewTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="core-tab" data-bs-toggle="tab" data-bs-target="#core"
                            type="button">1. Core & Admin</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="format-tab" data-bs-toggle="tab" data-bs-target="#format"
                            type="button">2. Format & Logistics</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="merit-tab" data-bs-toggle="tab" data-bs-target="#merit"
                            type="button">3. Merit & Evaluation</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="eligibility-tab" data-bs-toggle="tab" data-bs-target="#eligibility"
                            type="button">4. Eligibility & Relaxations</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="support-tab" data-bs-toggle="tab" data-bs-target="#support"
                            type="button">5. Support & Fees</button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="interviewTabsContent">

                    {{-- TAB 1: CORE & ADMIN --}}
                    <div class="tab-pane fade show active" id="core" role="tabpanel">
                        <!-- 1️⃣ CORE INTERVIEW IDENTITY -->
                        <h6 class="fw-bold text-primary mb-3">1. Core Interview Identity</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Stage Name</label>
                                <select name="stage_name" class="form-select">
                                    <option value="Interview" {{ old('stage_name', $interview->stage_name ?? '') == 'Interview' ? 'selected' : '' }}>Interview</option>
                                    <option value="Personality Test" {{ old('stage_name', $interview->stage_name ?? '') == 'Personality Test' ? 'selected' : '' }}>Personality Test</option>
                                    <option value="Viva Voce" {{ old('stage_name', $interview->stage_name ?? '') == 'Viva Voce' ? 'selected' : '' }}>Viva Voce</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Stage Order</label>
                                <input type="number" name="stage_order" class="form-control"
                                    value="{{ old('stage_order', $interview->stage_order ?? 3) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Mandatory?</label>
                                <select name="mandatory" class="form-select">
                                    <option value="1" {{ old('mandatory', $interview->mandatory ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('mandatory', $interview->mandatory ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contribution Type</label>
                                <select name="stage_contribution_type" class="form-select">
                                    <option value="merit_deciding" {{ old('stage_contribution_type', $interview->stage_contribution_type ?? '') == 'merit_deciding' ? 'selected' : '' }}>
                                        Merit Deciding ✅ (UPSC)</option>
                                    <option value="qualifying_only" {{ old('stage_contribution_type', $interview->stage_contribution_type ?? '') == 'qualifying_only' ? 'selected' : '' }}>
                                        Qualifying Only</option>
                                    <option value="verification_only" {{ old('stage_contribution_type', $interview->stage_contribution_type ?? '') == 'verification_only' ? 'selected' : '' }}>Verification Only</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2️⃣ CONDUCTING AUTHORITY -->
                        <h6 class="fw-bold text-primary mb-3">2. Conducting Authority</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Conducting Body</label>
                                <select name="interview_conducting_body" class="form-select">
                                    <option value="">Select Body</option>
                                    <option value="UPSC" {{ old('interview_conducting_body', $interview->interview_conducting_body ?? '') == 'UPSC' ? 'selected' : '' }}>UPSC
                                    </option>
                                    <option value="State PSC" {{ old('interview_conducting_body', $interview->interview_conducting_body ?? '') == 'State PSC' ? 'selected' : '' }}>State
                                        PSC</option>
                                    <option value="University Selection Board" {{ old('interview_conducting_body', $interview->interview_conducting_body ?? '') == 'University Selection Board' ? 'selected' : '' }}>University Selection Board</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Panel Type</label>
                                <select name="interview_panel_type" class="form-select">
                                    <option value="Single Panel" {{ old('interview_panel_type', $interview->interview_panel_type ?? '') == 'Single Panel' ? 'selected' : '' }}>Single
                                        Panel</option>
                                    <option value="Multiple Panels" {{ old('interview_panel_type', $interview->interview_panel_type ?? '') == 'Multiple Panels' ? 'selected' : '' }}>
                                        Multiple Panels</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Centres Scope</label>
                                <select name="interview_centres_scope" class="form-select">
                                    <option value="National" {{ old('interview_centres_scope', $interview->interview_centres_scope ?? '') == 'National' ? 'selected' : '' }}>National
                                    </option>
                                    <option value="State" {{ old('interview_centres_scope', $interview->interview_centres_scope ?? '') == 'State' ? 'selected' : '' }}>State
                                    </option>
                                    <option value="Campus-based" {{ old('interview_centres_scope', $interview->interview_centres_scope ?? '') == 'Campus-based' ? 'selected' : '' }}>
                                        Campus-based</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Panel Constitution Guidelines</label>
                                <textarea name="panel_constitution_guidelines" class="form-control"
                                    rows="2">{{ old('panel_constitution_guidelines', $interview->panel_constitution_guidelines ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Official Guidelines URL</label>
                                <input type="url" name="official_interview_guidelines_url" class="form-control"
                                    value="{{ old('official_interview_guidelines_url', $interview->official_interview_guidelines_url ?? '') }}">
                            </div>
                        </div>

                        <!-- 1️⃣4️⃣ ADMIN & CONTROL -->
                        <h6 class="fw-bold text-primary mb-3">14. Admin & Control</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Stage Status</label>
                                <select name="stage_status" class="form-select">
                                    <option value="Scheduled" {{ old('stage_status', $interview->stage_status ?? '') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="Completed" {{ old('stage_status', $interview->stage_status ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Archived" {{ old('stage_status', $interview->stage_status ?? '') == 'Archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="Public" {{ old('visibility', $interview->visibility ?? '') == 'Public' ? 'selected' : '' }}>Public</option>
                                    <option value="Draft" {{ old('visibility', $interview->visibility ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Private" {{ old('visibility', $interview->visibility ?? '') == 'Private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control"
                                    rows="2">{{ old('remarks', $interview->remarks ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: FORMAT & LOGISTICS --}}
                    <div class="tab-pane fade" id="format" role="tabpanel">
                        <!-- 3️⃣ INTERVIEW FORMAT & MODE -->
                        <h6 class="fw-bold text-primary mb-3">3. Format & Mode</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Mode</label>
                                <select name="interview_mode" class="form-select">
                                    <option value="In-Person" {{ old('interview_mode', $interview->interview_mode ?? '') == 'In-Person' ? 'selected' : '' }}>In-Person</option>
                                    <option value="Online" {{ old('interview_mode', $interview->interview_mode ?? '') == 'Online' ? 'selected' : '' }}>Online</option>
                                    <option value="Hybrid" {{ old('interview_mode', $interview->interview_mode ?? '') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Duration (Minutes)</label>
                                <input type="number" name="interview_duration_minutes" class="form-control"
                                    value="{{ old('interview_duration_minutes', $interview->interview_duration_minutes ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">No. of Panellists</label>
                                <input type="number" name="number_of_panellists" class="form-control"
                                    value="{{ old('number_of_panellists', $interview->number_of_panellists ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Medium Switch Allowed?</label>
                                <select name="medium_switch_allowed" class="form-select">
                                    <option value="1" {{ old('medium_switch_allowed', $interview->medium_switch_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('medium_switch_allowed', $interview->medium_switch_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Language Options (Comma separated)</label>
                                <input type="text" name="language_options_raw" class="form-control"
                                    value="{{ old('language_options_raw', isset($interview->language_options) ? implode(',', (array) $interview->language_options) : '') }}"
                                    placeholder="Hindi, English, etc.">
                            </div>
                        </div>

                        <!-- 7️⃣ INTERVIEW PROCESS FLOW -->
                        <h6 class="fw-bold text-primary mb-3">7. Process Flow</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Process Steps (Comma separated)</label>
                                <input type="text" name="interview_process_steps_raw" class="form-control"
                                    value="{{ old('interview_process_steps_raw', isset($interview->interview_process_steps) ? implode(',', (array) $interview->interview_process_steps) : '') }}"
                                    placeholder="Document Verification, Panel Interview, Exit Briefing">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Identity Verification?</label>
                                <select name="identity_verification_required" class="form-select">
                                    <option value="1" {{ old('identity_verification_required', $interview->identity_verification_required ?? true) ? 'selected' : '' }}>Yes
                                    </option>
                                    <option value="0" {{ !old('identity_verification_required', $interview->identity_verification_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Biometric Req.?</label>
                                <select name="biometric_verification_required" class="form-select">
                                    <option value="1" {{ old('biometric_verification_required', $interview->biometric_verification_required ?? false) ? 'selected' : '' }}>Yes
                                    </option>
                                    <option value="0" {{ !old('biometric_verification_required', $interview->biometric_verification_required ?? false) ? 'selected' : '' }}>No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- 8️⃣ SCHEDULING & SLOT ALLOCATION -->
                        <h6 class="fw-bold text-primary mb-3">8. Scheduling & Slots</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Slot Booking Req.?</label>
                                <select name="slot_booking_required" class="form-select">
                                    <option value="1" {{ old('slot_booking_required', $interview->slot_booking_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('slot_booking_required', $interview->slot_booking_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Allocation Method</label>
                                <select name="slot_allocation_method" class="form-select">
                                    <option value="Automated" {{ old('slot_allocation_method', $interview->slot_allocation_method ?? '') == 'Automated' ? 'selected' : '' }}>
                                        Automated</option>
                                    <option value="Manual" {{ old('slot_allocation_method', $interview->slot_allocation_method ?? '') == 'Manual' ? 'selected' : '' }}>Manual
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rescheduling Allowed?</label>
                                <select name="rescheduling_allowed" class="form-select">
                                    <option value="1" {{ old('rescheduling_allowed', $interview->rescheduling_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('rescheduling_allowed', $interview->rescheduling_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rescheduling Conditions</label>
                                <textarea name="rescheduling_conditions" class="form-control"
                                    rows="2">{{ old('rescheduling_conditions', $interview->rescheduling_conditions ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Late Reporting Policy</label>
                                <textarea name="late_reporting_policy" class="form-control"
                                    rows="2">{{ old('late_reporting_policy', $interview->late_reporting_policy ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: MERIT & EVALUATION --}}
                    <div class="tab-pane fade" id="merit" role="tabpanel">
                        <!-- 4️⃣ EVALUATION PARAMETERS -->
                        <h6 class="fw-bold text-primary mb-3">4. Evaluation Parameters</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label">Evaluation Criteria (Select Multiple)</label>
                                <select name="evaluation_criteria[]" class="form-select select2" multiple>
                                    @foreach(['Personality', 'Communication Skills', 'Subject Knowledge', 'Analytical Ability', 'Leadership', 'Ethical Judgment'] as $opt)
                                        <option value="{{ $opt }}" {{ in_array($opt, (array) old('evaluation_criteria', $interview->evaluation_criteria ?? [])) ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Criteria Weightage Defined?</label>
                                <select name="criteria_weightage_defined" class="form-select">
                                    <option value="1" {{ old('criteria_weightage_defined', $interview->criteria_weightage_defined ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('criteria_weightage_defined', $interview->criteria_weightage_defined ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- 5️⃣ MARKING & MERIT LOGIC -->
                        <h6 class="fw-bold text-primary mb-3">5. Marking & Merit Logic</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label class="form-label">Marks Applicable?</label>
                                <select name="marks_applicable" class="form-select">
                                    <option value="1" {{ old('marks_applicable', $interview->marks_applicable ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('marks_applicable', $interview->marks_applicable ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Marks</label>
                                <input type="number" step="0.01" name="maximum_marks" class="form-control"
                                    value="{{ old('maximum_marks', $interview->maximum_marks ?? '') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Qualifying</label>
                                <input type="number" step="0.01" name="minimum_qualifying_marks" class="form-control"
                                    value="{{ old('minimum_qualifying_marks', $interview->minimum_qualifying_marks ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category Cutoff?</label>
                                <select name="category_wise_cutoff_applicable" class="form-select">
                                    <option value="1" {{ old('category_wise_cutoff_applicable', $interview->category_wise_cutoff_applicable ?? false) ? 'selected' : '' }}>Yes
                                    </option>
                                    <option value="0" {{ !old('category_wise_cutoff_applicable', $interview->category_wise_cutoff_applicable ?? false) ? 'selected' : '' }}>No
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Weightage (%)</label>
                                <input type="number" step="0.01" name="weightage_percentage" class="form-control"
                                    value="{{ old('weightage_percentage', $interview->weightage_percentage ?? '') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Norm.?</label>
                                <select name="normalization_applied" class="form-select">
                                    <option value="1" {{ old('normalization_applied', $interview->normalization_applied ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('normalization_applied', $interview->normalization_applied ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- 9️⃣ RESULT & OUTPUT -->
                        <h6 class="fw-bold text-primary mb-3">9. Result & Output</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Result Type</label>
                                <select name="interview_result_type" class="form-select">
                                    <option value="Marks" {{ old('interview_result_type', $interview->interview_result_type ?? '') == 'Marks' ? 'selected' : '' }}>Marks</option>
                                    <option value="Pass/Fail" {{ old('interview_result_type', $interview->interview_result_type ?? '') == 'Pass/Fail' ? 'selected' : '' }}>Pass/Fail
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Visibility</label>
                                <select name="interview_result_visibility" class="form-select">
                                    <option value="Candidate Login" {{ old('interview_result_visibility', $interview->interview_result_visibility ?? '') == 'Candidate Login' ? 'selected' : '' }}>Candidate Login</option>
                                    <option value="Public" {{ old('interview_result_visibility', $interview->interview_result_visibility ?? '') == 'Public' ? 'selected' : '' }}>Public
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Declaration Date</label>
                                <input type="date" name="interview_result_declaration_date" class="form-control"
                                    value="{{ old('interview_result_declaration_date', isset($interview->interview_result_declaration_date) ? $interview->interview_result_declaration_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- TAB 4: ELIGIBILITY & RELAXATIONS --}}
                    <div class="tab-pane fade" id="eligibility" role="tabpanel">
                        <!-- 6️⃣ ELIGIBILITY TO APPEAR -->
                        <h6 class="fw-bold text-primary mb-3">6. Eligibility to Appear</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Prev. Stage Qualification Req.?</label>
                                <select name="previous_stage_qualification_required" class="form-select">
                                    <option value="1" {{ old('previous_stage_qualification_required', $interview->previous_stage_qualification_required ?? true) ? 'selected' : '' }}>Yes
                                    </option>
                                    <option value="0" {{ !old('previous_stage_qualification_required', $interview->previous_stage_qualification_required ?? true) ? 'selected' : '' }}>No
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shortlisting Basis</label>
                                <select name="shortlisting_basis" class="form-select">
                                    <option value="Rank-based" {{ old('shortlisting_basis', $interview->shortlisting_basis ?? '') == 'Rank-based' ? 'selected' : '' }}>Rank-based</option>
                                    <option value="Marks-based" {{ old('shortlisting_basis', $interview->shortlisting_basis ?? '') == 'Marks-based' ? 'selected' : '' }}>Marks-based</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Documents Required (Comma separated)</label>
                                <input type="text" name="documents_required_for_interview_call_raw" class="form-control"
                                    value="{{ old('documents_required_for_interview_call_raw', isset($interview->documents_required_for_interview_call) ? implode(',', (array) $interview->documents_required_for_interview_call) : '') }}">
                            </div>
                        </div>

                        <!-- 1️⃣1️⃣ SPECIAL RULES & RELAXATIONS -->
                        <h6 class="fw-bold text-primary mb-3">11. Special Rules & Relaxations</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Category Relaxations (Comma separated)</label>
                                <input type="text" name="category_relaxations_raw" class="form-control"
                                    value="{{ old('category_relaxations_raw', isset($interview->category_relaxations) ? implode(',', (array) $interview->category_relaxations) : '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PWD Accommodations Available?</label>
                                <select name="pwd_accommodations_available" class="form-select">
                                    <option value="1" {{ old('pwd_accommodations_available', $interview->pwd_accommodations_available ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('pwd_accommodations_available', $interview->pwd_accommodations_available ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ex-Servicemen Relaxations</label>
                                <textarea name="ex_servicemen_relaxations" class="form-control"
                                    rows="2">{{ old('ex_servicemen_relaxations', $interview->ex_servicemen_relaxations ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender Specific Guidelines</label>
                                <textarea name="gender_specific_guidelines" class="form-control"
                                    rows="2">{{ old('gender_specific_guidelines', $interview->gender_specific_guidelines ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 5: SUPPORT & FEES --}}
                    <div class="tab-pane fade" id="support" role="tabpanel">
                        <!-- 10️⃣ APPEAL / REVIEW -->
                        <h6 class="fw-bold text-primary mb-3">10. Appeal / Review</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Appeal Allowed?</label>
                                <select name="appeal_allowed" class="form-select">
                                    <option value="1" {{ old('appeal_allowed', $interview->appeal_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('appeal_allowed', $interview->appeal_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Time Limit (Days)</label>
                                <input type="number" name="appeal_time_limit_days" class="form-control"
                                    value="{{ old('appeal_time_limit_days', $interview->appeal_time_limit_days ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fee Required?</label>
                                <select name="appeal_fee_required" class="form-select">
                                    <option value="1" {{ old('appeal_fee_required', $interview->appeal_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('appeal_fee_required', $interview->appeal_fee_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fee Amount</label>
                                <input type="number" name="appeal_fee_amount" class="form-control"
                                    value="{{ old('appeal_fee_amount', $interview->appeal_fee_amount ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Appeal Process Description</label>
                                <textarea name="appeal_process_description" class="form-control"
                                    rows="2">{{ old('appeal_process_description', $interview->appeal_process_description ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Final Decision Authority</label>
                                <input type="text" name="final_decision_authority" class="form-control"
                                    value="{{ old('final_decision_authority', $interview->final_decision_authority ?? '') }}">
                            </div>
                        </div>

                        <!-- 1️⃣2️⃣ FEES -->
                        <h6 class="fw-bold text-primary mb-3">12. Interview Fees</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Fee Required?</label>
                                <select name="interview_fee_required" class="form-select">
                                    <option value="1" {{ old('interview_fee_required', $interview->interview_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('interview_fee_required', $interview->interview_fee_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fee Amount</label>
                                <input type="number" name="interview_fee_amount" class="form-control"
                                    value="{{ old('interview_fee_amount', $interview->interview_fee_amount ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Refundable?</label>
                                <select name="fee_refundable" class="form-select">
                                    <option value="1" {{ old('fee_refundable', $interview->fee_refundable ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('fee_refundable', $interview->fee_refundable ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Payment Modes (Comma separated)</label>
                                <input type="text" name="payment_modes_raw" class="form-control"
                                    value="{{ old('payment_modes_raw', isset($interview->payment_modes) ? implode(',', (array) $interview->payment_modes) : '') }}">
                            </div>
                        </div>

                        <!-- 1️⃣3️⃣ DISCLAIMERS & REFERENCES -->
                        <h6 class="fw-bold text-primary mb-3">13. Disclaimers & References</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Interview Disclaimer Text</label>
                                <textarea name="interview_disclaimer_text" class="form-control"
                                    rows="3">{{ old('interview_disclaimer_text', $interview->interview_disclaimer_text ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Information Source</label>
                                <input type="text" name="information_source" class="form-control"
                                    value="{{ old('information_source', $interview->information_source ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Verified On</label>
                                <input type="date" name="last_verified_on" class="form-control"
                                    value="{{ old('last_verified_on', isset($interview->last_verified_on) ? $interview->last_verified_on->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-end py-3 border-top">
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> Save Interview Details
                </button>
            </div>
        </div>
    </form>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
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

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
        });
    </script>
@endpush