<div class="card mb-4 border-primary shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>Stage 3: Interview Details</h6>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" name="stages[3][active]" value="1" id="stage_3_active" {{ isset($selectedStages[3]) ? 'checked' : '' }}>
            <label class="form-check-label text-white" for="stage_3_active">Active</label>
        </div>
    </div>
    <div class="card-body" id="stage_3_fields" style="{{ isset($selectedStages[3]) ? '' : 'display:none;' }}">
        <!-- 1️⃣ CORE INTERVIEW IDENTITY -->
        <h6 class="fw-bold text-primary mb-3">1. Core Interview Identity</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Stage Name</label>
                <input type="text" name="interview[stage_name]" class="form-control"
                    value="{{ $interview->stage_name ?? 'Interview' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Stage Order</label>
                <input type="number" name="interview[stage_order]" class="form-control"
                    value="{{ $interview->stage_order ?? 3 }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Mandatory?</label>
                <select name="interview[mandatory]" class="form-select">
                    <option value="1" {{ ($interview->mandatory ?? true) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->mandatory ?? true) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contribution Type</label>
                <select name="interview[stage_contribution_type]" class="form-select">
                    <option value="merit_deciding" {{ ($interview->stage_contribution_type ?? '') == 'merit_deciding' ? 'selected' : '' }}>Merit Deciding ✅ (UPSC)</option>
                    <option value="qualifying_only" {{ ($interview->stage_contribution_type ?? '') == 'qualifying_only' ? 'selected' : '' }}>Qualifying Only</option>
                    <option value="verification_only" {{ ($interview->stage_contribution_type ?? '') == 'verification_only' ? 'selected' : '' }}>Verification Only</option>
                </select>
            </div>
        </div>

        <!-- 2️⃣ CONDUCTING AUTHORITY -->
        <h6 class="fw-bold text-primary mb-3">2. Conducting Authority</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Conducting Body</label>
                <select name="interview[interview_conducting_body]" class="form-select">
                    <option value="">Select Body</option>
                    <option value="UPSC" {{ ($interview->interview_conducting_body ?? '') == 'UPSC' ? 'selected' : '' }}>
                        UPSC</option>
                    <option value="State PSC" {{ ($interview->interview_conducting_body ?? '') == 'State PSC' ? 'selected' : '' }}>State PSC</option>
                    <option value="University Selection Board" {{ ($interview->interview_conducting_body ?? '') == 'University Selection Board' ? 'selected' : '' }}>University Selection Board</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Panel Type</label>
                <select name="interview[interview_panel_type]" class="form-select">
                    <option value="Single Panel" {{ ($interview->interview_panel_type ?? '') == 'Single Panel' ? 'selected' : '' }}>Single Panel</option>
                    <option value="Multiple Panels" {{ ($interview->interview_panel_type ?? '') == 'Multiple Panels' ? 'selected' : '' }}>Multiple Panels</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Centres Scope</label>
                <select name="interview[interview_centres_scope]" class="form-select">
                    <option value="National" {{ ($interview->interview_centres_scope ?? '') == 'National' ? 'selected' : '' }}>National</option>
                    <option value="State" {{ ($interview->interview_centres_scope ?? '') == 'State' ? 'selected' : '' }}>
                        State</option>
                    <option value="Campus-based" {{ ($interview->interview_centres_scope ?? '') == 'Campus-based' ? 'selected' : '' }}>Campus-based</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Panel Constitution Guidelines</label>
                <textarea name="interview[panel_constitution_guidelines]" class="form-control"
                    rows="2">{{ $interview->panel_constitution_guidelines ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Official Guidelines URL</label>
                <input type="url" name="interview[official_interview_guidelines_url]" class="form-control"
                    value="{{ $interview->official_interview_guidelines_url ?? '' }}">
            </div>
        </div>

        <!-- 3️⃣ INTERVIEW FORMAT & MODE -->
        <h6 class="fw-bold text-primary mb-3">3. Format & Mode</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Mode</label>
                <select name="interview[interview_mode]" class="form-select">
                    <option value="In-Person" {{ ($interview->interview_mode ?? '') == 'In-Person' ? 'selected' : '' }}>
                        In-Person</option>
                    <option value="Online" {{ ($interview->interview_mode ?? '') == 'Online' ? 'selected' : '' }}>Online
                    </option>
                    <option value="Hybrid" {{ ($interview->interview_mode ?? '') == 'Hybrid' ? 'selected' : '' }}>Hybrid
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Duration (Minutes)</label>
                <input type="number" name="interview[interview_duration_minutes]" class="form-control"
                    value="{{ $interview->interview_duration_minutes ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">No. of Panellists</label>
                <input type="number" name="interview[number_of_panellists]" class="form-control"
                    value="{{ $interview->number_of_panellists ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Medium Switch Allowed?</label>
                <select name="interview[medium_switch_allowed]" class="form-select">
                    <option value="1" {{ ($interview->medium_switch_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->medium_switch_allowed ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Language Options (JSON or comma separated)</label>
                <input type="text" name="interview[language_options_raw]" class="form-control"
                    value="{{ isset($interview->language_options) ? implode(',', (array) $interview->language_options) : '' }}"
                    placeholder="Hindi, English, etc.">
            </div>
        </div>

        <!-- 4️⃣ EVALUATION PARAMETERS -->
        <h6 class="fw-bold text-primary mb-3">4. Evaluation Parameters</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label">Evaluation Criteria (Select Multiple)</label>
                <select name="interview[evaluation_criteria][]" class="form-select select2" multiple>
                    @foreach(['Personality', 'Communication Skills', 'Subject Knowledge', 'Analytical Ability', 'Leadership', 'Ethical Judgment'] as $opt)
                        <option value="{{ $opt }}" {{ in_array($opt, (array) ($interview->evaluation_criteria ?? [])) ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Criteria Weightage Defined?</label>
                <select name="interview[criteria_weightage_defined]" class="form-select">
                    <option value="1" {{ ($interview->criteria_weightage_defined ?? false) ? 'selected' : '' }}>Yes
                    </option>
                    <option value="0" {{ !($interview->criteria_weightage_defined ?? false) ? 'selected' : '' }}>No
                    </option>
                </select>
            </div>
        </div>

        <!-- 5️⃣ MARKING & MERIT LOGIC -->
        <h6 class="fw-bold text-primary mb-3">5. Marking & Merit Logic</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <label class="form-label">Marks Applicable?</label>
                <select name="interview[marks_applicable]" class="form-select">
                    <option value="1" {{ ($interview->marks_applicable ?? true) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->marks_applicable ?? true) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Max Marks</label>
                <input type="number" step="0.01" name="interview[maximum_marks]" class="form-control"
                    value="{{ $interview->maximum_marks ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Min Qualifying</label>
                <input type="number" step="0.01" name="interview[minimum_qualifying_marks]" class="form-control"
                    value="{{ $interview->minimum_qualifying_marks ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category Cutoff?</label>
                <select name="interview[category_wise_cutoff_applicable]" class="form-select">
                    <option value="1" {{ ($interview->category_wise_cutoff_applicable ?? false) ? 'selected' : '' }}>Yes
                    </option>
                    <option value="0" {{ !($interview->category_wise_cutoff_applicable ?? false) ? 'selected' : '' }}>No
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Weightage (%)</label>
                <input type="number" step="0.01" name="interview[weightage_percentage]" class="form-control"
                    value="{{ $interview->weightage_percentage ?? '' }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">Norm.?</label>
                <select name="interview[normalization_applied]" class="form-select">
                    <option value="1" {{ ($interview->normalization_applied ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->normalization_applied ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>

        <!-- 6️⃣ ELIGIBILITY TO APPEAR -->
        <h6 class="fw-bold text-primary mb-3">6. Eligibility to Appear</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Prev. Stage Qualification Req.?</label>
                <select name="interview[previous_stage_qualification_required]" class="form-select">
                    <option value="1" {{ ($interview->previous_stage_qualification_required ?? true) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->previous_stage_qualification_required ?? true) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Shortlisting Basis</label>
                <select name="interview[shortlisting_basis]" class="form-select">
                    <option value="Rank-based" {{ ($interview->shortlisting_basis ?? '') == 'Rank-based' ? 'selected' : '' }}>Rank-based</option>
                    <option value="Marks-based" {{ ($interview->shortlisting_basis ?? '') == 'Marks-based' ? 'selected' : '' }}>Marks-based</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Documents Required (JSON/Tags)</label>
                <input type="text" name="interview[documents_required_for_interview_call_raw]" class="form-control"
                    value="{{ isset($interview->documents_required_for_interview_call) ? implode(',', (array) $interview->documents_required_for_interview_call) : '' }}">
            </div>
        </div>

        <!-- 7️⃣ INTERVIEW PROCESS FLOW -->
        <h6 class="fw-bold text-primary mb-3">7. Process Flow</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Process Steps (JSON/Tags)</label>
                <input type="text" name="interview[interview_process_steps_raw]" class="form-control"
                    value="{{ isset($interview->interview_process_steps) ? implode(',', (array) $interview->interview_process_steps) : '' }}"
                    placeholder="Document Verification, Panel Interview, etc.">
            </div>
            <div class="col-md-3">
                <label class="form-label">Identity Verification?</label>
                <select name="interview[identity_verification_required]" class="form-select">
                    <option value="1" {{ ($interview->identity_verification_required ?? true) ? 'selected' : '' }}>Yes
                    </option>
                    <option value="0" {{ !($interview->identity_verification_required ?? true) ? 'selected' : '' }}>No
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Biometric Req.?</label>
                <select name="interview[biometric_verification_required]" class="form-select">
                    <option value="1" {{ ($interview->biometric_verification_required ?? false) ? 'selected' : '' }}>Yes
                    </option>
                    <option value="0" {{ !($interview->biometric_verification_required ?? false) ? 'selected' : '' }}>No
                    </option>
                </select>
            </div>
        </div>

        <!-- 8️⃣ SCHEDULING & SLOT ALLOCATION -->
        <h6 class="fw-bold text-primary mb-3">8. Scheduling & Slots</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Slot Booking Req.?</label>
                <select name="interview[slot_booking_required]" class="form-select">
                    <option value="1" {{ ($interview->slot_booking_required ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->slot_booking_required ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Allocation Method</label>
                <select name="interview[slot_allocation_method]" class="form-select">
                    <option value="Automated" {{ ($interview->slot_allocation_method ?? '') == 'Automated' ? 'selected' : '' }}>Automated</option>
                    <option value="Manual" {{ ($interview->slot_allocation_method ?? '') == 'Manual' ? 'selected' : '' }}>
                        Manual</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Rescheduling Allowed?</label>
                <select name="interview[rescheduling_allowed]" class="form-select">
                    <option value="1" {{ ($interview->rescheduling_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->rescheduling_allowed ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rescheduling Conditions</label>
                <textarea name="interview[rescheduling_conditions]" class="form-control"
                    rows="2">{{ $interview->rescheduling_conditions ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Late Reporting Policy</label>
                <textarea name="interview[late_reporting_policy]" class="form-control"
                    rows="2">{{ $interview->late_reporting_policy ?? '' }}</textarea>
            </div>
        </div>

        <!-- 9️⃣ RESULT & OUTPUT -->
        <h6 class="fw-bold text-primary mb-3">9. Result & Output</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Result Type</label>
                <select name="interview[interview_result_type]" class="form-select">
                    <option value="Marks" {{ ($interview->interview_result_type ?? '') == 'Marks' ? 'selected' : '' }}>
                        Marks</option>
                    <option value="Pass/Fail" {{ ($interview->interview_result_type ?? '') == 'Pass/Fail' ? 'selected' : '' }}>Pass/Fail</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Visibility</label>
                <select name="interview[interview_result_visibility]" class="form-select">
                    <option value="Candidate Login" {{ ($interview->interview_result_visibility ?? '') == 'Candidate Login' ? 'selected' : '' }}>Candidate Login</option>
                    <option value="Public" {{ ($interview->interview_result_visibility ?? '') == 'Public' ? 'selected' : '' }}>Public</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Declaration Date</label>
                <input type="date" name="interview[interview_result_declaration_date]" class="form-control"
                    value="{{ isset($interview->interview_result_declaration_date) ? $interview->interview_result_declaration_date->format('Y-m-d') : '' }}">
            </div>
        </div>

        <!-- 🔟 APPEAL / REVIEW -->
        <h6 class="fw-bold text-primary mb-3">10. Appeal / Review</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Appeal Allowed?</label>
                <select name="interview[appeal_allowed]" class="form-select">
                    <option value="1" {{ ($interview->appeal_allowed ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->appeal_allowed ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Time Limit (Days)</label>
                <input type="number" name="interview[appeal_time_limit_days]" class="form-control"
                    value="{{ $interview->appeal_time_limit_days ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fee Required?</label>
                <select name="interview[appeal_fee_required]" class="form-select">
                    <option value="1" {{ ($interview->appeal_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->appeal_fee_required ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fee Amount</label>
                <input type="number" name="interview[appeal_fee_amount]" class="form-control"
                    value="{{ $interview->appeal_fee_amount ?? '' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Appeal Process Description</label>
                <textarea name="interview[appeal_process_description]" class="form-control"
                    rows="2">{{ $interview->appeal_process_description ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Final Decision Authority</label>
                <input type="text" name="interview[final_decision_authority]" class="form-control"
                    value="{{ $interview->final_decision_authority ?? '' }}">
            </div>
        </div>

        <!-- 1️⃣1️⃣ SPECIAL RULES & RELAXATIONS -->
        <h6 class="fw-bold text-primary mb-3">11. Special Rules & Relaxations</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Category Relaxations (JSON/Tags)</label>
                <input type="text" name="interview[category_relaxations_raw]" class="form-control"
                    value="{{ isset($interview->category_relaxations) ? implode(',', (array) $interview->category_relaxations) : '' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">PWD Accommodations Available?</label>
                <select name="interview[pwd_accommodations_available]" class="form-select">
                    <option value="1" {{ ($interview->pwd_accommodations_available ?? false) ? 'selected' : '' }}>Yes
                    </option>
                    <option value="0" {{ !($interview->pwd_accommodations_available ?? false) ? 'selected' : '' }}>No
                    </option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Ex-Servicemen Relaxations</label>
                <textarea name="interview[ex_servicemen_relaxations]" class="form-control"
                    rows="2">{{ $interview->ex_servicemen_relaxations ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Gender Specific Guidelines</label>
                <textarea name="interview[gender_specific_guidelines]" class="form-control"
                    rows="2">{{ $interview->gender_specific_guidelines ?? '' }}</textarea>
            </div>
        </div>

        <!-- 1️⃣2️⃣ FEES -->
        <h6 class="fw-bold text-primary mb-3">12. Interview Fees</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Fee Required?</label>
                <select name="interview[interview_fee_required]" class="form-select">
                    <option value="1" {{ ($interview->interview_fee_required ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->interview_fee_required ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fee Amount</label>
                <input type="number" name="interview[interview_fee_amount]" class="form-control"
                    value="{{ $interview->interview_fee_amount ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Refundable?</label>
                <select name="interview[fee_refundable]" class="form-select">
                    <option value="1" {{ ($interview->fee_refundable ?? false) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !($interview->fee_refundable ?? false) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Payment Modes (JSON/Tags)</label>
                <input type="text" name="interview[payment_modes_raw]" class="form-control"
                    value="{{ isset($interview->payment_modes) ? implode(',', (array) $interview->payment_modes) : '' }}">
            </div>
        </div>

        <!-- 1️⃣3️⃣ DISCLAIMERS & REFERENCES -->
        <h6 class="fw-bold text-primary mb-3">13. Disclaimers & References</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <label class="form-label">Interview Disclaimer Text</label>
                <textarea name="interview[interview_disclaimer_text]" class="form-control"
                    rows="3">{{ $interview->interview_disclaimer_text ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Information Source</label>
                <input type="text" name="interview[information_source]" class="form-control"
                    value="{{ $interview->information_source ?? '' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Verified On</label>
                <input type="date" name="interview[last_verified_on]" class="form-control"
                    value="{{ isset($interview->last_verified_on) ? $interview->last_verified_on->format('Y-m-d') : '' }}">
            </div>
        </div>

        <!-- 1️⃣4️⃣ ADMIN & CONTROL -->
        <h6 class="fw-bold text-primary mb-3">14. Admin & Control</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Stage Status</label>
                <select name="interview[stage_status]" class="form-select">
                    <option value="Scheduled" {{ ($interview->stage_status ?? '') == 'Scheduled' ? 'selected' : '' }}>
                        Scheduled</option>
                    <option value="Completed" {{ ($interview->stage_status ?? '') == 'Completed' ? 'selected' : '' }}>
                        Completed</option>
                    <option value="Archived" {{ ($interview->stage_status ?? '') == 'Archived' ? 'selected' : '' }}>
                        Archived</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Visibility</label>
                <select name="interview[visibility]" class="form-select">
                    <option value="Public" {{ ($interview->visibility ?? '') == 'Public' ? 'selected' : '' }}>Public
                    </option>
                    <option value="Draft" {{ ($interview->visibility ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Private" {{ ($interview->visibility ?? '') == 'Private' ? 'selected' : '' }}>Private
                    </option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Last Updated On</label>
                <input type="text" class="form-control"
                    value="{{ isset($interview->last_updated_on) ? $interview->last_updated_on->format('d M Y H:i') : '' }}"
                    readonly disabled>
            </div>
            <div class="col-md-12">
                <label class="form-label">Remarks</label>
                <textarea name="interview[remarks]" class="form-control"
                    rows="2">{{ $interview->remarks ?? '' }}</textarea>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('stage_3_active').addEventListener('change', function () {
        document.getElementById('stage_3_fields').style.display = this.checked ? 'block' : 'none';
    });
</script>