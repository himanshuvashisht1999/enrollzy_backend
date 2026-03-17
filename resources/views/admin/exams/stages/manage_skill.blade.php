@extends('admin.layouts.master')

@section('title', 'Manage Skill Test Stage - ' . $exam->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Manage Skill Test Stage</h4>
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

    <form action="{{ route('admin.exams.skill.update', $exam->id) }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <ul class="nav nav-pills card-header-pills" id="skillTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="identity-tab" data-bs-toggle="tab" data-bs-target="#identity"
                            type="button">1. Identity & Purpose</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="requirements-tab" data-bs-toggle="tab" data-bs-target="#requirements"
                            type="button">2. Skills & Benchmarks</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="merit-tab" data-bs-toggle="tab" data-bs-target="#merit"
                            type="button">3. Merit & Logic</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="logistics-tab" data-bs-toggle="tab" data-bs-target="#logistics"
                            type="button">4. Mode & Logistics</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="support-tab" data-bs-toggle="tab" data-bs-target="#support"
                            type="button">5. Support & Legal</button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="skillTabsContent">

                    {{-- TAB 1: IDENTITY & PURPOSE --}}
                    <div class="tab-pane fade show active" id="identity" role="tabpanel">
                        <!-- 1️⃣ CORE SKILL STAGE IDENTITY -->
                        <h6 class="fw-bold text-primary mb-3">1. Core Skill Stage Identity</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Stage Name</label>
                                <select name="stage_name" class="form-select">
                                    <option value="Typing Test" {{ old('stage_name', $skill->stage_name ?? '') == 'Typing Test' ? 'selected' : '' }}>Typing Test</option>
                                    <option value="Skill Test" {{ old('stage_name', $skill->stage_name ?? 'Skill Test') == 'Skill Test' ? 'selected' : '' }}>Skill Test</option>
                                    <option value="Computer Proficiency Test" {{ old('stage_name', $skill->stage_name ?? '') == 'Computer Proficiency Test' ? 'selected' : '' }}>Computer Proficiency Test
                                    </option>
                                    <option value="Practical Test" {{ old('stage_name', $skill->stage_name ?? '') == 'Practical Test' ? 'selected' : '' }}>Practical Test</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Stage Order</label>
                                <input type="number" name="stage_order" class="form-control"
                                    value="{{ old('stage_order', $skill->stage_order ?? 5) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Mandatory?</label>
                                <select name="mandatory" class="form-select">
                                    <option value="1" {{ old('mandatory', $skill->mandatory ?? true) ? 'selected' : '' }}>
                                        Yes</option>
                                    <option value="0" {{ !old('mandatory', $skill->mandatory ?? true) ? 'selected' : '' }}>
                                        No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contribution Type</label>
                                <select name="stage_contribution_type" class="form-select">
                                    <option value="qualifying_only" {{ old('stage_contribution_type', $skill->stage_contribution_type ?? 'qualifying_only') == 'qualifying_only' ? 'selected' : '' }}>Qualifying Only ✅</option>
                                    <option value="merit_deciding" {{ old('stage_contribution_type', $skill->stage_contribution_type ?? '') == 'merit_deciding' ? 'selected' : '' }}>Merit
                                        Deciding</option>
                                    <option value="verification_only" {{ old('stage_contribution_type', $skill->stage_contribution_type ?? '') == 'verification_only' ? 'selected' : '' }}>
                                        Verification Only</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2️⃣ SKILL TEST CATEGORY -->
                        <h6 class="fw-bold text-primary mb-3">2. Skill Test Category</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select name="skill_test_category" class="form-select">
                                    <option value="Typing" {{ old('skill_test_category', $skill->skill_test_category ?? '') == 'Typing' ? 'selected' : '' }}>Typing</option>
                                    <option value="Computer Skill" {{ old('skill_test_category', $skill->skill_test_category ?? '') == 'Computer Skill' ? 'selected' : '' }}>Computer Skill</option>
                                    <option value="Data Entry" {{ old('skill_test_category', $skill->skill_test_category ?? '') == 'Data Entry' ? 'selected' : '' }}>Data Entry</option>
                                    <option value="Shorthand" {{ old('skill_test_category', $skill->skill_test_category ?? '') == 'Shorthand' ? 'selected' : '' }}>Shorthand</option>
                                    <option value="Practical / Lab" {{ old('skill_test_category', $skill->skill_test_category ?? '') == 'Practical / Lab' ? 'selected' : '' }}>Practical
                                        / Lab</option>
                                    <option value="Trade Skill" {{ old('skill_test_category', $skill->skill_test_category ?? '') == 'Trade Skill' ? 'selected' : '' }}>Trade Skill</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Purpose</label>
                                <input type="text" name="skill_test_purpose" class="form-control"
                                    value="{{ old('skill_test_purpose', $skill->skill_test_purpose ?? '') }}"
                                    placeholder="Eligibility Verification, Job Requirement Check, etc.">
                            </div>
                        </div>

                        <!-- 1️⃣5️⃣ ADMIN & CONTROL -->
                        <h6 class="fw-bold text-primary mb-3">15. Admin & Control</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Stage Status</label>
                                <select name="stage_status" class="form-select">
                                    <option value="Scheduled" {{ old('stage_status', $skill->stage_status ?? '') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="Completed" {{ old('stage_status', $skill->stage_status ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Archived" {{ old('stage_status', $skill->stage_status ?? '') == 'Archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="Public" {{ old('visibility', $skill->visibility ?? '') == 'Public' ? 'selected' : '' }}>Public</option>
                                    <option value="Draft" {{ old('visibility', $skill->visibility ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Private" {{ old('visibility', $skill->visibility ?? '') == 'Private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control"
                                    rows="2">{{ old('remarks', $skill->remarks ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: SKILLS & REQUIREMENTS --}}
                    <div class="tab-pane fade" id="requirements" role="tabpanel">
                        <!-- 3️⃣ SKILLS EVALUATED -->
                        <h6 class="fw-bold text-primary mb-3">3. Skills Evaluated (Multi-Skill)</h6>
                        <div id="skills-evaluated-wrapper">
                            @php
                                $skills = old('skills_evaluated', $skill->skills_evaluated ?? [[]]);
                            @endphp
                            @foreach($skills as $index => $s)
                                <div class="row g-3 mb-3 skill-item">
                                    <div class="col-md-3">
                                        <label class="form-label">Skill Name</label>
                                        <input type="text" name="skills_evaluated[{{$index}}][name]" class="form-control"
                                            value="{{ $s['name'] ?? '' }}" placeholder="e.g. English Typing">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Skill Code</label>
                                        <input type="text" name="skills_evaluated[{{$index}}][code]" class="form-control"
                                            value="{{ $s['code'] ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Description</label>
                                        <input type="text" name="skills_evaluated[{{$index}}][description]" class="form-control"
                                            value="{{ $s['description'] ?? '' }}">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger remove-skill"><i
                                                class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-skill" class="btn btn-sm btn-outline-primary mb-4"><i
                                class="fas fa-plus me-1"></i> Add Skill</button>

                        <!-- 4️⃣ SKILL REQUIREMENTS & BENCHMARKS -->
                        <h6 class="fw-bold text-primary mb-3">4. Skill Requirements & Benchmarks</h6>
                        <div class="row g-3 mb-4 p-3 bg-light rounded">
                            <div class="col-12"><small class="text-secondary fw-bold">A. TYPING / DATA ENTRY</small></div>
                            <div class="col-md-4">
                                <label class="form-label">Typing Languages (Comma separated)</label>
                                <input type="text" name="typing_language_options_raw" class="form-control"
                                    value="{{ old('typing_language_options_raw', isset($skill->typing_language_options) ? implode(',', (array) $skill->typing_language_options) : '') }}"
                                    placeholder="English, Hindi">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Speed (WPM/KDPH)</label>
                                <input type="text" name="minimum_typing_speed" class="form-control"
                                    value="{{ old('minimum_typing_speed', $skill->minimum_typing_speed ?? '') }}"
                                    placeholder="e.g. 35 WPM">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Accuracy Req. (%)</label>
                                <input type="number" step="0.01" name="accuracy_required_percentage" class="form-control"
                                    value="{{ old('accuracy_required_percentage', $skill->accuracy_required_percentage ?? '') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Backspace Allowed?</label>
                                <select name="backspace_allowed" class="form-select">
                                    <option value="1" {{ old('backspace_allowed', $skill->backspace_allowed ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('backspace_allowed', $skill->backspace_allowed ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4 p-3 bg-light rounded">
                            <div class="col-12"><small class="text-secondary fw-bold">B. COMPUTER / PRACTICAL SKILLS</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Software Tools (Comma separated)</label>
                                <input type="text" name="software_tools_tested_raw" class="form-control"
                                    value="{{ old('software_tools_tested_raw', isset($skill->software_tools_tested) ? implode(',', (array) $skill->software_tools_tested) : '') }}"
                                    placeholder="MS Word, Excel, PowerPoint">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Task-based Evaluation?</label>
                                <select name="task_based_evaluation" class="form-select">
                                    <option value="1" {{ old('task_based_evaluation', $skill->task_based_evaluation ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('task_based_evaluation', $skill->task_based_evaluation ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Task Time (Minutes)</label>
                                <input type="number" name="task_completion_time_minutes" class="form-control"
                                    value="{{ old('task_completion_time_minutes', $skill->task_completion_time_minutes ?? '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: MERIT & LOGIC --}}
                    <div class="tab-pane fade" id="merit" role="tabpanel">
                        <!-- 5️⃣ MARKING & QUALIFICATION LOGIC -->
                        <h6 class="fw-bold text-primary mb-3">5. Marking & Qualification Logic</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label class="form-label">Marks Applicable?</label>
                                <select name="marks_applicable" class="form-select">
                                    <option value="1" {{ old('marks_applicable', $skill->marks_applicable ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('marks_applicable', $skill->marks_applicable ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Marks</label>
                                <input type="number" step="0.01" name="maximum_marks" class="form-control"
                                    value="{{ old('maximum_marks', $skill->maximum_marks ?? '') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Qualifying</label>
                                <input type="number" step="0.01" name="minimum_qualifying_score" class="form-control"
                                    value="{{ old('minimum_qualifying_score', $skill->minimum_qualifying_score ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pass/Fail Only?</label>
                                <select name="pass_fail_only" class="form-select">
                                    <option value="1" {{ old('pass_fail_only', $skill->pass_fail_only ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('pass_fail_only', $skill->pass_fail_only ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Normalization Applied?</label>
                                <select name="normalization_applied" class="form-select">
                                    <option value="1" {{ old('normalization_applied', $skill->normalization_applied ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('normalization_applied', $skill->normalization_applied ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- 6️⃣ ELIGIBILITY & SHORTLISTING -->
                        <h6 class="fw-bold text-primary mb-3">6. Eligibility & Shortlisting</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Prev. Stage Qualification Req.?</label>
                                <select name="previous_stage_qualification_required" class="form-select">
                                    <option value="1" {{ old('previous_stage_qualification_required', $skill->previous_stage_qualification_required ?? true) ? 'selected' : '' }}>Yes
                                    </option>
                                    <option value="0" {{ !old('previous_stage_qualification_required', $skill->previous_stage_qualification_required ?? true) ? 'selected' : '' }}>No
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shortlisting Basis</label>
                                <select name="shortlisting_basis" class="form-select">
                                    <option value="Rank-based" {{ old('shortlisting_basis', $skill->shortlisting_basis ?? '') == 'Rank-based' ? 'selected' : '' }}>Rank-based</option>
                                    <option value="Marks-based" {{ old('shortlisting_basis', $skill->shortlisting_basis ?? '') == 'Marks-based' ? 'selected' : '' }}>Marks-based</option>
                                </select>
                            </div>
                        </div>

                        <!-- 10️⃣ RESULT & OUTPUT -->
                        <h6 class="fw-bold text-primary mb-3">10. Result & Output</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Result Type</label>
                                <select name="skill_test_result_type" class="form-select">
                                    <option value="Pass/Fail" {{ old('skill_test_result_type', $skill->skill_test_result_type ?? '') == 'Pass/Fail' ? 'selected' : '' }}>Pass/Fail
                                    </option>
                                    <option value="Score" {{ old('skill_test_result_type', $skill->skill_test_result_type ?? '') == 'Score' ? 'selected' : '' }}>Score</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Result Visibility</label>
                                <select name="result_visibility" class="form-select">
                                    <option value="Candidate Login" {{ old('result_visibility', $skill->result_visibility ?? '') == 'Candidate Login' ? 'selected' : '' }}>Candidate Login</option>
                                    <option value="Public" {{ old('result_visibility', $skill->result_visibility ?? '') == 'Public' ? 'selected' : '' }}>Public</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Declaration Date</label>
                                <input type="date" name="result_declaration_date" class="form-control"
                                    value="{{ old('result_declaration_date', isset($skill->result_declaration_date) ? $skill->result_declaration_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- TAB 4: MODE & LOGISTICS --}}
                    <div class="tab-pane fade" id="logistics" role="tabpanel">
                        <!-- 7️⃣ TEST MODE & ENVIRONMENT -->
                        <h6 class="fw-bold text-primary mb-3">7. Test Mode & Environment</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Test Mode</label>
                                <select name="test_mode" class="form-select">
                                    <option value="Online" {{ old('test_mode', $skill->test_mode ?? '') == 'Online' ? 'selected' : '' }}>Online</option>
                                    <option value="Offline" {{ old('test_mode', $skill->test_mode ?? '') == 'Offline' ? 'selected' : '' }}>Offline</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Environment</label>
                                <select name="test_environment" class="form-select">
                                    <option value="Controlled Lab" {{ old('test_environment', $skill->test_environment ?? '') == 'Controlled Lab' ? 'selected' : '' }}>Controlled Lab</option>
                                    <option value="Open Lab" {{ old('test_environment', $skill->test_environment ?? '') == 'Open Lab' ? 'selected' : '' }}>Open Lab</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Assistive Devices Allowed?</label>
                                <select name="assistive_devices_allowed" class="form-select">
                                    <option value="1" {{ old('assistive_devices_allowed', $skill->assistive_devices_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('assistive_devices_allowed', $skill->assistive_devices_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">PWD Accommodations?</label>
                                <select name="pwd_accommodations_available" class="form-select">
                                    <option value="1" {{ old('pwd_accommodations_available', $skill->pwd_accommodations_available ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('pwd_accommodations_available', $skill->pwd_accommodations_available ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- 8️⃣ EXAM CENTRES & LOGISTICS -->
                        <h6 class="fw-bold text-primary mb-3">8. Exam Centres & Logistics</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Centres Scope</label>
                                <select name="skill_test_centres_scope" class="form-select">
                                    <option value="National" {{ old('skill_test_centres_scope', $skill->skill_test_centres_scope ?? '') == 'National' ? 'selected' : '' }}>National
                                    </option>
                                    <option value="Regional" {{ old('skill_test_centres_scope', $skill->skill_test_centres_scope ?? '') == 'Regional' ? 'selected' : '' }}>Regional
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Lab Infrastructure Required</label>
                                <input type="text" name="lab_infrastructure_required" class="form-control"
                                    value="{{ old('lab_infrastructure_required', $skill->lab_infrastructure_required ?? '') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Reporting Guidelines</label>
                                <input type="text" name="reporting_time_guidelines" class="form-control"
                                    value="{{ old('reporting_time_guidelines', $skill->reporting_time_guidelines ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Identity Verification Required?</label>
                                <select name="identity_verification_required" class="form-select">
                                    <option value="1" {{ old('identity_verification_required', $skill->identity_verification_required ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('identity_verification_required', $skill->identity_verification_required ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- 9️⃣ ATTEMPTS & RETEST RULES -->
                        <h6 class="fw-bold text-primary mb-3">9. Attempts & Retest Rules</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Attempts Allowed</label>
                                <input type="number" name="attempts_allowed" class="form-control"
                                    value="{{ old('attempts_allowed', $skill->attempts_allowed ?? 1) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Retest Allowed?</label>
                                <select name="retest_allowed" class="form-select">
                                    <option value="1" {{ old('retest_allowed', $skill->retest_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('retest_allowed', $skill->retest_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Retest Conditions</label>
                                <input type="text" name="retest_conditions" class="form-control"
                                    value="{{ old('retest_conditions', $skill->retest_conditions ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Temp Failure Recovery Allowed?</label>
                                <select name="temporary_failure_recovery_allowed" class="form-select">
                                    <option value="1" {{ old('temporary_failure_recovery_allowed', $skill->temporary_failure_recovery_allowed ?? false) ? 'selected' : '' }}>Yes
                                    </option>
                                    <option value="0" {{ !old('temporary_failure_recovery_allowed', $skill->temporary_failure_recovery_allowed ?? false) ? 'selected' : '' }}>No
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 5: SUPPORT & LEGAL --}}
                    <div class="tab-pane fade" id="support" role="tabpanel">
                        <!-- 11️⃣ APPEAL / REVIEW -->
                        <h6 class="fw-bold text-primary mb-3">11. Appeal / Review</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Appeal Allowed?</label>
                                <select name="appeal_allowed" class="form-select">
                                    <option value="1" {{ old('appeal_allowed', $skill->appeal_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('appeal_allowed', $skill->appeal_allowed ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Time Limit (Days)</label>
                                <input type="number" name="appeal_time_limit_days" class="form-control"
                                    value="{{ old('appeal_time_limit_days', $skill->appeal_time_limit_days ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Appeal Fee Req.?</label>
                                <select name="appeal_fee_required" class="form-select">
                                    <option value="1" {{ old('appeal_fee_required', $skill->appeal_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('appeal_fee_required', $skill->appeal_fee_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Appeal Fee Amount</label>
                                <input type="number" name="appeal_fee_amount" class="form-control"
                                    value="{{ old('appeal_fee_amount', $skill->appeal_fee_amount ?? '') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Appeal Process</label>
                                <textarea name="appeal_process" class="form-control"
                                    rows="2">{{ old('appeal_process', $skill->appeal_process ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- 12️⃣ DOCUMENTS & INSTRUCTIONS -->
                        <h6 class="fw-bold text-primary mb-3">12. Documents & Instructions</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Documents Req. (Comma separated)</label>
                                <input type="text" name="documents_required_raw" class="form-control"
                                    value="{{ old('documents_required_raw', isset($skill->documents_required) ? implode(',', (array) $skill->documents_required) : '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Guidelines URL</label>
                                <input type="url" name="instruction_guidelines_url" class="form-control"
                                    value="{{ old('instruction_guidelines_url', $skill->instruction_guidelines_url ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mock Test Available?</label>
                                <select name="mock_test_available" class="form-select">
                                    <option value="1" {{ old('mock_test_available', $skill->mock_test_available ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('mock_test_available', $skill->mock_test_available ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Demo Env Available?</label>
                                <select name="demo_environment_available" class="form-select">
                                    <option value="1" {{ old('demo_environment_available', $skill->demo_environment_available ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('demo_environment_available', $skill->demo_environment_available ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- 13️⃣ FEES -->
                        <h6 class="fw-bold text-primary mb-3">13. Skill Test Fees</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Fee Required?</label>
                                <select name="skill_test_fee_required" class="form-select">
                                    <option value="1" {{ old('skill_test_fee_required', $skill->skill_test_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('skill_test_fee_required', $skill->skill_test_fee_required ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fee Amount</label>
                                <input type="number" name="skill_test_fee_amount" class="form-control"
                                    value="{{ old('skill_test_fee_amount', $skill->skill_test_fee_amount ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Refundable?</label>
                                <select name="fee_refundable" class="form-select">
                                    <option value="1" {{ old('fee_refundable', $skill->fee_refundable ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('fee_refundable', $skill->fee_refundable ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Payment Modes (Comma separated)</label>
                                <input type="text" name="payment_modes_raw" class="form-control"
                                    value="{{ old('payment_modes_raw', isset($skill->payment_modes) ? implode(',', (array) $skill->payment_modes) : '') }}">
                            </div>
                        </div>

                        <!-- 14️⃣ DISCLAIMERS & REFERENCES -->
                        <h6 class="fw-bold text-primary mb-3">14. Disclaimers & References</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Disclaimer Text</label>
                                <textarea name="skill_test_disclaimer_text" class="form-control"
                                    rows="2">{{ old('skill_test_disclaimer_text', $skill->skill_test_disclaimer_text ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Information Source</label>
                                <input type="text" name="information_source" class="form-control"
                                    value="{{ old('information_source', $skill->information_source ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Verified On</label>
                                <input type="date" name="last_verified_on" class="form-control"
                                    value="{{ old('last_verified_on', isset($skill->last_verified_on) ? $skill->last_verified_on->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-end py-3 border-top">
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> Save Skill Test Details
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

        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({ theme: 'bootstrap-5' });

            $('#add-skill').click(function () {
                let index = $('.skill-item').length;
                let html = `
                        <div class="row g-3 mb-3 skill-item">
                            <div class="col-md-3">
                                <label class="form-label">Skill Name</label>
                                <input type="text" name="skills_evaluated[${index}][name]" class="form-control" placeholder="e.g. English Typing">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Skill Code</label>
                                <input type="text" name="skills_evaluated[${index}][code]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <input type="text" name="skills_evaluated[${index}][description]" class="form-control">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger remove-skill"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>`;
                $('#skills-evaluated-wrapper').append(html);
            });

            $(document).on('click', '.remove-skill', function () {
                if ($('.skill-item').length > 1) {
                    $(this).closest('.skill-item').remove();
                } else {
                    $(this).closest('.skill-item').find('input').val('');
                }
            });
        });
    </script>
@endpush