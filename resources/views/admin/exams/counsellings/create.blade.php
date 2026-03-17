@extends('admin.layouts.master')

@section('title', 'Add Counselling - ' . $exam->name)

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .ck-editor__editable {
            min-height: 200px;
        }

        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #dee2e6;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        .nav-tabs-custom .nav-link {
            color: #495057;
        }

        .nav-tabs-custom .nav-link.active {
            color: #556ee6;
            background-color: transparent;
            border-color: transparent transparent #556ee6 transparent;
            border-bottom-width: 3px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Add New Counselling for {{ $exam->name }}</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.exams.counsellings.index', $exam->id) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.exams.counsellings.store', $exam->id) }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#identity"
                                    role="tab">Identity</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#scope" role="tab">Scope</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#eligibility"
                                    role="tab">Eligibility</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rounds"
                                    role="tab">Rounds</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#process"
                                    role="tab">Process</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dates" role="tab">Dates</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#allocation"
                                    role="tab">Allocation</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#documents"
                                    role="tab">Documents</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#fees"
                                    role="tab">Application & Fees</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#support"
                                    role="tab">Support</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#seo" role="tab">SEO &
                                    Meta</a></li>
                        </ul>

                        <div class="tab-content p-3 text-muted">

                            <!-- 1. Identity -->
                            <div class="tab-pane active" id="identity" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Counselling Name *</label>
                                        <input type="text" name="counselling_name" class="form-control"
                                            value="{{ old('counselling_name') }}" required
                                            placeholder="e.g. NEET UG Counselling {{ date('Y') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Slug</label>
                                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}"
                                            placeholder="Auto-generated if empty">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Counselling Type *</label>
                                        <select name="counselling_type" class="form-select" required>
                                            <option value="Centralised" {{ old('counselling_type') == 'Centralised' ? 'selected' : '' }}>Centralised</option>
                                            <option value="State-Level" {{ old('counselling_type') == 'State-Level' ? 'selected' : '' }}>State-Level</option>
                                            <option value="Institute-Level" {{ old('counselling_type') == 'Institute-Level' ? 'selected' : '' }}>Institute-Level</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Counselling Mode</label>
                                        <select name="counselling_mode" class="form-select">
                                            <option value="Online" {{ old('counselling_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                                            <option value="Offline" {{ old('counselling_mode') == 'Offline' ? 'selected' : '' }}>Offline</option>
                                            <option value="Hybrid" {{ old('counselling_mode') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Conducting Authority Name *</label>
                                        <input type="text" name="conducting_authority_name" class="form-control"
                                            value="{{ old('conducting_authority_name') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Conducting Authority Type</label>
                                        <select name="conducting_authority_type" class="form-select">
                                            <option value="Central Government" {{ old('conducting_authority_type') == 'Central Government' ? 'selected' : '' }}>Central Government</option>
                                            <option value="State Government" {{ old('conducting_authority_type') == 'State Government' ? 'selected' : '' }}>State Government</option>
                                            <option value="University Body" {{ old('conducting_authority_type') == 'University Body' ? 'selected' : '' }}>University Body</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Official Website</label>
                                        <input type="url" name="official_counselling_website" class="form-control"
                                            value="{{ old('official_counselling_website') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Scope -->
                            <div class="tab-pane" id="scope" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Applicable Course Levels</label>
                                        <select name="applicable_course_levels[]" class="form-select select2" multiple>
                                            <option value="UG" {{ in_array('UG', old('applicable_course_levels', [])) ? 'selected' : '' }}>UG</option>
                                            <option value="PG" {{ in_array('PG', old('applicable_course_levels', [])) ? 'selected' : '' }}>PG</option>
                                            <option value="Diploma" {{ in_array('Diploma', old('applicable_course_levels', [])) ? 'selected' : '' }}>Diploma</option>
                                            <option value="Ph.D" {{ in_array('Ph.D', old('applicable_course_levels', [])) ? 'selected' : '' }}>Ph.D</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Applicable Quotas</label>
                                        <select name="applicable_quotas[]" class="form-select select2" multiple>
                                            <option value="All India Quota" {{ in_array('All India Quota', old('applicable_quotas', [])) ? 'selected' : '' }}>All India Quota</option>
                                            <option value="State Quota" {{ in_array('State Quota', old('applicable_quotas', [])) ? 'selected' : '' }}>State Quota</option>
                                            <option value="Institutional Quota" {{ in_array('Institutional Quota', old('applicable_quotas', [])) ? 'selected' : '' }}>Institutional Quota
                                            </option>
                                            <option value="Management Quota" {{ in_array('Management Quota', old('applicable_quotas', [])) ? 'selected' : '' }}>Management Quota</option>
                                            <option value="NRI Quota" {{ in_array('NRI Quota', old('applicable_quotas', [])) ? 'selected' : '' }}>NRI Quota</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Applicable Categories</label>
                                        <select name="applicable_categories[]" class="form-select select2" multiple>
                                            <option value="General" {{ in_array('General', old('applicable_categories', [])) ? 'selected' : '' }}>General</option>
                                            <option value="OBC" {{ in_array('OBC', old('applicable_categories', [])) ? 'selected' : '' }}>OBC</option>
                                            <option value="SC" {{ in_array('SC', old('applicable_categories', [])) ? 'selected' : '' }}>SC</option>
                                            <option value="ST" {{ in_array('ST', old('applicable_categories', [])) ? 'selected' : '' }}>ST</option>
                                            <option value="EWS" {{ in_array('EWS', old('applicable_categories', [])) ? 'selected' : '' }}>EWS</option>
                                            <option value="PwD" {{ in_array('PwD', old('applicable_categories', [])) ? 'selected' : '' }}>PwD</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="domicile_required"
                                                id="domicile_required" value="1" {{ old('domicile_required') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="domicile_required">Domicile
                                                Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">State Applicability (if State-Level)</label>
                                        <input type="text" name="state_applicability" class="form-control"
                                            placeholder="e.g. Maharashtra, Karnataka"
                                            value="{{ old('state_applicability') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Eligibility -->
                            <div class="tab-pane" id="eligibility" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox"
                                                name="minimum_exam_qualification_required"
                                                id="minimum_exam_qualification_required" value="1" {{ old('minimum_exam_qualification_required', '1') ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="minimum_exam_qualification_required">Minimum Exam Qualification
                                                Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Min Score/Rank Required</label>
                                        <input type="text" name="minimum_score_or_rank_required" class="form-control"
                                            placeholder="e.g. 50th Percentile"
                                            value="{{ old('minimum_score_or_rank_required') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Attempts Allowed</label>
                                        <input type="text" name="attempts_allowed" class="form-control"
                                            value="{{ old('attempts_allowed') }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Age Criteria</label>
                                        <textarea name="age_criteria_for_counselling" class="form-control"
                                            rows="2">{{ old('age_criteria_for_counselling') }}</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Eligibility Notes</label>
                                        <textarea name="eligibility_notes"
                                            class="editor">{{ old('eligibility_notes') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Rounds -->
                            <div class="tab-pane" id="rounds" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Number of Rounds</label>
                                        <input type="number" name="number_of_rounds" class="form-control"
                                            value="{{ old('number_of_rounds', 1) }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Rounds Details (JSON Structure)</label>
                                        <div id="rounds-container">
                                            <div class="rounds-repeater-item border p-3 mb-2 rounded">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <input type="text" name="rounds[0][round_name]" class="form-control"
                                                            placeholder="Round Name (e.g. Round 1)"
                                                            value="{{ old('rounds.0.round_name') }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <select name="rounds[0][round_type]" class="form-select">
                                                            <option value="Regular" {{ old('rounds.0.round_type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                                                            <option value="Special" {{ old('rounds.0.round_type') == 'Special' ? 'selected' : '' }}>Special</option>
                                                            <option value="Mop-Up" {{ old('rounds.0.round_type') == 'Mop-Up' ? 'selected' : '' }}>Mop-Up</option>
                                                            <option value="Stray" {{ old('rounds.0.round_type') == 'Stray' ? 'selected' : '' }}>Stray Vacancy</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-check mt-2">
                                                            <input type="checkbox" name="rounds[0][upgrade_allowed]"
                                                                value="1" class="form-check-input" {{ old('rounds.0.upgrade_allowed') ? 'checked' : '' }}>
                                                            <label>Upgrade</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-check mt-2">
                                                            <input type="checkbox" name="rounds[0][fresh_reg]" value="1"
                                                                class="form-check-input" {{ old('rounds.0.fresh_reg') ? 'checked' : '' }}> <label>Fresh Reg</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-round w-100">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-round">+ Add
                                            Round</button>
                                        <small class="text-muted d-block mt-1">Configure admission rounds for this
                                            counselling.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Process -->
                            <div class="tab-pane" id="process" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Registration Process Steps (JSON/List)</label>
                                        <textarea name="registration_process_steps[]" class="form-control mb-2"
                                            placeholder="Step 1...">{{ old('registration_process_steps.0') }}</textarea>
                                        <small class="text-muted">Enter core steps line by line or manageable
                                            format.</small>
                                    </div>
                                    <div class="col-12"><label class="form-label">Choice Filling Process</label><textarea
                                            name="choice_filling_process"
                                            class="editor">{{ old('choice_filling_process') }}</textarea></div>
                                    <div class="col-12"><label class="form-label">Seat Allotment Process</label><textarea
                                            name="seat_allotment_process"
                                            class="editor">{{ old('seat_allotment_process') }}</textarea></div>
                                    <div class="col-12"><label class="form-label">Reporting Process</label><textarea
                                            name="reporting_process"
                                            class="editor">{{ old('reporting_process') }}</textarea></div>
                                    <div class="col-12"><label class="form-label">Document Verification</label><textarea
                                            name="document_verification_process"
                                            class="editor">{{ old('document_verification_process') }}</textarea></div>
                                    <div class="col-12"><label class="form-label">Upgradation Rules</label><textarea
                                            name="upgradation_rules"
                                            class="editor">{{ old('upgradation_rules') }}</textarea></div>
                                    <div class="col-12"><label class="form-label">Exit & Refund Rules</label><textarea
                                            name="exit_and_refund_rules"
                                            class="editor">{{ old('exit_and_refund_rules') }}</textarea></div>
                                </div>
                            </div>

                            <!-- 6. Dates -->
                            <div class="tab-pane" id="dates" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Counselling Year (e.g.
                                            2026)</label><input type="month" name="counselling_year" class="form-control"
                                            value="{{ old('counselling_year', date('Y-m')) }}"></div>
                                    <div class="col-md-4"><label class="form-label">Reg Start Date</label><input type="date"
                                            name="registration_start_date" class="form-control"
                                            value="{{ old('registration_start_date') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Reg End Date</label><input type="date"
                                            name="registration_end_date" class="form-control"
                                            value="{{ old('registration_end_date') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Choice Filling Start</label><input
                                            type="date" name="choice_filling_start_date" class="form-control"
                                            value="{{ old('choice_filling_start_date') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Choice Filling End</label><input
                                            type="date" name="choice_filling_end_date" class="form-control"
                                            value="{{ old('choice_filling_end_date') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Seat Allotment Result</label><input
                                            type="date" name="seat_allotment_result_date" class="form-control"
                                            value="{{ old('seat_allotment_result_date') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Reporting Start</label><input
                                            type="date" name="reporting_start_date" class="form-control"
                                            value="{{ old('reporting_start_date') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Reporting End</label><input type="date"
                                            name="reporting_end_date" class="form-control"
                                            value="{{ old('reporting_end_date') }}"></div>

                                    <div class="col-md-12">
                                        <label class="form-label">Round-wise Schedule (JSON/Description)</label>
                                        <textarea name="round_wise_schedule" class="form-control" rows="3"
                                            placeholder="Enter detailed schedule here as text or JSON structure...">{{ old('round_wise_schedule') }}</textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Additional Date Notes</label>
                                        <textarea name="important_dates[note]"
                                            class="editor">{{ old('important_dates.note') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Allocation -->
                            <div class="tab-pane" id="allocation" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Seat Allocation Basis</label><select
                                            name="seat_allocation_basis" class="form-select">
                                            <option value="Exam Rank" {{ old('seat_allocation_basis') == 'Exam Rank' ? 'selected' : '' }}>Exam Rank</option>
                                            <option value="Score" {{ old('seat_allocation_basis') == 'Score' ? 'selected' : '' }}>Score</option>
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label">Tie Breaking Rules</label><textarea
                                            name="tie_breaking_rules" class="form-control"
                                            rows="3">{{ old('tie_breaking_rules') }}</textarea></div>
                                    <div class="col-md-12"><label class="form-label">Seat Conversion Rules</label><textarea
                                            name="seat_conversion_rules"
                                            class="editor">{{ old('seat_conversion_rules') }}</textarea></div>
                                </div>
                            </div>

                            <!-- 8. Documents -->
                            <div class="tab-pane" id="documents" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label">Documents Required
                                            (Multi-select)</label>
                                        <select name="documents_required[]" class="form-select select2" multiple>
                                            <option value="Admit Card" {{ in_array('Admit Card', old('documents_required', [])) ? 'selected' : '' }}>Admit Card</option>
                                            <option value="Score Card" {{ in_array('Score Card', old('documents_required', [])) ? 'selected' : '' }}>Score Card</option>
                                            <option value="10th Marksheet" {{ in_array('10th Marksheet', old('documents_required', [])) ? 'selected' : '' }}>10th Marksheet</option>
                                            <option value="12th Marksheet" {{ in_array('12th Marksheet', old('documents_required', [])) ? 'selected' : '' }}>12th Marksheet</option>
                                            <option value="Category Certificate" {{ in_array('Category Certificate', old('documents_required', [])) ? 'selected' : '' }}>Category Certificate
                                            </option>
                                            <option value="Domicile Certificate" {{ in_array('Domicile Certificate', old('documents_required', [])) ? 'selected' : '' }}>Domicile Certificate
                                            </option>
                                            <option value="Photo ID" {{ in_array('Photo ID', old('documents_required', [])) ? 'selected' : '' }}>Photo ID</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Document Format
                                            Requirements</label><textarea name="document_format_requirements"
                                            class="editor">{{ old('document_format_requirements') }}</textarea></div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="original_documents_required_at_reporting" value="1" {{ old('original_documents_required_at_reporting', '1') ? 'checked' : '' }}>
                                            <label>Originals Required at Reporting</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Application & Fees -->
                            <div class="tab-pane" id="fees" role="tabpanel">
                                <h5 class="fw-bold mb-3 text-primary">Registration Fee Configuration</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="registration_fee_required"
                                                id="registration_fee_required" value="1" {{ old('registration_fee_required', '1') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="registration_fee_required">Registration Fee
                                                Required</label>
                                        </div>
                                    </div>
                                </div>

                                <div id="registration_fee_section"
                                    style="{{ old('registration_fee_required') ? '' : 'display:none;' }}">
                                    <div id="registration-fee-container">
                                        @php $regFeeCount = count(old('registration_fee_structure', [])) ?: 1; @endphp
                                        @for($i = 0; $i < $regFeeCount; $i++)
                                            <div class="card border mb-2 registration-fee-item">
                                                <div class="card-body p-3">
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="small">Categories</label>
                                                            <select name="registration_fee_structure[{{$i}}][categories][]"
                                                                class="form-select form-select-sm select2-category" multiple
                                                                data-placeholder="Select Categories">
                                                                @foreach(['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD'] as $cat)
                                                                    <option value="{{$cat}}" {{ is_array(old("registration_fee_structure.$i.categories")) && in_array($cat, old("registration_fee_structure.$i.categories")) ? 'selected' : '' }}>{{$cat}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="small">Amount</label>
                                                            <input type="number"
                                                                name="registration_fee_structure[{{$i}}][amount]"
                                                                class="form-control form-control-sm"
                                                                value="{{ old("registration_fee_structure.$i.amount") }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Currency</label>
                                                            <input type="text"
                                                                name="registration_fee_structure[{{$i}}][currency]"
                                                                class="form-control form-control-sm"
                                                                value="{{ old("registration_fee_structure.$i.currency", 'INR') }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Refundable</label>
                                                            <select name="registration_fee_structure[{{$i}}][refundable]"
                                                                class="form-select form-select-sm">
                                                                <option value="No" {{ old("registration_fee_structure.$i.refundable") == 'No' ? 'selected' : '' }}>No</option>
                                                                <option value="Yes" {{ old("registration_fee_structure.$i.refundable") == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-reg-fee w-100 {{ $i == 0 ? 'disabled' : '' }}">Remove</button>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            <input type="text"
                                                                name="registration_fee_structure[{{$i}}][remarks]"
                                                                class="form-control form-control-sm" placeholder="Remarks"
                                                                value="{{ old("registration_fee_structure.$i.remarks") }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-reg-fee">+ Add
                                        Fee Row</button>
                                </div>

                                <hr>
                                <h5 class="fw-bold mb-3 text-danger">Late Application / Penalty Fees</h5>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="late_registration_allowed"
                                        id="late_registration_allowed" value="1" {{ old('late_registration_allowed') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="late_registration_allowed">Late Registration
                                        Allowed with Penalty</label>
                                </div>

                                <div id="late_fee_section"
                                    style="{{ old('late_registration_allowed') ? '' : 'display:none;' }}">
                                    <div id="late-fee-container">
                                        @php $lateFeeCount = count(old('late_fee_rules', [])) ?: 1; @endphp
                                        @for($i = 0; $i < $lateFeeCount; $i++)
                                            <div class="card border mb-2 late-fee-item bg-light">
                                                <div class="card-body p-3">
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="small">Condition</label>
                                                            <select name="late_fee_rules[{{$i}}][condition]"
                                                                class="form-select form-select-sm">
                                                                @foreach(['Late registration', 'Missed reporting', 'Choice not locked'] as $cond)
                                                                    <option value="{{$cond}}" {{ old("late_fee_rules.$i.condition") == $cond ? 'selected' : '' }}>{{$cond}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Type</label>
                                                            <select name="late_fee_rules[{{$i}}][penalty_type]"
                                                                class="form-select form-select-sm">
                                                                <option value="Flat" {{ old("late_fee_rules.$i.penalty_type") == 'Flat' ? 'selected' : '' }}>Flat</option>
                                                                <option value="Percentage" {{ old("late_fee_rules.$i.penalty_type") == 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Amount</label>
                                                            <input type="number" name="late_fee_rules[{{$i}}][penalty_amount]"
                                                                class="form-control form-control-sm"
                                                                value="{{ old("late_fee_rules.$i.penalty_amount") }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Cap</label>
                                                            <input type="number"
                                                                name="late_fee_rules[{{$i}}][maximum_penalty_cap]"
                                                                class="form-control form-control-sm" placeholder="Max Cap"
                                                                value="{{ old("late_fee_rules.$i.maximum_penalty_cap") }}">
                                                        </div>
                                                        <div class="col-md-3 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-late-fee w-100 {{ $i == 0 ? 'disabled' : '' }}">Remove</button>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            <input type="text" name="late_fee_rules[{{$i}}][remarks]"
                                                                class="form-control form-control-sm" placeholder="Remarks"
                                                                value="{{ old("late_fee_rules.$i.remarks") }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-4" id="add-late-fee">+ Add
                                        Late Fee Rule</button>
                                </div>

                                <hr>
                                <h5 class="fw-bold mb-3 text-warning">Security Deposit Rules</h5>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="security_deposit_required"
                                        id="security_deposit_required" value="1" {{ old('security_deposit_required') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="security_deposit_required">Security Deposit
                                        Required</label>
                                </div>

                                <div id="security_deposit_section"
                                    style="{{ old('security_deposit_required') ? '' : 'display:none;' }}">
                                    <div id="security-deposit-container">
                                        @php $sdCount = count(old('security_deposit_structure', [])) ?: 1; @endphp
                                        @for($i = 0; $i < $sdCount; $i++)
                                            <div class="card border mb-2 security-deposit-item">
                                                <div class="card-body p-3">
                                                    <div class="row g-2">
                                                        <div class="col-md-2">
                                                            <label class="small">Categories</label>
                                                            <select
                                                                name="security_deposit_structure[{{$i}}][candidate_categories][]"
                                                                class="form-select form-select-sm select2-category" multiple
                                                                data-placeholder="Select Categories">
                                                                @foreach(['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD'] as $cat)
                                                                    <option value="{{$cat}}" {{ is_array(old("security_deposit_structure.$i.candidate_categories")) && in_array($cat, old("security_deposit_structure.$i.candidate_categories")) ? 'selected' : '' }}>{{$cat}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">College Type</label>
                                                            <select name="security_deposit_structure[{{$i}}][college_type]"
                                                                class="form-select form-select-sm">
                                                                @foreach(['Government', 'Private', 'Deemed'] as $ct)
                                                                    <option value="{{$ct}}" {{ old("security_deposit_structure.$i.college_type") == $ct ? 'selected' : '' }}>{{$ct}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Quota</label>
                                                            <select name="security_deposit_structure[{{$i}}][quota_type]"
                                                                class="form-select form-select-sm">
                                                                @foreach(['All India', 'State'] as $qt)
                                                                    <option value="{{$qt}}" {{ old("security_deposit_structure.$i.quota_type") == $qt ? 'selected' : '' }}>{{$qt}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Amount</label>
                                                            <input type="number"
                                                                name="security_deposit_structure[{{$i}}][amount]"
                                                                class="form-control form-control-sm"
                                                                value="{{ old("security_deposit_structure.$i.amount") }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Refundable</label>
                                                            <select name="security_deposit_structure[{{$i}}][refundable]"
                                                                class="form-select form-select-sm">
                                                                <option value="Yes" {{ old("security_deposit_structure.$i.refundable") == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                                <option value="No" {{ old("security_deposit_structure.$i.refundable") == 'No' ? 'selected' : '' }}>No</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-sd w-100 {{ $i == 0 ? 'disabled' : '' }}">Remove</button>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <input type="text"
                                                                name="security_deposit_structure[{{$i}}][refund_conditions]"
                                                                class="form-control form-control-sm"
                                                                placeholder="Refund Conditions"
                                                                value="{{ old("security_deposit_structure.$i.refund_conditions") }}">
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <input type="text"
                                                                name="security_deposit_structure[{{$i}}][forfeiture_conditions]"
                                                                class="form-control form-control-sm"
                                                                placeholder="Forfeiture Conditions"
                                                                value="{{ old("security_deposit_structure.$i.forfeiture_conditions") }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning mb-4" id="add-sd">+ Add
                                        Security Deposit Rule</button>
                                </div>

                                <hr>
                                <h5 class="fw-bold mb-3 text-info">Round-Wise Fee Rules</h5>
                                <div id="round-fee-container">
                                    @php $rfCount = count(old('round_specific_fee_rules', [])) ?: 1; @endphp
                                    @for($i = 0; $i < $rfCount; $i++)
                                        <div class="card border mb-2 round-fee-item bg-light">
                                            <div class="card-body p-3">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="small">Round Name</label>
                                                        <input type="text" name="round_specific_fee_rules[{{$i}}][round_name]"
                                                            class="form-control form-control-sm" placeholder="e.g. Mop-up Round"
                                                            value="{{ old("round_specific_fee_rules.$i.round_name") }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Fresh Reg Fee</label>
                                                        <input type="number"
                                                            name="round_specific_fee_rules[{{$i}}][fresh_registration_fee]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("round_specific_fee_rules.$i.fresh_registration_fee") }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Addl. Security Deposit</label>
                                                        <input type="number"
                                                            name="round_specific_fee_rules[{{$i}}][additional_security_deposit]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("round_specific_fee_rules.$i.additional_security_deposit") }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Refundable</label>
                                                        <select name="round_specific_fee_rules[{{$i}}][refund_applicable]"
                                                            class="form-select form-select-sm">
                                                            <option value="Yes" {{ old("round_specific_fee_rules.$i.refund_applicable") == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                            <option value="No" {{ old("round_specific_fee_rules.$i.refund_applicable") == 'No' ? 'selected' : '' }}>No</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-round-fee w-100 {{ $i == 0 ? 'disabled' : '' }}">Remove</button>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <input type="text" name="round_specific_fee_rules[{{$i}}][remarks]"
                                                            class="form-control form-control-sm" placeholder="Remarks"
                                                            value="{{ old("round_specific_fee_rules.$i.remarks") }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info mb-4" id="add-round-fee">+ Add
                                    Round Rule</button>

                                <hr>
                                <h5 class="fw-bold mb-3 text-success">Refund & Forfeiture Policy</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Policy Summary</label>
                                        <textarea name="refund_policy_summary" class="form-control"
                                            rows="3">{{ old('refund_policy_summary') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Refund Timeline</label>
                                        <input type="text" name="refund_timeline" class="form-control"
                                            placeholder="e.g. 15-30 working days" value="{{ old('refund_timeline') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Refund Mode</label>
                                        <select name="refund_mode" class="form-select">
                                            <option value="Original Payment Method" {{ old('refund_mode') == 'Original Payment Method' ? 'selected' : '' }}>Original Payment Method</option>
                                            <option value="Bank Transfer" {{ old('refund_mode') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="partial_refund_allowed"
                                                id="partial_refund_allowed_coun_create" value="1" {{ old('partial_refund_allowed') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="partial_refund_allowed_coun_create">Partial
                                                Refund Allowed</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Forfeiture Scenarios</label>
                                        <div id="forfeiture-container">
                                            @php $forfeitCount = count(old('forfeiture_scenarios', [])) ?: 1; @endphp
                                            @for($i = 0; $i < $forfeitCount; $i++)
                                                <div class="input-group mb-2 forfeiture-item">
                                                    <select name="forfeiture_scenarios[{{ $i }}][scenario]" class="form-select">
                                                        @foreach(['Seat allotted but not joined', 'Upgradation declined', 'False information'] as $scen)
                                                            <option value="{{ $scen }}" {{ old("forfeiture_scenarios.$i.scenario") == $scen ? 'selected' : '' }}>
                                                                {{ $scen }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text" name="forfeiture_scenarios[{{ $i }}][remarks]"
                                                        class="form-control w-50" placeholder="Optional remarks"
                                                        value="{{ old("forfeiture_scenarios.$i.remarks") }}">
                                                    <button
                                                        class="btn btn-outline-danger remove-forfeit {{ $i == 0 ? 'disabled' : '' }}"
                                                        type="button">Remove</button>
                                                </div>
                                            @endfor
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-dark" id="add-forfeit">+ Add
                                            Scenario</button>
                                    </div>
                                </div>

                                <hr>
                                <h5 class="fw-bold mb-3 text-primary">Payment & Transaction Rules</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Allowed Payment Modes</label>
                                        <select name="payment_modes_allowed[]" class="form-select select2" multiple>
                                            @foreach(['Debit Card', 'Credit Card', 'Net Banking', 'UPI', 'Wallet'] as $pm)
                                                <option value="{{ $pm }}" {{ in_array($pm, old('payment_modes_allowed', [])) ? 'selected' : '' }}>{{ $pm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox"
                                                name="transaction_charges_applicable" id="tx_charges" value="1" {{ old('transaction_charges_applicable') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tx_charges">Transaction Charges
                                                Applicable</label>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row" id="transaction_charges_section" style="{{ old('transaction_charges_applicable') ? '' : 'display:none;' }}">
                                            <div class="col-md-6">
                                                <label class="form-label">Charges Borne By</label>
                                                <select name="transaction_charge_borne_by" class="form-select">
                                                    <option value="Candidate" {{ old('transaction_charge_borne_by') == 'Candidate' ? 'selected' : '' }}>Candidate</option>
                                                    <option value="Authority" {{ old('transaction_charge_borne_by') == 'Authority' ? 'selected' : '' }}>Authority</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Payment Gateway Name</label>
                                                <input type="text" name="payment_gateway_name" class="form-control"
                                                    placeholder="e.g. Razorpay, BillDesk" value="{{ old('payment_gateway_name') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Support -->
                            <div class="tab-pane" id="support" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Helpdesk Number</label><input
                                            type="text" name="helpdesk_contact_number" class="form-control"
                                            value="{{ old('helpdesk_contact_number') }}"></div>
                                    <div class="col-md-6"><label class="form-label">Helpdesk Email</label><input
                                            type="email" name="helpdesk_email" class="form-control"
                                            value="{{ old('helpdesk_email') }}"></div>
                                    <div class="col-md-12"><label class="form-label">Grievance Process</label><textarea
                                            name="grievance_redressal_process"
                                            class="editor">{{ old('grievance_redressal_process') }}</textarea></div>
                                </div>
                            </div>

                            <!-- 11. SEO -->
                            <div class="tab-pane" id="seo" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text"
                                            name="meta_title" class="form-control" value="{{ old('meta_title') }}"></div>
                                    <div class="col-md-6"><label class="form-label">Meta Description</label><textarea
                                            name="meta_description" class="form-control"
                                            rows="2">{{ old('meta_description') }}</textarea></div>
                                    <div class="col-md-6"><label class="form-label">Canonical URL</label><input type="url"
                                            name="canonical_url" class="form-control" value="{{ old('canonical_url') }}">
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Status</label><select name="status"
                                            class="form-select">
                                            <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active
                                            </option>
                                            <option value="Upcoming" {{ old('status') == 'Upcoming' ? 'selected' : '' }}>
                                                Upcoming</option>
                                            <option value="Closed" {{ old('status') == 'Closed' ? 'selected' : '' }}>Closed
                                            </option>
                                        </select></div>
                                </div>
                            </div>

                        </div> <!-- Tab Content -->
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Create Counselling</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });

            document.querySelectorAll('.editor').forEach((element) => {
                ClassicEditor.create(element).catch(error => console.error(error));
            });

            $(document).on('click', '.remove-session-btn', function () {
                $(this).closest('.session-row').remove();
            });

            // Toggles
            $('#registration_fee_required').on('change', function () {
                $('#registration_fee_section').toggle(this.checked);
            }).trigger('change');
            $('#late_registration_allowed').on('change', function () {
                $('#late_fee_section').toggle(this.checked);
            });
            $('#security_deposit_required').on('change', function () {
                $('#security_deposit_section').toggle(this.checked);
            });
            $('#tx_charges').on('change', function () {
                $('#transaction_charges_section').toggle(this.checked);
            });

            // Generic Repeater Logic
            function setupRepeater(config) {
                let index = config.initialIndex;
                $(config.addButton).on('click', function () {
                    let html = config.template(index);
                    let $newRow = $(html);
                    $(config.container).append($newRow);
                    index++;
                    updateRemoveButtons(config);
                    if (config.afterAdd) config.afterAdd($newRow);
                });

                $(config.container).on('click', config.removeButton, function () {
                    $(this).closest(config.itemClass).remove();
                    updateRemoveButtons(config);
                    if (config.afterRemove) config.afterRemove();
                });

                function updateRemoveButtons(config) {
                    let items = $(config.container).find(config.itemClass);
                    items.each(function (i) {
                        $(this).find(config.removeButton).toggleClass('disabled', items.length === 1 && config.requireOne);
                    });
                }
            }

            $('.select2').select2({ width: '100%' });
            $('.select2-category').select2({
                width: '100%',
                closeOnSelect: false
            });

            function refreshCategoryOptions(sectionSelector) {
                const allSelects = $(sectionSelector + ' .select2-category');
                const allSelectedValues = [];

                // Gather all selected values
                allSelects.each(function () {
                    const vals = $(this).val() || [];
                    vals.forEach(v => {
                        if (v) allSelectedValues.push(v);
                    });
                });

                // Update visibility/availability
                allSelects.each(function () {
                    const currentSelect = $(this);
                    const currentVals = currentSelect.val() || [];

                    currentSelect.find('option').each(function () {
                        const opt = $(this);
                        const val = opt.val();
                        if (allSelectedValues.includes(val) && !currentVals.includes(val)) {
                            opt.prop('disabled', true);
                        } else {
                            opt.prop('disabled', false);
                        }
                    });

                    if (currentSelect.data('select2')) {
                        currentSelect.select2('destroy').select2({
                            width: '100%',
                            closeOnSelect: false
                        });
                    }
                });
            }

            $(document).on('change', '#registration-fee-container .select2-category', function () {
                refreshCategoryOptions('#registration-fee-container');
            });

            $(document).on('change', '#security-deposit-container .select2-category', function () {
                refreshCategoryOptions('#security-deposit-container');
            });

            // Registration Fee Repeater
            setupRepeater({
                addButton: '#add-reg-fee',
                removeButton: '.remove-reg-fee',
                container: '#registration-fee-container',
                itemClass: '.registration-fee-item',
                requireOne: true,
                initialIndex: {{ count(old('registration_fee_structure', [[]])) }},
                template: (i) => `
                                        <div class="card border mb-2 registration-fee-item">
                                            <div class="card-body p-3">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="small">Categories</label>
                                                        <select name="registration_fee_structure[${i}][categories][]" class="form-select form-select-sm select2-category" multiple data-placeholder="Select Categories">
                                                            ${['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Amount</label>
                                                        <input type="number" name="registration_fee_structure[${i}][amount]" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Currency</label>
                                                        <input type="text" name="registration_fee_structure[${i}][currency]" class="form-control form-control-sm" value="INR">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Refundable</label>
                                                        <select name="registration_fee_structure[${i}][refundable]" class="form-select form-select-sm">
                                                            <option value="No">No</option>
                                                            <option value="Yes">Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="button" class="btn btn-sm btn-danger remove-reg-fee w-100">Remove</button>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <input type="text" name="registration_fee_structure[${i}][remarks]" class="form-control form-control-sm" placeholder="Remarks">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`,
                afterAdd: ($row) => {
                    $row.find('.select2-category').select2({ width: '100%', closeOnSelect: false });
                    refreshCategoryOptions('#registration-fee-container');
                },
                afterRemove: () => refreshCategoryOptions('#registration-fee-container')
            });

            setupRepeater({
                addButton: '#add-late-fee',
                removeButton: '.remove-late-fee',
                container: '#late-fee-container',
                itemClass: '.late-fee-item',
                requireOne: true,
                initialIndex: {{ count(old('late_fee_rules', [[]])) }},
                template: (i) => `
                                        <div class="card border mb-2 late-fee-item bg-light">
                                            <div class="card-body p-3">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="small">Condition</label>
                                                        <select name="late_fee_rules[${i}][condition]" class="form-select form-select-sm">
                                                            ${['Late registration', 'Missed reporting', 'Choice not locked'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Type</label>
                                                        <select name="late_fee_rules[${i}][penalty_type]" class="form-select form-select-sm">
                                                            <option value="Flat">Flat</option>
                                                            <option value="Percentage">Percentage</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Amount</label>
                                                        <input type="number" name="late_fee_rules[${i}][penalty_amount]" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Cap</label>
                                                        <input type="number" name="late_fee_rules[${i}][maximum_penalty_cap]" class="form-control form-control-sm" placeholder="Max Cap">
                                                    </div>
                                                    <div class="col-md-3 d-flex align-items-end">
                                                        <button type="button" class="btn btn-sm btn-danger remove-late-fee w-100">Remove</button>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <input type="text" name="late_fee_rules[${i}][remarks]" class="form-control form-control-sm" placeholder="Remarks">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`
            });

            setupRepeater({
                addButton: '#add-sd',
                removeButton: '.remove-sd',
                container: '#security-deposit-container',
                itemClass: '.security-deposit-item',
                requireOne: true,
                initialIndex: {{ count(old('security_deposit_structure', [[]])) }},
                template: (i) => `
                                        <div class="card border mb-2 security-deposit-item">
                                            <div class="card-body p-3">
                                                <div class="row g-2">
                                                    <div class="col-md-2">
                                                        <label class="small">Categories</label>
                                                        <select name="security_deposit_structure[${i}][candidate_categories][]" class="form-select form-select-sm select2-category" multiple data-placeholder="Select Categories">
                                                            ${['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">College Type</label>
                                                        <select name="security_deposit_structure[${i}][college_type]" class="form-select form-select-sm">
                                                            ${['Government', 'Private', 'Deemed'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Quota</label>
                                                        <select name="security_deposit_structure[${i}][quota_type]" class="form-select form-select-sm">
                                                            ${['All India', 'State'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Amount</label>
                                                        <input type="number" name="security_deposit_structure[${i}][amount]" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Refundable</label>
                                                        <select name="security_deposit_structure[${i}][refundable]" class="form-select form-select-sm">
                                                            <option value="Yes">Yes</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="button" class="btn btn-sm btn-danger remove-sd w-100">Remove</button>
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <input type="text" name="security_deposit_structure[${i}][refund_conditions]" class="form-control form-control-sm" placeholder="Refund Conditions">
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <input type="text" name="security_deposit_structure[${i}][forfeiture_conditions]" class="form-control form-control-sm" placeholder="Forfeiture Conditions">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`,
                afterAdd: ($row) => {
                    $row.find('.select2-category').select2({ width: '100%', closeOnSelect: false });
                    refreshCategoryOptions('#security-deposit-container');
                },
                afterRemove: () => refreshCategoryOptions('#security-deposit-container')
            });

            setupRepeater({
                addButton: '#add-round-fee',
                removeButton: '.remove-round-fee',
                container: '#round-fee-container',
                itemClass: '.round-fee-item',
                requireOne: true,
                initialIndex: {{ count(old('round_specific_fee_rules', [[]])) }},
                template: (i) => `
                                        <div class="card border mb-2 round-fee-item bg-light">
                                            <div class="card-body p-3">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="small">Round Name</label>
                                                        <input type="text" name="round_specific_fee_rules[${i}][round_name]" class="form-control form-control-sm" placeholder="e.g. Mop-up Round">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Fresh Reg Fee</label>
                                                        <input type="number" name="round_specific_fee_rules[${i}][fresh_registration_fee]" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Addl. Security Deposit</label>
                                                        <input type="number" name="round_specific_fee_rules[${i}][additional_security_deposit]" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Refundable</label>
                                                        <select name="round_specific_fee_rules[${i}][refund_applicable]" class="form-select form-select-sm">
                                                            <option value="Yes">Yes</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="button" class="btn btn-sm btn-danger remove-round-fee w-100">Remove</button>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <input type="text" name="round_specific_fee_rules[${i}][remarks]" class="form-control form-control-sm" placeholder="Remarks">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`
            });

            setupRepeater({
                addButton: '#add-forfeit',
                removeButton: '.remove-forfeit',
                container: '#forfeiture-container',
                itemClass: '.forfeiture-item',
                requireOne: true,
                initialIndex: {{ count(old('forfeiture_scenarios', [[]])) }},
                template: (i) => `
                                        <div class="input-group mb-2 forfeiture-item">
                                            <select name="forfeiture_scenarios[${i}][scenario]" class="form-select">
                                                ${['Seat allotted but not joined', 'Upgradation declined', 'False information'].map(s => `<option value="${s}">${s}</option>`).join('')}
                                            </select>
                                            <input type="text" name="forfeiture_scenarios[${i}][remarks]" class="form-control w-50" placeholder="Optional remarks">
                                            <button class="btn btn-outline-danger remove-forfeit" type="button">Remove</button>
                                        </div>`
            });

            setupRepeater({
                addButton: '#add-round',
                removeButton: '.remove-round',
                container: '#rounds-container',
                itemClass: '.rounds-repeater-item',
                requireOne: true,
                initialIndex: {{ count(old('rounds', [[]])) }},
                template: (i) => `
                                <div class="rounds-repeater-item border p-3 mb-2 rounded mt-2">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="text" name="rounds[${i}][round_name]" class="form-control" placeholder="Round Name">
                                        </div>
                                        <div class="col-md-3">
                                            <select name="rounds[${i}][round_type]" class="form-select">
                                                <option value="Regular">Regular</option>
                                                <option value="Special">Special</option>
                                                <option value="Mop-Up">Mop-Up</option>
                                                <option value="Stray">Stray Vacancy</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check mt-2">
                                                <input type="checkbox" name="rounds[${i}][upgrade_allowed]" value="1" class="form-check-input"> <label>Upgrade</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check mt-2">
                                                <input type="checkbox" name="rounds[${i}][fresh_reg]" value="1" class="form-check-input"> <label>Fresh Reg</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-danger remove-round w-100">Remove</button>
                                        </div>
                                    </div>
                                </div>`
            });
        });
    </script>
@endpush