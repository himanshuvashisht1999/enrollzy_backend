@extends('admin.layouts.master')

@section('title', 'Student Registration')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card { border-radius: 12px; border: none; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 1.25rem; }
        .section-head { font-size: 0.9rem; font-weight: 700; color: #4e73df; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem; border-bottom: 2px solid #4e73df; display: inline-block; padding-bottom: 3px; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #555; }
        .form-control, .form-select { padding: 0.6rem 0.75rem; font-size: 0.9rem; border-radius: 6px; }
        .photo-preview-wrapper { width: 120px; height: 120px; border: 2px dashed #ddd; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; background: #f9f9f9; }
        .photo-preview-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .academic-table th { background: #f8f9fc; font-size: 0.75rem; text-transform: uppercase; color: #888; }
        .doc-upload-item { background: #fcfcfc; padding: 8px 12px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 8px; }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <form action="{{ route('admin.customers.main.index.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Comprehensive Registration Form</h5>
                            <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">Offline Form Sync</span>
                        </div>
                        <div class="card-body p-4">

                            <!-- 1. Personal Information -->
                            <div class="section-head">1. Personal Information</div>
                            <div class="row g-3 mb-4 align-items-start">
                                <div class="col-md-2 text-center">
                                    <div class="photo-preview-wrapper mx-auto mb-2" onclick="$('#student_photo').click()">
                                        <div id="photo_placeholder" class="text-center">
                                            <i class="fas fa-camera text-muted mb-1"></i><br>
                                            <small class="text-muted" style="font-size: 0.65rem;">Upload Photo</small>
                                        </div>
                                        <img id="photo_preview" style="display:none;">
                                    </div>
                                    <input type="file" name="image" id="student_photo" class="d-none" accept="image/*">
                                </div>
                                <div class="col-md-10">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Student Name *</label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">DOB</label>
                                            <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Gender</label>
                                            <select name="gender" class="form-select">
                                                <option value="">Select</option>
                                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Category</label>
                                            <select name="category_id" class="form-select select2" required>
                                                <option value="">Select Category</option>
                                                @php
                                                    function renderCategoryOptions($categories, $level = 0) {
                                                        foreach ($categories as $cat) {
                                                            echo '<option value="'.$cat->id.'"'.
                                                                (old('category_id') == $cat->id ? ' selected' : '').
                                                                '>';
                                                            echo str_repeat("— ", $level).$cat->name;
                                                            echo '</option>';
                                                            if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                                                renderCategoryOptions($cat->childrenRecursive, $level + 1);
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                @php renderCategoryOptions($categories); @endphp
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Mobile Number *</label>
                                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Alternate Mobile</label>
                                            <input type="text" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Email ID</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Aadhaar Number</label>
                                            <input type="text" name="aadhaar_number" class="form-control" value="{{ old('aadhaar_number') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Pincode</label>
                                            <input type="text" name="pincode" id="pincode" class="form-control" placeholder="6-digit" value="{{ old('pincode') }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">State</label>
                                            <input type="text" name="state" id="state" class="form-control" value="{{ old('state') }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">City</label>
                                            <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}">
                                        </div>
                                        {{-- <div class="col-md-4">
                                            <label class="form-label">Institute</label>
                                            <select name="institute_id" class="form-select select2">
                                                <option value="">Select Institute</option>
                                                @foreach($institutes as $ins)
                                                    <option value="{{ $ins->id }}" {{ old('institute_id') == $ins->id ? 'selected' : '' }}>{{ $ins->name }}</option>
                                                @endforeach
                                            </select>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Current Academic Details -->
                            <div class="section-head">2. Current Academic Details</div>
                            <div class="mb-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-bold">Current Course/Programme</label>
                                            @php
                                                $selectedCourse = old('current_course', isset($customer) ? ($customer->current_course_id ?? $customer->current_course_text ?? '') : '');
                                                $courseIsId = is_numeric($selectedCourse);
                                            @endphp
                                            <select name="current_course" id="current_course" class="form-select rounded-3 custom-select2">
                                                <option value="">Select or Type Course</option>
                                                @if(isset($courses))
                                                    @foreach($courses as $course)
                                                        <option value="{{ $course->id }}" {{ $selectedCourse == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                                    @endforeach
                                                @endif
                                                @if(!empty($selectedCourse) && !$courseIsId && (!isset($courses) || !$courses->contains('id', $selectedCourse)))
                                                    <option value="{{ $selectedCourse }}" selected>{{ $selectedCourse }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-bold">Current Session</label>
                                            @php
                                                $selectedSession = old('current_session', isset($customer) ? ($customer->current_session ?? '') : '');
                                            @endphp
                                            <select name="current_session" id="current_session" class="form-select rounded-3 custom-select2">
                                                <option value="">Select Session</option>
                                                @if(isset($sessions))
                                                    @foreach($sessions as $session)
                                                        <option value="{{ $session->id }}" {{ $selectedSession == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                                    @endforeach
                                                @endif
                                                @if(!empty($selectedSession) && (!isset($sessions) || !$sessions->contains('id', $selectedSession)))
                                                    <option value="{{ $selectedSession }}" selected>{{ $selectedSession }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-bold">Current University</label>
                                            @php
                                                $selectedUniversity = old('current_university', isset($customer) ? ($customer->current_university_id ?? $customer->current_university_text ?? '') : '');
                                                $universityIsId = is_numeric($selectedUniversity);
                                            @endphp
                                            <select name="current_university" id="current_university" class="form-select rounded-3 custom-select2">
                                                <option value="">Select or Type University</option>
                                                @if(isset($universities))
                                                    @foreach($universities as $uni)
                                                        <option value="{{ $uni->id }}" {{ $selectedUniversity == $uni->id ? 'selected' : '' }}>{{ $uni->name }}</option>
                                                    @endforeach
                                                @endif
                                                @if(!empty($selectedUniversity) && !$universityIsId && (!isset($universities) || !$universities->contains('id', $selectedUniversity)))
                                                    <option value="{{ $selectedUniversity }}" selected>{{ $selectedUniversity }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-bold">Current Program Mode</label>
                                            @php
                                                $selectedMode = old('current_program_mode', isset($customer) ? ($customer->current_course_type ?? '') : '');
                                             @endphp
                                            <select name="current_program_mode" id="current_program_mode" class="form-select rounded-3 custom-select2">
                                                <option value="">Select Program Mode</option>
                                                @if(isset($program_types))
                                                    @foreach($program_types as $pt)
                                                        <option value="{{ $pt->title }}" {{ $selectedMode == $pt->title ? 'selected' : '' }}>{{ $pt->title }}</option>
                                                    @endforeach
                                                @endif
                                                @if(!empty($selectedMode) && (!isset($program_types) || !$program_types->contains('title', $selectedMode)))
                                                    <option value="{{ $selectedMode }}" selected>{{ $selectedMode }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Program of Interest -->
                            <div class="section-head">3. Program of Interest</div>
                            <div class="row g-3 mb-4">
                                <div class="col-lg-4">
                                    <label class="form-label small fw-bold">Program Level</label>
                                    <select name="program_level_id" id="program_level_id" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type Program Level</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @if(isset($program_levels))
                                            @foreach($program_levels as $pl)
                                                <option value="{{ $pl->id }}" {{ old('program_level_id') == $pl->id ? 'selected' : '' }}>{{ $pl->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-4" id="school_type_container" style="display:none;">
                                    <label class="form-label small fw-bold">School Type</label>
                                    <select name="school_type" id="school_type" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type School Type</option>
                                        @if(isset($school_types))
                                            @foreach($school_types as $st)
                                                <option value="{{ $st->id }}" {{ old('school_type') == $st->id ? 'selected' : '' }}>{{ $st->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-4" id="course_container">
                                    <label class="form-label small fw-bold" id="course_label">Course</label>
                                    <select name="course_input" id="course_input" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type Course</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_input') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4" id="course_type_container">
                                    <label class="form-label small fw-bold">Program Mode</label>
                                    <select name="course_type" id="course_type" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type Program Mode</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @if(isset($program_types))
                                            @foreach($program_types as $pt)
                                                <option value="{{ $pt->title }}" data-db-id="{{ $pt->id }}" {{ old('course_type') == $pt->title ? 'selected' : '' }}>{{ $pt->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-6" id="university_container">
                                    <label class="form-label small fw-bold" id="university_label">University / Organization</label>
                                    <select name="university_input" id="university_input" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type University</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @foreach($universities as $uni)
                                            @php
                                                $types = [];
                                                $orgType = is_array($uni->campus_type_new_id) ? $uni->campus_type_new_id : json_decode($uni->campus_type_new_id, true) ?? [$uni->campus_type_new_id];
                                                if(is_array($orgType)) {
                                                    $types = array_merge($types, $orgType);
                                                }
                                                if ($uni->campuses) {
                                                    foreach($uni->campuses as $campus) {
                                                        $campType = is_array($campus->campus_type_new_id) ? $campus->campus_type_new_id : json_decode($campus->campus_type_new_id, true) ?? [$campus->campus_type_new_id];
                                                        if(is_array($campType)) {
                                                            $types = array_merge($types, $campType);
                                                        }
                                                    }
                                                }
                                                // Clean up array
                                                $types = array_values(array_unique(array_filter($types)));
                                            @endphp
                                            <option value="{{ $uni->id }}" data-type-id="{{ $uni->organisation_type_id }}" data-school-type-id="{{ json_encode($types) }}" {{ old('university_input') == $uni->id ? 'selected' : '' }}>{{ $uni->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label small fw-bold">Session</label>
                                    <select name="session_id" id="session_input" class="form-select rounded-3 custom-select2">
                                        <option value="">Select Session</option>
                                        @if(isset($sessions))
                                            @foreach($sessions as $session)
                                                <option value="{{ $session->id }}" {{ old('session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <!-- 4. Parent/Guardian Information -->
                            <div class="section-head">4. Parent/Guardian Information</div>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <h6 class="small fw-bold text-muted border-bottom pb-2 mb-3">Father's Details</h6>
                                    <div class="row g-2">
                                        <div class="col-12"><input type="text" name="father_name" class="form-control form-control-sm" placeholder="Father Name" value="{{ old('father_name') }}"></div>
                                        <div class="col-md-6"><input type="text" name="father_mobile" class="form-control form-control-sm" placeholder="Mobile" value="{{ old('father_mobile') }}"></div>
                                        <div class="col-md-6"><input type="email" name="father_email" class="form-control form-control-sm" placeholder="Email" value="{{ old('father_email') }}"></div>
                                        <div class="col-12"><input type="text" name="father_occupation" class="form-control form-control-sm" placeholder="Occupation" value="{{ old('father_occupation') }}"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="small fw-bold text-muted border-bottom pb-2 mb-3">Mother's Details</h6>
                                    <div class="row g-2">
                                        <div class="col-12"><input type="text" name="mother_name" class="form-control form-control-sm" placeholder="Mother Name" value="{{ old('mother_name') }}"></div>
                                        <div class="col-md-6"><input type="text" name="mother_mobile" class="form-control form-control-sm" placeholder="Mobile" value="{{ old('mother_mobile') }}"></div>
                                        <div class="col-md-6"><input type="email" name="mother_email" class="form-control form-control-sm" placeholder="Email" value="{{ old('mother_email') }}"></div>
                                        <div class="col-12"><input type="text" name="mother_occupation" class="form-control form-control-sm" placeholder="Occupation" value="{{ old('mother_occupation') }}"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Academic History -->
                            <div class="section-head">5. Academic Records</div>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered academic-table text-center">
                                    <thead>
                                        <tr>
                                            <th>Examination</th>
                                            <th>Board / University</th>
                                            <th>School / College</th>
                                            <th>Year</th>
                                            <th>% / CGPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(['10th', '12th', 'Graduation', 'Post Graduation', 'Other'] as $exam)
                                            <tr>
                                                <td class="fw-bold small bg-light align-middle">{{ $exam }}</td>
                                                <td><input type="text" name="academics[{{ $exam }}][board]" class="form-control form-control-sm border-0" value="{{ old('academics.'.$exam.'.board') }}"></td>
                                                <td><input type="text" name="academics[{{ $exam }}][college]" class="form-control form-control-sm border-0" value="{{ old('academics.'.$exam.'.college') }}"></td>
                                                <td><input type="text" name="academics[{{ $exam }}][year]" class="form-control form-control-sm border-0" value="{{ old('academics.'.$exam.'.year') }}"></td>
                                                <td><input type="text" name="academics[{{ $exam }}][percentage]" class="form-control form-control-sm border-0" value="{{ old('academics.'.$exam.'.percentage') }}"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- 6. Documents & Additional -->
                            <div class="row mb-4">
                                <div class="col-md-7">
                                    <div class="section-head">6. Documents Upload</div>
                                    <div class="row g-2">
                                        @foreach(['Aadhaar Card', '10th Marksheet', '12th Marksheet', 'Graduation', 'PG Marksheet', 'Passport Photo', 'Caste Cert.', 'Income Cert.', 'Other'] as $doc)
                                            <div class="col-md-6">
                                                <div class="doc-upload-item d-flex align-items-center justify-content-between">
                                                    <span class="small fw-bold text-muted">{{ $doc }}</span>
                                                    <input type="file" name="documents[{{ $doc }}]" class="form-control form-control-sm w-50" style="font-size: 0.65rem;">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="section-head">7. Referral & Source</div>
                                    <div class="card bg-light border-0 p-3">
                                        <div class="mb-3">
                                            <label class="form-label">Referred By</label>
                                            <input type="text" name="referred_by" class="form-control" value="{{ old('referred_by') }}">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label d-block">Sibling Enrolled?</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="sibling_enrolled" id="s_yes" value="1" {{ old('sibling_enrolled') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="s_yes">Yes</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="sibling_enrolled" id="s_no" value="0" {{ old('sibling_enrolled', '0') == '0' ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="s_no">No</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mt-2" id="sibling_details_container" style="{{ old('sibling_enrolled') == '1' ? '' : 'display:none;' }}">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Sibling Name</label>
                                                        <input type="text" name="sibling_name" class="form-control form-control-sm" value="{{ old('sibling_name') }}" placeholder="Name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Sibling Age</label>
                                                        <input type="number" name="sibling_age" class="form-control form-control-sm" value="{{ old('sibling_age') }}" placeholder="Age">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Lead Source</label>
                                                <select name="source" class="form-select">
                                                    <option value="">Select Source</option>
                                                    @foreach(['Google', 'Facebook', 'Newspaper', 'Friend', 'Website', 'Other'] as $src)
                                                        <option value="{{ $src }}" {{ old('source') == $src ? 'selected' : '' }}>{{ $src }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Office Section -->
                            <div class="section-head">8. Office Use Only</div>
                            <div class="row g-3 p-3 bg-light rounded-3">
                                <div class="col-md-3">
                                    <label class="form-label">Reg. Number</label>
                                    <input type="text" name="registration_no" class="form-control" placeholder="INTERNAL-ID" value="{{ old('registration_no') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Reg. Date</label>
                                    <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Counselor</label>
                                    <input type="text" name="counselor_name" class="form-control" value="{{ old('counselor_name') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Class / Batch</label>
                                    <input type="text" name="class_batch" class="form-control" placeholder="e.g. Evening Batch" value="{{ old('class_batch') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="Unpaid" {{ old('payment_status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                        <option value="Paid" {{ old('payment_status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="Partial" {{ old('payment_status') == 'Partial' ? 'selected' : '' }}>Partial</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Account Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Any special notes...">{{ old('remarks') }}</textarea>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer bg-white p-4 text-center border-0">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill">REGISTER STUDENT</button>
                            <a href="{{ route('admin.customers.main.index.index') }}" class="btn btn-link text-muted ms-3">Back to List</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            let initialUniversity = $('#university_input').val();
            $('.select2').select2({ width: '100%' });

            $('#student_photo').on('change', function () {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function (event) {
                        $('#photo_preview').attr('src', event.target.result).show();
                        $('#photo_placeholder').hide();
                    }
                    reader.readAsDataURL(file);
                }
            });

            $('#pincode').on('keyup', function () {
                let pin = $(this).val();
                if (pin.length === 6) {
                    $.getJSON(`https://api.postalpincode.in/pincode/${pin}`, function (res) {
                        if (res[0].Status === "Success") {
                            let post = res[0].PostOffice[0];
                            $('#state').val(post.State);
                            $('#city').val(post.District);
                        }
                    });
                }
            });

            // Sibling Details Toggle
            $('input[name="sibling_enrolled"]').on('change', function() {
                if ($(this).val() == '1') {
                    $('#sibling_details_container').fadeIn();
                } else {
                    $('#sibling_details_container').fadeOut();
                    $('input[name="sibling_name"]').val('');
                    $('input[name="sibling_age"]').val('');
                }
            });

            $('#university_input').select2({
                tags: true,
                placeholder: "Select or Type University",
                allowClear: true,
                width: '100%'
            });

            $('#course_input').select2({
                tags: true,
                placeholder: "Select or Type Course",
                allowClear: true,
                width: '100%'
            });

            $('#program_level_id').select2({
                tags: true,
                placeholder: "Select or Type Program Level",
                allowClear: true,
                width: '100%'
            });

            $('#course_type').select2({
                tags: true,
                placeholder: "Select or Type Program Mode",
                allowClear: true,
                width: '100%'
            });
            
            $('#school_type').select2({
                tags: true,
                placeholder: "Select or Type School Type",
                allowClear: true,
                width: '100%'
            });

            $('#session_input').select2({
                placeholder: "Select Session",
                allowClear: true,
                width: '100%'
            });

            $('#current_course').select2({
                tags: true,
                placeholder: "Select or Type Course",
                allowClear: true,
                width: '100%'
            });

            $('#current_session').select2({
                tags: true,
                placeholder: "Select Session",
                allowClear: true,
                width: '100%'
            });

            $('#current_university').select2({
                tags: true,
                placeholder: "Select or Type University",
                allowClear: true,
                width: '100%'
            });

            $('#current_program_mode').select2({
                tags: true,
                placeholder: "Select Program Mode",
                allowClear: true,
                width: '100%'
            });

            let allUniversities = [];
            let allCourseTypes = [];
            let allCourses = [];
            let courseProgramTypes = @json($course_program_types ?? []);
            
            $('#university_input option').each(function() {
                let stid = $(this).attr('data-school-type-id');
                try {
                    stid = JSON.parse(stid);
                } catch(e) {}
                allUniversities.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    typeId: $(this).data('type-id'),
                    schoolTypeId: stid
                });
            });
            
            $('#course_type option').each(function() {
                allCourseTypes.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    dbId: $(this).data('db-id')
                });
            });

            $('#course_input option').each(function() {
                allCourses.push({
                    id: $(this).val(),
                    text: $(this).text()
                });
            });

            $('#program_level_id').on('change', function() {
                let levelId = $(this).val();
                let selectedText = $(this).find('option:selected').text().trim().toLowerCase();
                
                let universitySelect = $('#university_input');
                universitySelect.empty();

                if (selectedText === 'school') {
                    $('#school_type_container').show();
                    $('#course_label').text('Choose Class');
                    $('#course_type_container').hide();
                    $('#university_label').text('School Name');
                    
                    $('#school_type').val('');
                    setTimeout(function() {
                        $('#school_type').trigger('change');
                    }, 10);
                } else if (selectedText === 'competetive coaching' || selectedText === 'competitive coaching') {
                    $('#school_type_container').hide();
                    $('#course_label').text('Course');
                    $('#course_type_container').show();
                    $('#university_label').text('Choose institute');
                    
                    allUniversities.forEach(function(u) {
                        if (!u.id || u.id === 'Not decided yet' || u.typeId == 3) {
                            let option = new Option(u.text, u.id, false, false);
                            $(option).attr('data-type-id', u.typeId);
                            universitySelect.append(option);
                        }
                    });
                } else {
                    $('#school_type_container').hide();
                    $('#course_label').text('Course');
                    $('#course_type_container').show();
                    $('#university_label').text('University / Organization');
                    
                    allUniversities.forEach(function(u) {
                        if (u.typeId != 4 || !u.id || u.id === 'Not decided yet') {
                            let option = new Option(u.text, u.id, false, false);
                            $(option).attr('data-type-id', u.typeId);
                            universitySelect.append(option);
                        }
                    });
                }
                universitySelect.trigger('change');

                let courseSelect = $('#course_input');
                courseSelect.html('<option value="">Loading...</option>').trigger('change');
                
                $.ajax({
                    url: '{{ route("admin.students-crm.calling-module.get-courses") }}',
                    type: 'GET',
                    data: { program_level_id: levelId },
                    success: function(res) {
                        let html = '<option value="">Select or Type Course</option>';
                        html += '<option value="Not decided yet">Not decided yet</option>';
                        if(res && res.length > 0) {
                            res.forEach(c => {
                                html += `<option value="${c.id}">${c.name}</option>`;
                            });
                        } else {
                            allCourses.forEach(function(c) {
                                if (c.id && c.id !== 'Not decided yet') {
                                    html += `<option value="${c.id}">${c.text}</option>`;
                                }
                            });
                        }
                        courseSelect.html(html).trigger('change');
                    },
                    error: function() {
                        courseSelect.html('<option value="">Select or Type Course</option><option value="Not decided yet">Not decided yet</option>').trigger('change');
                    }
                });
            });

            $('#school_type').on('change', function() {
                let schoolTypeId = $(this).val();
                let universitySelect = $('#university_input');
                let currentVal = universitySelect.val();
                if (isInitialLoad && initialUniversity) {
                    currentVal = initialUniversity;
                }
                universitySelect.empty();
                
                allUniversities.forEach(function(u) {
                    if (!u.id || u.id === 'Not decided yet' || u.typeId == 4) {
                        if (!schoolTypeId || !u.id || u.id === 'Not decided yet') {
                            let option = new Option(u.text, u.id, false, false);
                            $(option).attr('data-type-id', u.typeId);
                            universitySelect.append(option);
                        } else {
                            let sTypes = Array.isArray(u.schoolTypeId) ? u.schoolTypeId.map(String) : (u.schoolTypeId ? [String(u.schoolTypeId)] : []);
                            if (sTypes.includes(String(schoolTypeId))) {
                                let option = new Option(u.text, u.id, false, false);
                                $(option).attr('data-type-id', u.typeId);
                                universitySelect.append(option);
                            }
                        }
                    }
                });
                universitySelect.val(currentVal).trigger('change');
            });

            $('#course_input').on('change', function() {
                let courseId = $(this).val();
                let programLevelText = $('#program_level_id').find('option:selected').text().trim().toLowerCase();
                
                let courseTypeSelect = $('#course_type');
                
                if (programLevelText === 'competetive coaching' || programLevelText === 'competitive coaching') {
                    courseTypeSelect.empty();
                    
                    let option1 = new Option('Select or Type Program Mode', '', false, false);
                    let option2 = new Option('Not decided yet', 'Not decided yet', false, false);
                    courseTypeSelect.append(option1).append(option2);

                    if (courseId && courseId !== 'Not decided yet') {
                        let allowedTypeIds = courseProgramTypes
                            .filter(cpt => cpt.course_id == courseId)
                            .map(cpt => parseInt(cpt.program_type_id));
                            
                        allCourseTypes.forEach(function(ct) {
                            if (ct.id && ct.id !== 'Not decided yet' && allowedTypeIds.includes(parseInt(ct.dbId))) {
                                let option = new Option(ct.text, ct.id, false, false);
                                $(option).attr('data-db-id', ct.dbId);
                                courseTypeSelect.append(option);
                            }
                        });
                    }
                    courseTypeSelect.trigger('change');
                } else {
                    courseTypeSelect.empty();
                    allCourseTypes.forEach(function(ct) {
                        let option = new Option(ct.text, ct.id, false, false);
                        if (ct.dbId) $(option).attr('data-db-id', ct.dbId);
                        courseTypeSelect.append(option);
                    });
                    courseTypeSelect.trigger('change');
                }
            });
        });
    </script>
@endpush
