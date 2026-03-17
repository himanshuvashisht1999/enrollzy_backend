@extends('admin.layouts.master')

@section('title', 'Manage Preliminary Stage - ' . $exam->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Manage Preliminary Stage</h4>
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

    <form action="{{ route('admin.exams.preliminary.update', $exam->id) }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <ul class="nav nav-pills card-header-pills" id="stageTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="eligibility-tab" data-bs-toggle="tab"
                            data-bs-target="#eligibility" type="button">1. Eligibility & Pattern</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="dates-tab" data-bs-toggle="tab" data-bs-target="#dates"
                            type="button">2. Important Dates</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="cutoff-tab" data-bs-toggle="tab" data-bs-target="#cutoff"
                            type="button">3. Result & Cutoff</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="fees-tab" data-bs-toggle="tab" data-bs-target="#fees" type="button">4.
                            Application & Fees</button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="stageTabsContent">

                    {{-- TAB 1: ELIGIBILITY & PATTERN --}}
                    <div class="tab-pane fade show active" id="eligibility" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Stage Display Name</label>
                                <input type="text" name="stage_name" class="form-control"
                                    value="{{ old('stage_name', $preliminary->stage_name ?? 'Preliminary Exam') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mandatory?</label>
                                <select name="mandatory" class="form-select">
                                    <option value="1" {{ old('mandatory', $preliminary->mandatory ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('mandatory', $preliminary->mandatory ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">Eligibility Criteria</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Subjects Required (Comma separated)</label>
                                <input type="text" name="subjects_required_raw" class="form-control"
                                    value="{{ old('subjects_required_raw', isset($preliminary->subjects_required) ? implode(',', (array) $preliminary->subjects_required) : '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Attempt Limit</label>
                                <input type="number" name="attempt_limit" class="form-control"
                                    value="{{ old('attempt_limit', $preliminary->attempt_limit ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gap Year Allowed?</label>
                                <select name="gap_year_allowed" class="form-select">
                                    <option value="1" {{ old('gap_year_allowed', $preliminary->gap_year_allowed ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('gap_year_allowed', $preliminary->gap_year_allowed ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Eligibility Notes</label>
                                <textarea name="eligibility_notes" class="form-control editor"
                                    rows="3">{{ old('eligibility_notes', $preliminary->eligibility_notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">Exam Pattern & Syllabus</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Mode</label>
                                <select name="exam_mode" class="form-select">
                                    <option value="Online" {{ old('exam_mode', $preliminary->exam_mode ?? '') == 'Online' ? 'selected' : '' }}>Online (CBT)</option>
                                    <option value="Offline" {{ old('exam_mode', $preliminary->exam_mode ?? '') == 'Offline' ? 'selected' : '' }}>Offline (Pen & Paper)</option>
                                    <option value="Hybrid" {{ old('exam_mode', $preliminary->exam_mode ?? '') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Format</label>
                                <select name="exam_format" class="form-select">
                                    <option value="MCQ" {{ old('exam_format', $preliminary->exam_format ?? '') == 'MCQ' ? 'selected' : '' }}>MCQ</option>
                                    <option value="Descriptive" {{ old('exam_format', $preliminary->exam_format ?? '') == 'Descriptive' ? 'selected' : '' }}>Descriptive</option>
                                    <option value="Mixed" {{ old('exam_format', $preliminary->exam_format ?? '') == 'Mixed' ? 'selected' : '' }}>Mixed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total Qs</label>
                                <input type="number" name="total_questions" class="form-control"
                                    value="{{ old('total_questions', $preliminary->total_questions ?? '') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total Marks</label>
                                <input type="number" name="total_marks" class="form-control"
                                    value="{{ old('total_marks', $preliminary->total_marks ?? '') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Duration (Mins)</label>
                                <input type="number" name="duration_minutes" class="form-control"
                                    value="{{ old('duration_minutes', $preliminary->duration_minutes ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Negative Marking?</label>
                                <select name="negative_marking" class="form-select">
                                    <option value="1" {{ old('negative_marking', $preliminary->negative_marking ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('negative_marking', $preliminary->negative_marking ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Marking Scheme</label>
                                <input type="text" name="negative_marking_scheme" class="form-control"
                                    value="{{ old('negative_marking_scheme', $preliminary->negative_marking_scheme ?? '') }}"
                                    placeholder="e.g. -0.25">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Syllabus URL</label>
                                <input type="url" name="syllabus_url" class="form-control"
                                    value="{{ old('syllabus_url', $preliminary->syllabus_url ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Difficulty</label>
                                <select name="difficulty_level" class="form-select">
                                    @foreach(['Easy', 'Moderate', 'Hard', 'Very Hard'] as $lvl)
                                        <option value="{{ $lvl }}" {{ old('difficulty_level', $preliminary->difficulty_level ?? '') == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Subjects Covered (Comma separated)</label>
                                <input type="text" name="subjects_covered_raw" class="form-control"
                                    value="{{ old('subjects_covered_raw', isset($preliminary->subjects_covered) ? implode(',', (array) $preliminary->subjects_covered) : '') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Syllabus Source/Notes</label>
                                <textarea name="syllabus_source" class="form-control editor"
                                    rows="2">{{ old('syllabus_source', $preliminary->syllabus_source ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: IMPORTANT DATES --}}
                    <div class="tab-pane fade" id="dates" role="tabpanel">
                        <h6 class="fw-bold text-primary mb-3">Exam Sessions & Dates</h6>
                        <div id="sessions-container">
                            @php $sessions = old('sessions_data', $preliminary->sessions_data ?? []); @endphp
                            @forelse($sessions as $index => $sess)
                                <div class="card mb-3 session-row bg-light border p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="fw-bold">Session Details</h6>
                                        <button type="button" class="btn btn-sm btn-danger remove-session-btn"><i
                                                class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-2">
                                            <label class="small">Year</label>
                                            <input type="text" name="sessions_data[{{$index}}][academic_year]"
                                                class="form-control form-control-sm" value="{{ $sess['academic_year'] ?? '' }}"
                                                required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small">App. Start</label>
                                            <input type="date" name="sessions_data[{{$index}}][application_start_date]"
                                                class="form-control form-control-sm"
                                                value="{{ $sess['application_start_date'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small">App. End</label>
                                            <input type="date" name="sessions_data[{{$index}}][application_end_date]"
                                                class="form-control form-control-sm"
                                                value="{{ $sess['application_end_date'] ?? '' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small">Exam Date</label>
                                            <input type="date" name="sessions_data[{{$index}}][exam_date]"
                                                class="form-control form-control-sm" value="{{ $sess['exam_date'] ?? '' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small">Result Date</label>
                                            <input type="date" name="sessions_data[{{$index}}][result_declaration_date]"
                                                class="form-control form-control-sm"
                                                value="{{ $sess['result_declaration_date'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small no-sessions-msg">No sessions added yet.</p>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mb-4" id="add-session-btn">+ Add
                            Session</button>

                        <h6 class="fw-bold text-primary mb-3">Procedures</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Admit Card Download Procedure</label>
                                <textarea name="admit_card_download_procedure" class="form-control editor"
                                    rows="2">{{ old('admit_card_download_procedure', $preliminary->admit_card_download_procedure ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Result Check Procedure</label>
                                <textarea name="result_check_procedure" class="form-control editor"
                                    rows="2">{{ old('result_check_procedure', $preliminary->result_check_procedure ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: RESULT & CUTOFF --}}
                    <div class="tab-pane fade" id="cutoff" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Score Type</label>
                                <select name="score_type" class="form-select">
                                    <option value="Marks" {{ old('score_type', $preliminary->score_type ?? '') == 'Marks' ? 'selected' : '' }}>Marks</option>
                                    <option value="Percentile" {{ old('score_type', $preliminary->score_type ?? '') == 'Percentile' ? 'selected' : '' }}>Percentile</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Normalization Applied?</label>
                                <select name="normalization_applied" class="form-select">
                                    <option value="1" {{ old('normalization_applied', $preliminary->normalization_applied ?? false) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !old('normalization_applied', $preliminary->normalization_applied ?? false) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tie Breaking Rules</label>
                                <input type="text" name="tie_breaking_rules" class="form-control"
                                    value="{{ old('tie_breaking_rules', $preliminary->tie_breaking_rules ?? '') }}">
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">Cutoff Information</h6>
                        <div id="cutoff-container">
                            @php $cutoffs = old('cutoff_year_wise', $preliminary->cutoff_year_wise ?? []); @endphp
                            @forelse($cutoffs as $index => $cut)
                                <div class="card mb-2 cutoff-row bg-light border p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="fw-bold">Cutoff Year: {{ $cut['year'] ?? '' }}</h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-cutoff-btn"><i
                                                class="fas fa-times"></i></button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-2">
                                            <input type="text" name="cutoff_year_wise[{{$index}}][year]"
                                                class="form-control form-control-sm" placeholder="Year"
                                                value="{{ $cut['year'] ?? '' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="cutoff_year_wise[{{$index}}][general]"
                                                class="form-control form-control-sm" placeholder="General"
                                                value="{{ $cut['general'] ?? '' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="cutoff_year_wise[{{$index}}][obc]"
                                                class="form-control form-control-sm" placeholder="OBC"
                                                value="{{ $cut['obc'] ?? '' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="cutoff_year_wise[{{$index}}][sc_st]"
                                                class="form-control form-control-sm" placeholder="SC/ST"
                                                value="{{ $cut['sc_st'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="cutoff_year_wise[{{$index}}][notes]"
                                                class="form-control form-control-sm" placeholder="Notes"
                                                value="{{ $cut['notes'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small">No cutoffs added.</p>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-cutoff-btn">+ Add Cutoff
                            Row</button>
                    </div>

                    {{-- TAB 4: APPLICATION & FEES --}}
                    <div class="tab-pane fade" id="fees" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch p-2 border rounded">
                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                        name="registration_fee_required" value="1" {{ old('registration_fee_required', $preliminary->registration_fee_required ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">Registration Fee Required for this Stage</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3">Fee Structure</h6>
                        <div id="fee-section-wrapper"
                            style="{{ old('registration_fee_required', $preliminary->registration_fee_required ?? false) ? '' : 'display:none;' }}">
                            <div id="fee-container">
                                @php $feeStructure = old('registration_fee_structure', $preliminary->registration_fee_structure ?? []); @endphp
                                @forelse($feeStructure as $index => $fee)
                                    <div class="card border mb-2 fee-item bg-light p-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6 class="fw-bold">Fee Group</h6>
                                            <button type="button"
                                                class="btn btn-sm btn-link text-danger remove-fee-btn p-0">Remove</button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="small">Categories</label>
                                                <select name="registration_fee_structure[{{$index}}][categories][]"
                                                    class="form-select form-select-sm select2-category" multiple
                                                    data-placeholder="Select Categories">
                                                    @foreach(['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD'] as $cat)
                                                        <option value="{{$cat}}" {{ (isset($fee['categories']) && in_array($cat, $fee['categories'])) ? 'selected' : '' }}>{{$cat}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small">Amount</label>
                                                <input type="number" name="registration_fee_structure[{{$index}}][amount]"
                                                    class="form-control form-control-sm" placeholder="Amount"
                                                    value="{{ $fee['amount'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small">Refundable</label>
                                                <select name="registration_fee_structure[{{$index}}][refundable]"
                                                    class="form-select form-select-sm">
                                                    <option value="No" {{ ($fee['refundable'] ?? '') == 'No' ? 'selected' : '' }}>
                                                        Non-Refundable</option>
                                                    <option value="Yes" {{ ($fee['refundable'] ?? '') == 'Yes' ? 'selected' : '' }}>Refundable</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted small">No fees defined.</p>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-4" id="add-fee-btn">+ Add Fee
                                Category</button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Modes (Comma separated)</label>
                                <input type="text" name="payment_modes_allowed_raw" class="form-control"
                                    value="{{ old('payment_modes_allowed_raw', isset($preliminary->payment_modes_allowed) ? implode(',', (array) $preliminary->payment_modes_allowed) : '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tx Charges Applicable?</label>
                                <select name="transaction_charges_applicable" class="form-select">
                                    <option value="1" {{ old('transaction_charges_applicable', $preliminary->transaction_charges_applicable ?? false) ? 'selected' : '' }}>Yes
                                    </option>
                                    <option value="0" {{ !old('transaction_charges_applicable', $preliminary->transaction_charges_applicable ?? false) ? 'selected' : '' }}>No
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Borne By</label>
                                <select name="transaction_charge_borne_by" class="form-select">
                                    <option value="Candidate" {{ old('transaction_charge_borne_by', $preliminary->transaction_charge_borne_by ?? '') == 'Candidate' ? 'selected' : '' }}>
                                        Candidate</option>
                                    <option value="Authority" {{ old('transaction_charge_borne_by', $preliminary->transaction_charge_borne_by ?? '') == 'Authority' ? 'selected' : '' }}>
                                        Authority</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-end py-3 border-top">
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> Save Preliminary Stage Details
                </button>
            </div>
        </div>
    </form>

    {{-- TEMPLATES FOR REPEATERS --}}
    <template id="session-template">
        <div class="card mb-3 session-row bg-light border p-3">
            <div class="d-flex justify-content-between mb-2">
                <h6 class="fw-bold">New Session</h6>
                <button type="button" class="btn btn-sm btn-danger remove-session-btn"><i class="fas fa-trash"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="small">Year</label>
                    <input type="text" name="sessions_data[INDEX][academic_year]" class="form-control form-control-sm"
                        placeholder="2025" required>
                </div>
                <div class="col-md-3">
                    <label class="small">App. Start</label>
                    <input type="date" name="sessions_data[INDEX][application_start_date]"
                        class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="small">App. End</label>
                    <input type="date" name="sessions_data[INDEX][application_end_date]"
                        class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="small">Exam Date</label>
                    <input type="date" name="sessions_data[INDEX][exam_date]" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="small">Result Date</label>
                    <input type="date" name="sessions_data[INDEX][result_declaration_date]"
                        class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </template>

    <template id="cutoff-template">
        <div class="card mb-2 cutoff-row bg-light border p-3">
            <div class="d-flex justify-content-between mb-2">
                <h6 class="fw-bold">New Cutoff</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-cutoff-btn"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-md-2"><input type="text" name="cutoff_year_wise[INDEX][year]"
                        class="form-control form-control-sm" placeholder="Year"></div>
                <div class="col-md-2"><input type="text" name="cutoff_year_wise[INDEX][general]"
                        class="form-control form-control-sm" placeholder="General"></div>
                <div class="col-md-2"><input type="text" name="cutoff_year_wise[INDEX][obc]"
                        class="form-control form-control-sm" placeholder="OBC"></div>
                <div class="col-md-2"><input type="text" name="cutoff_year_wise[INDEX][sc_st]"
                        class="form-control form-control-sm" placeholder="SC/ST"></div>
                <div class="col-md-4"><input type="text" name="cutoff_year_wise[INDEX][notes]"
                        class="form-control form-control-sm" placeholder="Notes"></div>
            </div>
        </div>
    </template>

    <template id="fee-template">
        <div class="card border mb-2 fee-item bg-light p-3">
            <div class="d-flex justify-content-between mb-2">
                <h6 class="fw-bold">New Fee</h6>
                <button type="button" class="btn btn-sm btn-link text-danger remove-fee-btn p-0">Remove</button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="small">Categories</label>
                    <select name="registration_fee_structure[INDEX][categories][]"
                        class="form-select form-select-sm select2-category" multiple data-placeholder="Select Categories">
                        @foreach(['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD'] as $cat)
                            <option value="{{$cat}}">{{$cat}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small">Amount</label>
                    <input type="number" name="registration_fee_structure[INDEX][amount]"
                        class="form-control form-control-sm" placeholder="Amount">
                </div>
                <div class="col-md-4">
                    <label class="small">Refundable</label>
                    <select name="registration_fee_structure[INDEX][refundable]" class="form-select form-select-sm">
                        <option value="No">Non-Refundable</option>
                        <option value="Yes">Refundable</option>
                    </select>
                </div>
            </div>
        </div>
    </template>

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

        h6.text-primary {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }

        .card-header-pills {
            border-bottom: none;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            const allCategories = ['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD'];

            function initSelect2() {
                if ($('.select2-category').length) {
                    $('.select2-category').select2({
                        theme: 'bootstrap-5',
                        placeholder: "Select Categories",
                        allowClear: true,
                        width: '100%'
                    });
                }
                updateCategoryOptions();
            }

            function updateCategoryOptions() {
                let selectedValues = [];
                $('.select2-category').each(function () {
                    let val = $(this).val();
                    if (val) {
                        selectedValues = selectedValues.concat(Array.isArray(val) ? val : [val]);
                    }
                });

                $('.select2-category').each(function () {
                    let currentVal = $(this).val() || [];
                    if (!Array.isArray(currentVal)) currentVal = [currentVal];

                    $(this).find('option').each(function () {
                        let optVal = $(this).val();
                        if (selectedValues.includes(optVal) && !currentVal.includes(optVal)) {
                            $(this).attr('disabled', 'disabled');
                        } else {
                            $(this).removeAttr('disabled');
                        }
                    });

                    // Trigger select2 to refresh the disabled state
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                        $(this).select2({
                            theme: 'bootstrap-5',
                            placeholder: "Select Categories",
                            allowClear: true,
                            width: '100%'
                        });
                    }
                });
            }

            initSelect2();

            $(document).on('change', '.select2-category', function () {
                updateCategoryOptions();
            });

            $('input[name="registration_fee_required"]').change(function () {
                if ($(this).is(':checked')) {
                    $('#fee-section-wrapper').slideDown();
                } else {
                    $('#fee-section-wrapper').slideUp();
                }
            });

            function initRepeater(triggerId, containerId, templateId) {
                let index = $(containerId + ' .card').length;
                $(triggerId).click(function () {
                    let html = $(templateId).html().replace(/INDEX/g, index);
                    $(containerId).append(html);
                    $(containerId + ' .no-sessions-msg').remove();
                    initSelect2();
                    index++;
                });
                $(containerId).on('click', '.remove-session-btn, .remove-cutoff-btn, .remove-fee-btn', function () {
                    $(this).closest('.card').remove();
                    updateCategoryOptions();
                });
            }

            initRepeater('#add-session-btn', '#sessions-container', '#session-template');
            initRepeater('#add-cutoff-btn', '#cutoff-container', '#cutoff-template');
            initRepeater('#add-fee-btn', '#fee-container', '#fee-template');
        });
    </script>
@endpush