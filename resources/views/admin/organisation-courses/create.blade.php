@extends('admin.layouts.master')

@section('title', 'Add Course - ' . $organisation->name)

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

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.organisation-courses.index', ['organisation_id' => $organisation->id]) }}"
            class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-1"></i> Back to Courses
        </a>
        <h3 class="fw-bold mt-2">Add New Course to {{ $organisation->name }}</h3>
    </div>

    <form action="{{ route('admin.organisation-courses.store') }}" method="POST">
        @csrf
        <input type="hidden" name="organisation_id" value="{{ $organisation->id }}">

        <div class="row g-4">
            @php $typeId = $organisation->organisation_type_id; @endphp

            {{-- ========================================== --}}
            {{-- UNIVERSITY / COLLEGE (Type 1 & 2) --}}
            {{-- ========================================== --}}
            @if(in_array($typeId, [1, 2]))
                <!-- Basic Info -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-info-circle me-2"></i>Basic Course
                                Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select Course <span class="text-danger">*</span></label>
                                    <select name="course_id" class="form-select select2" required>
                                        <option value="">-- Select Master Course --</option>
                                        @foreach($masterCourses as $master)
                                            <option value="{{ $master->id }}" {{ old('course_id') == $master->id ? 'selected' : '' }}>
                                                {{ $master->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">If not listed, add to <a href="{{ route('admin.courses.index') }}"
                                            target="_blank">Master Course List</a>.</div>
                                    <div class="card mt-2 border-0 shadow-sm bg-light d-none course-details-card">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-book-open me-2"></i>Course Details</h6>
                                            <div class="small text-muted">
                                                <div class="row g-2">
                                                    <div class="col-6"><strong>Program Level:</strong> <span class="program-level">-</span></div>
                                                    <div class="col-6"><strong>Stream:</strong> <span class="stream">-</span></div>
                                                    <div class="col-6"><strong>Discipline:</strong> <span class="discipline">-</span></div>
                                                    <div class="col-6"><strong>Duration:</strong> <span class="duration">-</span> Years</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(isset($campusId) && isset($departmentId))
                                    {{-- Show context when campus and department are pre-selected --}}
                                    <div class="col-12">
                                        <div class="alert alert-soft-primary border-primary d-flex align-items-center" role="alert">
                                            <i class="fas fa-graduation-cap me-2 fs-4"></i>
                                            <div>
                                                Adding Course to: 
                                                <strong>{{ $organisation->name }}</strong>
                                                @php 
                                                    $selectedCampus = $campuses->firstWhere('id', $campusId);
                                                    $selectedDepartment = $departments->firstWhere('id', $departmentId);
                                                @endphp
                                                @if($selectedCampus)
                                                    - <strong>{{ $selectedCampus->campus_name }}</strong>
                                                @endif
                                                @if($selectedDepartment)
                                                    - <strong>{{ $selectedDepartment->department_name }}</strong>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="hidden" name="campus_id" value="{{ $campusId }}">
                                        <input type="hidden" name="department_id" value="{{ $departmentId }}">
                                    </div>
                                @else
                                    {{-- Show dropdowns when not pre-selected --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Select Campus <span class="text-danger">*</span></label>
                                        <select name="campus_id" id="campus_id_select" class="form-select select2" required>
                                            <option value="">-- Select Campus --</option>
                                            @foreach($campuses as $campus)
                                                <option value="{{ $campus->id }}" {{ old('campus_id', $campusId ?? '') == $campus->id ? 'selected' : '' }}>
                                                    {{ $campus->campus_name }}
                                                    @if($campus->campus_type) ({{ $campus->campus_type }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-danger">Course will be linked to this specific campus.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Select Department</label>
                                        <select name="department_id" id="department_id_select" class="form-select select2">
                                            <option value="">-- Select Department --</option>
                                            <!-- Populated via JS -->
                                        </select>
                                    </div>
                                @endif

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Specialization Area (Multiple)</label>
                                    <select name="specialization_ids[]" class="form-select select2" multiple>
                                        @foreach($specializations as $spec)
                                            <option value="{{ $spec->id }}" {{ in_array($spec->id, old('specialization_ids', [])) ? 'selected' : '' }}>
                                                {{ $spec->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Exam Category (Filter)</label>
                                    <select class="form-select select2 exam-category-filter">
                                        <option value="">-- All Categories --</option>
                                        @foreach($examCategories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Admission Route (Exams)</label>
                                    <select name="entrance_exam_ids[]" class="form-select select2 entrance-exam-ids-select" multiple>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" data-category="{{ is_array($exam->exam_category) ? implode(',', $exam->exam_category) : ($exam->exam_category ?? '') }}"
                                                {{ in_array($exam->id, old('entrance_exam_ids', [])) ? 'selected' : '' }}>
                                                {{ $exam->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Course Languages (Multiple)</label>
                                    <select name="course_languages[]" class="form-select select2" multiple>
                                        @foreach($languages as $lang)
                                            <option value="{{ $lang->id }}" {{ in_array($lang->id, old('course_languages', [])) ? 'selected' : '' }}>
                                                {{ $lang->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Mode</label>
                                    <input type="text" name="mode" class="form-control" value="{{ old('mode') }}"
                                        placeholder="e.g. Online">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Total Fees</label>
                                    <input type="number" step="0.01" name="total_fees" class="form-control" value="{{ old('total_fees') }}"
                                        placeholder="e.g. 200000.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Rating (0-5)</label>
                                    <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control"
                                        value="{{ old('rating') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">ROI</label>
                                    <select name="roi" class="form-select">
                                        <option value="">Select ROI</option>
                                        <option value="Low" {{ old('roi') == 'Low' ? 'selected' : '' }}>Low</option>
                                        <option value="Medium" {{ old('roi') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="High" {{ old('roi') == 'High' ? 'selected' : '' }}>High</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="{{ old('sort_order', 0) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1"
                                            checked>
                                        <label class="form-check-label" for="status">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Provisional Admission</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="provisional_admission"
                                            id="provisional_admission" value="1" {{ old('provisional_admission') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="provisional_admission">Available</label>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Info (Rich Text) -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-align-left me-2"></i>Detailed Course
                                Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Admission Process</label>
                                    <textarea name="admission_process" class="editor">{{ old('admission_process') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Eligibility Criteria</label>
                                    <textarea name="eligibility" class="editor">{{ old('eligibility') }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fees Structure Details</label>
                                    <textarea name="fees_structure" class="editor">{{ old('fees_structure') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Curriculum / Syllabus</label>
                                    <textarea name="curriculum" class="editor">{{ old('curriculum') }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Career Prospects</label>
                                    <textarea name="career_prospects" class="editor">{{ old('career_prospects') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Placement Details</label>
                                    <textarea name="placement_details" class="editor">{{ old('placement_details') }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Industrial & Academic Collaborators</label>
                                    <textarea name="industrial_collaboration"
                                        class="editor">{{ old('industrial_collaboration') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Internship & Course Ranking</label>
                                    <textarea name="internship_ranking"
                                        class="editor">{{ old('internship_ranking') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================== --}}
            {{-- INSTITUTE (Type 3) --}}
            {{-- ========================================== --}}
            @if($typeId == 3)
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="fw-bold mb-0 text-primary">Core Academic Identity</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Institute / Academic Unit Name *</label>
                                    <input type="text" name="academic_unit_name" class="form-control" required
                                        value="{{ old('academic_unit_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select Master Course <span class="text-danger">*</span></label>
                                    <select name="course_id" class="form-select select2" required>
                                        <option value="">-- Select Master Course --</option>
                                        @foreach($masterCourses as $master)
                                            <option value="{{ $master->id }}" {{ old('course_id') == $master->id ? 'selected' : '' }}>
                                                {{ $master->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">If not listed, add to <a href="{{ route('admin.courses.index') }}"
                                            target="_blank">Master Course List</a>.</div>
                                    <div class="card mt-2 border-0 shadow-sm bg-light d-none course-details-card">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-book-open me-2"></i>Course Details</h6>
                                            <div class="small text-muted">
                                                <div class="row g-2">
                                                    <div class="col-6"><strong>Program Level:</strong> <span class="program-level">-</span></div>
                                                    <div class="col-6"><strong>Stream:</strong> <span class="stream">-</span></div>
                                                    <div class="col-6"><strong>Discipline:</strong> <span class="discipline">-</span></div>
                                                    <div class="col-6"><strong>Duration:</strong> <span class="duration">-</span> Years</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Exam Category (Filter)</label>
                                    <select class="form-select select2 exam-category-filter">
                                        <option value="">-- All Categories --</option>
                                        @foreach($examCategories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Admission Route (Exams)</label>
                                    <select name="entrance_exam_ids[]" class="form-select select2 entrance-exam-ids-select" multiple>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" data-category="{{ is_array($exam->exam_category) ? implode(',', $exam->exam_category) : ($exam->exam_category ?? '') }}"
                                                {{ in_array($exam->id, old('entrance_exam_ids', [])) ? 'selected' : '' }}>
                                                {{ $exam->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Campus / Centre (Optional)</label>
                                    <select name="campus_id" class="form-select select2">
                                        <option value="">-- Select Centre --</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}" {{ (old('campus_id') == $campus->id || (isset($campusId) && $campusId == $campus->id)) ? 'selected' : '' }}>
                                                {{ $campus->campus_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Department (Optional)</label>
                                    <select name="department_id" class="form-select select2" data-selected="{{ old('department_id', $departmentId ?? '') }}">
                                        <option value="">-- Select Department --</option>
                                        <!-- Populated via JS -->
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Delivery Mode</label>
                                    <select name="delivery_mode" class="form-select">
                                        <option value="">Select Mode</option>
                                        <option value="Offline">Offline</option>
                                        <option value="Online">Online</option>
                                        <option value="Hybrid">Hybrid</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Course Languages (Multiple)</label>
                                    <select name="course_languages[]" class="form-select select2" multiple>
                                        @foreach($languages as $lang)
                                            <option value="{{ $lang->id }}" {{ in_array($lang->id, old('course_languages', [])) ? 'selected' : '' }}>
                                                {{ $lang->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Total Fees</label>
                                    <input type="number" step="0.01" name="total_fees" class="form-control"
                                        value="{{ old('total_fees') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                                <div class="col-md-6" style="display: none;">
                                    <label class="form-label fw-bold">Integrated Schooling Available?</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="integrated_schooling_available"
                                            value="1">
                                        <label class="form-check-label">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add more Institute sections as needed (Faculty, Results, etc.) simplified for brevity but expandable -->
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="fw-bold mb-0 text-primary">Results & Fees</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Total Selections (All Time)</label>
                                    <input type="text" name="total_selections_all_time" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Highest Rank Achieved</label>
                                    <input type="text" name="highest_rank_achieved" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Avg Course Fee Range</label>
                                    <input type="text" name="average_course_fee_range" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================== --}}
            {{-- SCHOOL (Type 4) --}}
            {{-- ========================================== --}}
            @if($typeId == 4)
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="fw-bold mb-0 text-primary">School Identity</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">School Name *</label>
                                    <input type="text" name="academic_unit_name" class="form-control" required
                                        value="{{ old('academic_unit_name') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">School Type</label>
                                    <select name="school_type" class="form-select">
                                        <option value="">Select Type</option>
                                        <option value="Day School">Day School</option>
                                        <option value="Boarding School">Boarding School</option>
                                        <option value="Day-cum-Boarding">Day-cum-Boarding</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Exam Category (Filter)</label>
                                    <select class="form-select select2 exam-category-filter">
                                        <option value="">-- All Categories --</option>
                                        @foreach($examCategories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Admission Route (Exams)</label>
                                    <select name="entrance_exam_ids[]" class="form-select select2 entrance-exam-ids-select" multiple>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" data-category="{{ is_array($exam->exam_category) ? implode(',', $exam->exam_category) : ($exam->exam_category ?? '') }}"
                                                {{ in_array($exam->id, old('entrance_exam_ids', [])) ? 'selected' : '' }}>
                                                {{ $exam->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Course Languages (Multiple)</label>
                                    <select name="course_languages[]" class="form-select select2" multiple>
                                        @foreach($languages as $lang)
                                            <option value="{{ $lang->id }}" {{ in_array($lang->id, old('course_languages', [])) ? 'selected' : '' }}>
                                                {{ $lang->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Total Fees</label>
                                    <input type="number" step="0.01" name="total_fees" class="form-control"
                                        value="{{ old('total_fees') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Campus / Location</label>
                                    <select name="campus_id" class="form-select select2">
                                        <option value="">-- Select Campus --</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                                {{ $campus->campus_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Department (Optional)</label>
                                    <select name="department_id" class="form-select select2">
                                        <option value="">-- Select Department --</option>
                                        <!-- Populated via JS -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="status" id="school_status"
                                            value="1" checked>
                                        <label class="form-check-label" for="school_status">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="fw-bold mb-0 text-primary">Board & Affiliation</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Education Board</label>
                                    <select name="education_board" class="form-select select2">
                                        <option value="">Select Board</option>
                                        <option value="CBSE">CBSE</option>
                                        <option value="ICSE">ICSE</option>
                                        <option value="State Board">State Board</option>
                                        <option value="IB">IB</option>
                                        <option value="IGCSE">IGCSE</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Grade Range</label>
                                    <input type="text" name="grade_range" class="form-control"
                                        placeholder="e.g. Pre-Primary to XII">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Medium of Instruction</label>
                                    <input type="text" name="medium_of_instruction" class="form-control"
                                        placeholder="e.g. English">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Streams Offered</label>
                                    <select name="streams_offered[]" class="form-select select2" multiple>
                                        <option value="Science">Science</option>
                                        <option value="Commerce">Commerce</option>
                                        <option value="Humanities">Humanities</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="fw-bold mb-0 text-primary">Stats & Facilities</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Student Strength</label>
                                    <input type="text" name="student_strength" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Total Teachers</label>
                                    <input type="text" name="total_teachers" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Class Size</label>
                                    <input type="text" name="average_class_size" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Board Result %</label>
                                    <input type="text" name="average_board_result_percentage" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="transport_fee" value="1">
                                        <!-- Using boolean logic for simplicity or modify input type -->
                                        <label class="form-check-label">Transport Available</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hostel_fee" value="1">
                                        <!-- Using boolean logic/presence check -->
                                        <label class="form-check-label">Hostel Available</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            @endif

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </div>
    </form>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                width: '100%' // Ensure proper width
            });

            // Master Course Data
            const masterCourses = @json($masterCourses);
            const departments = @json($departments);

            function filterDepartments(campusSelect) {
                const campusId = $(campusSelect).val();
                // Find the closest department select (in the same row or container)
                // For Type 1&2, it's in the same .row g-4 usually, but we need to be careful.
                // Since we have multiple forms or sections, we can target by name within the form if unique, 
                // but IDK if multiple campus selects exist (conditional rendering in blade).
                // Actually, only one section is rendered based on IF conditions. So `$('select[name="department_id"]')` is safe.
                
                const deptSelect = $('select[name="department_id"]');
                const selectedDept = deptSelect.attr('data-selected') || ''; // Should be passed if old value exists

                deptSelect.empty().append('<option value="">-- Select Department --</option>');

                if (campusId) {
                    const filtered = departments.filter(d => d.campus_id == campusId);
                    filtered.forEach(d => {
                        const isSelected = d.id == selectedDept ? 'selected' : '';
                        deptSelect.append(`<option value="${d.id}" ${isSelected}>${d.department_name}</option>`);
                    });
                }
                deptSelect.trigger('change');
            }

            $('select[name="campus_id"]').on('change', function() {
                filterDepartments(this);
            });
            
            // Trigger on load if campus is selected
            if ($('select[name="campus_id"]').val()) {
                $('select[name="department_id"]').attr('data-selected', "{{ old('department_id') }}");
                filterDepartments($('select[name="campus_id"]'));
            }

            // Function to update card
            function updateCourseCard(select) {
                const courseId = $(select).val();
                // Find the closest card info container relative to the select
                // Since structure is .col-md-6 > label + select + form-text + card
                const card = $(select).siblings('.course-details-card');
                
                if (courseId) {
                    const course = masterCourses.find(c => c.id == courseId);
                    if (course) {
                        card.find('.program-level').text(course.program_level ? course.program_level.title : 'N/A');
                        card.find('.stream').text(course.stream_offered ? course.stream_offered.title : 'N/A');
                        card.find('.discipline').text(course.discipline ? course.discipline.title : 'N/A');
                        card.find('.duration').text(course.duration || 'N/A');
                        card.removeClass('d-none');
                    } else {
                        card.addClass('d-none');
                    }
                } else {
                    card.addClass('d-none');
                }
            }

            // Bind change event
            $('select[name="course_id"]').on('change', function() {
                updateCourseCard(this);
            });

            // Initialize CKEditor for all textareas with class 'editor'
            document.querySelectorAll('textarea.editor').forEach((element) => {
                ClassicEditor
                    .create(element)
                    .catch(error => {
                        console.error(error);
                    });
            });

            // Exam filtering logic
            $('.exam-category-filter').each(function() {
                const categorySelect = $(this);
                const examSelect = categorySelect.closest('.row').find('.entrance-exam-ids-select');
                
                if (examSelect.length === 0) return;

                // Store original options for this specific pair
                const allExamOptions = examSelect.find('option').clone();

                function filterExams(selectedCategory) {
                    const currentSelections = examSelect.val() || [];
                    examSelect.empty();

                    if (selectedCategory) {
                        allExamOptions.each(function () {
                            let catData = $(this).data('category');
                            if (catData && String(catData).split(',').includes(selectedCategory)) {
                                examSelect.append($(this).clone());
                            }
                        });
                    } else {
                        allExamOptions.each(function () {
                            if ($(this).val() !== "") {
                                examSelect.append($(this).clone());
                            }
                        });
                    }

                    examSelect.val(currentSelections);
                    examSelect.trigger('change');
                }

                categorySelect.on('change', function () {
                    filterExams($(this).val());
                });

                // Initial Filter on Load
                if (categorySelect.val()) {
                    filterExams(categorySelect.val());
                }
            });
        });
    </script>
@endpush