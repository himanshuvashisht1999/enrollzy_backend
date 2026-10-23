@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 text-dark fw-bold">
                    <i class="fas fa-user-plus text-primary me-2"></i>Add Faculty Profile
                </h4>
                <p class="text-muted small mb-0">Create a generic faculty profile applicable across universities, colleges, schools, or coaching centers.</p>
            </div>
            <div>
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Faculty List
                </a>
            </div>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Please correct the errors below:</h6>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    <form action="{{ route('admin.faculties.store') }}" method="POST" enctype="multipart/form-data" id="facultyForm">
        @csrf

        {{-- Nav Tabs --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white p-0 border-bottom">
                <ul class="nav nav-tabs nav-fill border-0" id="facultyTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3 fw-semibold text-start" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">
                            <span class="badge bg-primary me-1">1</span> Basic Profile
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold text-start" id="mapping-tab" data-bs-toggle="tab" data-bs-target="#mapping" type="button">
                            <span class="badge bg-secondary me-1">2</span> Institutional Mapping
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold text-start" id="qualifications-tab" data-bs-toggle="tab" data-bs-target="#qualifications" type="button">
                            <span class="badge bg-secondary me-1">3</span> Education & Exp.
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold text-start" id="research-tab" data-bs-toggle="tab" data-bs-target="#research" type="button">
                            <span class="badge bg-secondary me-1">4</span> Research & Papers
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold text-start" id="achievements-tab" data-bs-toggle="tab" data-bs-target="#achievements" type="button">
                            <span class="badge bg-secondary me-1">5</span> Awards & Certs
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold text-start" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">
                            <span class="badge bg-secondary me-1">6</span> Social & Contact
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-semibold text-start" id="custom-seo-tab" data-bs-toggle="tab" data-bs-target="#custom-seo" type="button">
                            <span class="badge bg-secondary me-1">7</span> Custom Fields & SEO
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="facultyTabContent">

                    {{-- TAB 1: BASIC PROFILE --}}
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Faculty Basic Profile</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" placeholder="e.g. Dr. Rajesh Sharma" value="{{ old('full_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Display Name / Title</label>
                                <input type="text" name="display_name" class="form-control" placeholder="e.g. Prof. Rajesh Sharma, Ph.D." value="{{ old('display_name') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Designation (Master) <span class="text-danger">*</span></label>
                                <select name="designation_id" class="form-select" required>
                                    <option value="">-- Select Designation --</option>
                                    <optgroup label="University / Higher Education">
                                        @foreach($designations->where('institution_category', 'university') as $desig)
                                            <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="School Education">
                                        @foreach($designations->where('institution_category', 'school') as $desig)
                                            <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Coaching & Competitive">
                                        @foreach($designations->where('institution_category', 'coaching') as $desig)
                                            <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Other / General">
                                        @foreach($designations->where('institution_category', 'all') as $desig)
                                            <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Faculty Type <span class="text-danger">*</span></label>
                                <select name="faculty_type_id" class="form-select" required>
                                    <option value="">-- Select Faculty Type --</option>
                                    @foreach($facultyTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('faculty_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Profile Photo <span class="text-danger">*</span></label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                                <small class="text-muted">High resolution headshot (JPG, PNG, WebP up to 3MB).</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Date of Joining</label>
                                <input type="date" name="date_of_joining" class="form-control" value="{{ old('date_of_joining') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Years of Experience</label>
                                <div class="input-group">
                                    <input type="number" step="0.5" name="experience_years" class="form-control" placeholder="e.g. 15.5" value="{{ old('experience_years', 0) }}">
                                    <span class="input-group-text">Years</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Short Bio <span class="text-danger">*</span></label>
                                <textarea name="short_bio" class="form-control" rows="2" placeholder="Brief summary highlighted on preview cards..." required>{{ old('short_bio') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Detailed Biography</label>
                                <textarea name="detailed_biography" class="form-control" rows="5" placeholder="Full academic profile, background, milestones...">{{ old('detailed_biography') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch" checked>
                                    <label class="form-check-label fw-bold" for="statusSwitch">Profile Active Status</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: INSTITUTIONAL & COURSE MAPPING --}}
                    <div class="tab-pane fade" id="mapping" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">2. Institutional & Department Mapping</h5>
                                <p class="text-muted small mb-0">Map this faculty member to multiple Organizations, Campuses, and Departments.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addDeptMappingRow()">
                                <i class="fas fa-plus me-1"></i> Add Department Mapping
                            </button>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle" id="deptMappingTable">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="25%">Organisation <span class="text-danger">*</span></th>
                                        <th width="20%">Campus</th>
                                        <th width="20%">Department</th>
                                        <th width="15%">Role / Designation</th>
                                        <th width="10%" class="text-center">Primary?</th>
                                        <th width="10%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="deptMappingContainer">
                                    {{-- Row 0 --}}
                                    <tr class="dept-mapping-row" data-index="0">
                                        <td>
                                            <select name="department_mappings[0][organisation_id]" class="form-select form-select-sm org-select" onchange="loadCampuses(this, 0)" required>
                                                <option value="">-- Select Organisation --</option>
                                                @foreach($organisations as $org)
                                                    <option value="{{ $org->id }}" {{ (old('department_mappings.0.organisation_id') == $org->id || $selectedOrganisationId == $org->id) ? 'selected' : '' }}>{{ $org->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="department_mappings[0][campus_id]" class="form-select form-select-sm campus-select" onchange="loadDepartments(this, 0)">
                                                <option value="">-- Select Campus --</option>
                                                @foreach($campuses as $campus)
                                                    <option value="{{ $campus->id }}" {{ $selectedCampusId == $campus->id ? 'selected' : '' }}>{{ $campus->campus_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="department_mappings[0][department_id]" class="form-select form-select-sm dept-select">
                                                <option value="">-- Select Department --</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}" {{ $selectedDepartmentId == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="department_mappings[0][designation_id]" class="form-select form-select-sm">
                                                <option value="">Default Designation</option>
                                                @foreach($designations as $desig)
                                                    <option value="{{ $desig->id }}">{{ $desig->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="department_mappings[0][is_primary_department]" value="1" class="form-check-input" checked>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- SUB-SECTION: COURSE & TEACHING ASSOCIATION --}}
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 mt-4">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Teaching / Course Association</h5>
                                <p class="text-muted small mb-0">Associate faculty with specific courses, semesters, and subjects.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addCourseMappingRow()">
                                <i class="fas fa-plus me-1"></i> Add Course Association
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="courseMappingTable">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="25%">Course</th>
                                        <th width="25%">Subject / Module Name</th>
                                        <th width="15%">Academic Year</th>
                                        <th width="15%">Semester</th>
                                        <th width="12%">Role</th>
                                        <th width="8%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="courseMappingContainer">
                                    <tr class="course-mapping-row" data-index="0">
                                        <td>
                                            <select name="course_mappings[0][course_id]" class="form-select form-select-sm">
                                                <option value="">-- Select Master Course --</option>
                                                @foreach($courses as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="course_mappings[0][subject_name]" class="form-control form-control-sm" placeholder="e.g. Data Structures / Physics">
                                        </td>
                                        <td>
                                            <input type="text" name="course_mappings[0][academic_year]" class="form-control form-control-sm" placeholder="e.g. 2025-2026">
                                        </td>
                                        <td>
                                            <input type="text" name="course_mappings[0][semester]" class="form-control form-control-sm" placeholder="e.g. Semester 3">
                                        </td>
                                        <td>
                                            <input type="text" name="course_mappings[0][role]" class="form-control form-control-sm" placeholder="e.g. Lead Instructor">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 3: ACADEMIC QUALIFICATIONS & PROFESSIONAL EXPERIENCE --}}
                    <div class="tab-pane fade" id="qualifications" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">3. Academic Qualifications</h5>
                                <p class="text-muted small mb-0">Degrees, specializations, institutions, and certificates.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addQualificationRow()">
                                <i class="fas fa-plus me-1"></i> Add Qualification
                            </button>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="15%">Degree <span class="text-danger">*</span></th>
                                        <th width="18%">Specialization</th>
                                        <th width="20%">College / Institute</th>
                                        <th width="15%">University</th>
                                        <th width="8%">Year</th>
                                        <th width="10%">Grade / %</th>
                                        <th width="10%">Doc Upload</th>
                                        <th width="4%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="qualificationContainer">
                                    <tr class="qual-row" data-index="0">
                                        <td><input type="text" name="qualifications[0][degree]" class="form-control form-control-sm" placeholder="e.g. Ph.D."></td>
                                        <td><input type="text" name="qualifications[0][specialization]" class="form-control form-control-sm" placeholder="Artificial Intelligence"></td>
                                        <td><input type="text" name="qualifications[0][institution]" class="form-control form-control-sm" placeholder="IIT Delhi"></td>
                                        <td><input type="text" name="qualifications[0][university]" class="form-control form-control-sm" placeholder="IIT Delhi"></td>
                                        <td><input type="text" name="qualifications[0][year]" class="form-control form-control-sm" placeholder="2018"></td>
                                        <td><input type="text" name="qualifications[0][grade_percentage]" class="form-control form-control-sm" placeholder="9.2 CGPA"></td>
                                        <td><input type="file" name="qualifications[0][certificate_document]" class="form-control form-control-sm"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- SUB-SECTION: PROFESSIONAL EXPERIENCE --}}
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 mt-4">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Professional Experience Timeline</h5>
                                <p class="text-muted small mb-0">Previous institutions, organizations, and designations.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addExperienceRow()">
                                <i class="fas fa-plus me-1"></i> Add Experience
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="25%">Organization / Institution <span class="text-danger">*</span></th>
                                        <th width="20%">Designation <span class="text-danger">*</span></th>
                                        <th width="15%">Start Date</th>
                                        <th width="15%">End Date</th>
                                        <th width="10%" class="text-center">Currently Here?</th>
                                        <th width="15%">Description</th>
                                        <th width="5%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="experienceContainer">
                                    <tr class="exp-row" data-index="0">
                                        <td><input type="text" name="experiences[0][organization]" class="form-control form-control-sm" placeholder="XYZ University"></td>
                                        <td><input type="text" name="experiences[0][designation]" class="form-control form-control-sm" placeholder="Associate Professor"></td>
                                        <td><input type="date" name="experiences[0][start_date]" class="form-control form-control-sm"></td>
                                        <td><input type="date" name="experiences[0][end_date]" class="form-control form-control-sm"></td>
                                        <td class="text-center"><input type="checkbox" name="experiences[0][currently_working]" value="1" class="form-check-input"></td>
                                        <td><input type="text" name="experiences[0][description]" class="form-control form-control-sm" placeholder="Taught CSE courses"></td>
                                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 4: RESEARCH, SPECIALIZATIONS & PUBLICATIONS --}}
                    <div class="tab-pane fade" id="research" role="tabpanel">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">4. Research Profile Overview</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Research Interests</label>
                                <textarea name="research_interests" class="form-control" rows="2" placeholder="e.g. Natural Language Processing, Machine Learning, Robotics...">{{ old('research_interests') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Research Areas</label>
                                <textarea name="research_areas" class="form-control" rows="2" placeholder="e.g. Computer Vision, Deep Learning, Biomedical Image Analysis...">{{ old('research_areas') }}</textarea>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Total Publications</label>
                                <input type="number" name="total_publications" class="form-control" value="{{ old('total_publications', 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Books Published</label>
                                <input type="number" name="books_published" class="form-control" value="{{ old('books_published', 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Patents Filed/Granted</label>
                                <input type="number" name="patents" class="form-control" value="{{ old('patents', 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Research Projects Managed</label>
                                <input type="number" name="research_projects" class="form-control" value="{{ old('research_projects', 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Grants Received</label>
                                <input type="text" name="grants_received" class="form-control" placeholder="e.g. INR 45,00,000 from DST-SERB" value="{{ old('grants_received') }}">
                            </div>
                        </div>

                        {{-- TAGS SECTION --}}
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Areas of Specialization & Subjects (Tags)</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Areas of Specialization</label>
                                <input type="text" name="specializations" class="form-control" placeholder="Artificial Intelligence, Machine Learning, Data Science" value="{{ old('specializations') }}">
                                <small class="text-muted">Separate with commas.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Research Areas Tags</label>
                                <input type="text" name="research_areas_tags" class="form-control" placeholder="NLP, Computer Vision, Deep Learning" value="{{ old('research_areas_tags') }}">
                                <small class="text-muted">Separate with commas.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Subjects Taught</label>
                                <input type="text" name="subjects_taught" class="form-control" placeholder="Python, Data Structures, Algorithms" value="{{ old('subjects_taught') }}">
                                <small class="text-muted">Separate with commas.</small>
                            </div>
                        </div>

                        {{-- PUBLICATIONS REPEATER --}}
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Research Papers & Publications</h5>
                                <p class="text-muted small mb-0">Journals, Conferences, Books, Patents.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addPublicationRow()">
                                <i class="fas fa-plus me-1"></i> Add Publication
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="25%">Title <span class="text-danger">*</span></th>
                                        <th width="12%">Type</th>
                                        <th width="15%">Journal / Conference</th>
                                        <th width="10%">Date</th>
                                        <th width="10%">DOI</th>
                                        <th width="8%">Citations</th>
                                        <th width="10%">PDF Document</th>
                                        <th width="5%" class="text-center">Verified</th>
                                        <th width="5%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="publicationContainer">
                                    <tr class="pub-row" data-index="0">
                                        <td><input type="text" name="publications[0][title]" class="form-control form-control-sm" placeholder="Title of the paper"></td>
                                        <td>
                                            <select name="publications[0][publication_type]" class="form-select form-select-sm">
                                                <option value="Journal">Journal</option>
                                                <option value="Conference">Conference</option>
                                                <option value="Book">Book</option>
                                                <option value="Patent">Patent</option>
                                                <option value="Article">Article</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="publications[0][journal_conference]" class="form-control form-control-sm" placeholder="IEEE / Springer"></td>
                                        <td><input type="date" name="publications[0][publication_date]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="publications[0][doi]" class="form-control form-control-sm" placeholder="10.1016/..."></td>
                                        <td><input type="number" name="publications[0][citation_count]" class="form-control form-control-sm" value="0"></td>
                                        <td><input type="file" name="publications[0][document_pdf]" class="form-control form-control-sm"></td>
                                        <td class="text-center"><input type="checkbox" name="publications[0][verified]" value="1" class="form-check-input"></td>
                                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 5: ACHIEVEMENTS & CERTIFICATIONS --}}
                    <div class="tab-pane fade" id="achievements" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">5. Achievements & Awards</h5>
                                <p class="text-muted small mb-0">National fellowships, best teacher awards, excellence recognition.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAchievementRow()">
                                <i class="fas fa-plus me-1"></i> Add Achievement
                            </button>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="25%">Title <span class="text-danger">*</span></th>
                                        <th width="15%">Achievement Type</th>
                                        <th width="20%">Awarding Body / Organization</th>
                                        <th width="10%">Year</th>
                                        <th width="15%">Description</th>
                                        <th width="10%">Certificate Image</th>
                                        <th width="5%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="achievementContainer">
                                    <tr class="achieve-row" data-index="0">
                                        <td><input type="text" name="achievements[0][title]" class="form-control form-control-sm" placeholder="Best Teacher Award"></td>
                                        <td>
                                            <select name="achievements[0][achievement_type]" class="form-select form-select-sm">
                                                <option value="Award">Award</option>
                                                <option value="Honor">Honor</option>
                                                <option value="Fellowship">Fellowship</option>
                                                <option value="Recognition">Recognition</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="achievements[0][organization]" class="form-control form-control-sm" placeholder="National Education Society"></td>
                                        <td><input type="text" name="achievements[0][year]" class="form-control form-control-sm" placeholder="2024"></td>
                                        <td><input type="text" name="achievements[0][description]" class="form-control form-control-sm" placeholder="Awarded for research"></td>
                                        <td><input type="file" name="achievements[0][certificate_image]" class="form-control form-control-sm"></td>
                                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- CERTIFICATIONS SUB-SECTION --}}
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 mt-4">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Professional Certifications</h5>
                                <p class="text-muted small mb-0">Industry certifications, credentials, pedagogy trainings.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addCertificationRow()">
                                <i class="fas fa-plus me-1"></i> Add Certification
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="25%">Certification Name <span class="text-danger">*</span></th>
                                        <th width="20%">Issuing Organization</th>
                                        <th width="12%">Issue Date</th>
                                        <th width="12%">Expiry Date</th>
                                        <th width="15%">Credential ID / URL</th>
                                        <th width="10%">Certificate Upload</th>
                                        <th width="6%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="certificationContainer">
                                    <tr class="cert-row" data-index="0">
                                        <td><input type="text" name="certifications[0][certification_name]" class="form-control form-control-sm" placeholder="AWS Certified Solutions Architect"></td>
                                        <td><input type="text" name="certifications[0][issuing_organization]" class="form-control form-control-sm" placeholder="Amazon Web Services"></td>
                                        <td><input type="date" name="certifications[0][issue_date]" class="form-control form-control-sm"></td>
                                        <td><input type="date" name="certifications[0][expiry_date]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="certifications[0][credential_id]" class="form-control form-control-sm" placeholder="AWS-987654"></td>
                                        <td><input type="file" name="certifications[0][certificate_upload]" class="form-control form-control-sm"></td>
                                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 6: SOCIAL PROFILES, PRIVACY & CONTACT --}}
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="fas fa-shield-alt me-1"></i> <strong>Privacy Guard:</strong> Internal contact details are kept secure and strictly hidden from public profiles.
                        </div>

                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">6. Internal Contact / Communication (Private)</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Official Email</label>
                                <input type="email" name="official_email" class="form-control" placeholder="rajesh.sharma@institution.edu" value="{{ old('official_email') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Official Phone</label>
                                <input type="text" name="official_phone" class="form-control" placeholder="+91 9876543210" value="{{ old('official_phone') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Phone Extension</label>
                                <input type="text" name="extension" class="form-control" placeholder="Ext. 4021" value="{{ old('extension') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Office Location / Department Building</label>
                                <input type="text" name="office_location" class="form-control" placeholder="Academic Block B, 3rd Floor" value="{{ old('office_location') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Office Room Number</label>
                                <input type="text" name="office_room_number" class="form-control" placeholder="Room 312" value="{{ old('office_room_number') }}">
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Faculty Social & Professional Profiles</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fab fa-linkedin text-primary me-1"></i> LinkedIn Profile URL</label>
                                <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/..." value="{{ old('linkedin_url') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-graduation-cap text-success me-1"></i> Google Scholar Profile URL</label>
                                <input type="url" name="google_scholar_url" class="form-control" placeholder="https://scholar.google.com/citations?user=..." value="{{ old('google_scholar_url') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-id-badge text-warning me-1"></i> ORCID ID / URL</label>
                                <input type="text" name="orcid_id" class="form-control" placeholder="0000-0002-1825-0097" value="{{ old('orcid_id') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-flask text-teal me-1"></i> ResearchGate URL</label>
                                <input type="url" name="researchgate_url" class="form-control" placeholder="https://www.researchgate.net/profile/..." value="{{ old('researchgate_url') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-globe text-secondary me-1"></i> Personal Website / Portfolio</label>
                                <input type="url" name="personal_website_url" class="form-control" placeholder="https://www.rajeshsharma.com" value="{{ old('personal_website_url') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Scopus Author ID</label>
                                <input type="text" name="scopus_id" class="form-control" placeholder="5720000000" value="{{ old('scopus_id') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Vidwan ID (INFLIBNET)</label>
                                <input type="text" name="vidwan_id" class="form-control" placeholder="VIDWAN-109283" value="{{ old('vidwan_id') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Academia.edu Profile URL</label>
                                <input type="url" name="academia_url" class="form-control" placeholder="https://independent.academia.edu/..." value="{{ old('academia_url') }}">
                            </div>
                        </div>
                    </div>

                    {{-- TAB 7: CUSTOM FIELDS, SEO & VERIFICATION --}}
                    <div class="tab-pane fade" id="custom-seo" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">7. Institution-Specific Dynamic Custom Fields</h5>
                                <p class="text-muted small mb-0">Dynamic fields for Universities (Citations, h-index), Schools (Boards, Classes), or Coaching (AIR Ranks, Batches).</p>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="addCustomFieldPreset('university')">+ University Preset</button>
                                <button type="button" class="btn btn-outline-success" onclick="addCustomFieldPreset('school')">+ School Preset</button>
                                <button type="button" class="btn btn-outline-warning" onclick="addCustomFieldPreset('coaching')">+ Coaching Preset</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="addCustomFieldRow()">+ Custom Row</button>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th width="15%">Institution Type</th>
                                        <th width="25%">Field Key (e.g. h_index)</th>
                                        <th width="25%">Field Label</th>
                                        <th width="30%">Field Value</th>
                                        <th width="5%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="customFieldsContainer">
                                    {{-- Prepopulated Starter Rows --}}
                                    <tr class="custom-field-row" data-index="0">
                                        <td>
                                            <select name="custom_fields[0][institution_type]" class="form-select form-select-sm">
                                                <option value="university" selected>University</option>
                                                <option value="school">School</option>
                                                <option value="coaching">Coaching</option>
                                                <option value="general">General</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="custom_fields[0][field_key]" class="form-control form-control-sm" value="h_index"></td>
                                        <td><input type="text" name="custom_fields[0][field_label]" class="form-control form-control-sm" value="h-index"></td>
                                        <td><input type="text" name="custom_fields[0][field_value]" class="form-control form-control-sm" placeholder="e.g. 18"></td>
                                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- SEO URL SECTION --}}
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Profile URL & SEO Metadata</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Profile Slug (URL)</label>
                                <input type="text" name="slug" class="form-control" placeholder="dr-rajesh-sharma (auto-generated if left blank)" value="{{ old('slug') }}">
                                <small class="text-muted">Direct public URL on Enrollzy directory.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" placeholder="Dr. Rajesh Sharma - Professor of CSE | Enrollzy" value="{{ old('meta_title') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2" placeholder="Explore academic background, research papers, and courses taught by...">{{ old('meta_description') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SEO Keywords</label>
                                <textarea name="meta_keywords" class="form-control" rows="2" placeholder="faculty, professor, CSE, machine learning">{{ old('meta_keywords') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Canonical URL</label>
                                <input type="url" name="canonical_url" class="form-control" placeholder="https://enrollzy.com/faculty/dr-rajesh-sharma" value="{{ old('canonical_url') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Profile Status</label>
                                <select name="profile_status" class="form-select">
                                    <option value="published" selected>Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_indexed" value="1" id="indexSwitch" checked>
                                    <label class="form-check-label fw-bold" for="indexSwitch">Allow Search Indexing</label>
                                </div>
                            </div>
                        </div>

                        {{-- VERIFICATION WORKFLOW --}}
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Profile Verification Badge</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Verification Status</label>
                                <select name="verification_status" class="form-select">
                                    <option value="pending" selected>Pending Review</option>
                                    <option value="verified">Verified (Show ✓ Verified Badge)</option>
                                    <option value="unverified">Unverified</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Verification Source</label>
                                <input type="text" name="verification_source" class="form-control" placeholder="e.g. Official University Website / HR Letter" value="{{ old('verification_source') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Verification Notes / Remarks</label>
                                <input type="text" name="verification_notes" class="form-control" placeholder="Internal remarks for compliance" value="{{ old('verification_notes') }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer Controls --}}
            <div class="card-footer bg-light d-flex justify-content-between align-items-center py-3">
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Save Faculty Profile
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('js')
<script>
    // Row Index Trackers
    let deptIndex = 1;
    let courseIndex = 1;
    let qualIndex = 1;
    let expIndex = 1;
    let pubIndex = 1;
    let achieveIndex = 1;
    let certIndex = 1;
    let customIndex = 1;

    window.removeRow = function(btn) {
        const row = btn.closest('tr');
        if (row) {
            row.remove();
        }
    };

    // Dynamic Cascading for Department Mapping
    window.loadCampuses = function(selectElem, index) {
        const orgId = selectElem.value;
        const row = selectElem.closest('tr');
        const campusSelect = row.querySelector('.campus-select');
        const deptSelect = row.querySelector('.dept-select');

        campusSelect.innerHTML = '<option value="">-- Loading Campuses... --</option>';
        deptSelect.innerHTML = '<option value="">-- Select Department --</option>';

        if (!orgId) {
            campusSelect.innerHTML = '<option value="">-- Select Campus --</option>';
            return;
        }

        fetch(`/admin/faculties/ajax/campuses/${orgId}`)
            .then(res => res.json())
            .then(data => {
                campusSelect.innerHTML = '<option value="">-- Select Campus --</option>';
                data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.campus_name;
                    campusSelect.appendChild(opt);
                });
                window.loadDepartments(campusSelect, index);
            })
            .catch(() => {
                campusSelect.innerHTML = '<option value="">-- Error Loading --</option>';
            });
    };

    window.loadDepartments = function(selectElem, index) {
        const campusId = selectElem.value;
        const row = selectElem.closest('tr');
        const orgSelect = row.querySelector('.org-select');
        const orgId = orgSelect ? orgSelect.value : '';
        const deptSelect = row.querySelector('.dept-select');

        deptSelect.innerHTML = '<option value="">-- Loading Departments... --</option>';

        fetch(`/admin/faculties/ajax/departments?campus_id=${campusId}&organisation_id=${orgId}`)
            .then(res => res.json())
            .then(data => {
                deptSelect.innerHTML = '<option value="">-- Select Department --</option>';
                data.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.department_name;
                    deptSelect.appendChild(opt);
                });
            })
            .catch(() => {
                deptSelect.innerHTML = '<option value="">-- Error Loading --</option>';
            });
    };

    // Add Department Mapping Row
    window.addDeptMappingRow = function() {
        const container = document.getElementById('deptMappingContainer');
        const tr = document.createElement('tr');
        tr.className = 'dept-mapping-row';
        tr.dataset.index = deptIndex;
        tr.innerHTML = `
            <td>
                <select name="department_mappings[${deptIndex}][organisation_id]" class="form-select form-select-sm org-select" onchange="window.loadCampuses(this, ${deptIndex})" required>
                    <option value="">-- Select Organisation --</option>
                    @foreach($organisations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="department_mappings[${deptIndex}][campus_id]" class="form-select form-select-sm campus-select" onchange="window.loadDepartments(this, ${deptIndex})">
                    <option value="">-- Select Campus --</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->campus_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="department_mappings[${deptIndex}][department_id]" class="form-select form-select-sm dept-select">
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="department_mappings[${deptIndex}][designation_id]" class="form-select form-select-sm">
                    <option value="">Default Designation</option>
                    @foreach($designations as $desig)
                        <option value="{{ $desig->id }}">{{ $desig->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <input type="checkbox" name="department_mappings[${deptIndex}][is_primary_department]" value="1" class="form-check-input">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
        container.appendChild(tr);
        deptIndex++;
    };

    // Add Course Mapping Row
    window.addCourseMappingRow = function() {
        const container = document.getElementById('courseMappingContainer');
        const tr = document.createElement('tr');
        tr.className = 'course-mapping-row';
        tr.dataset.index = courseIndex;
        tr.innerHTML = `
            <td>
                <select name="course_mappings[${courseIndex}][course_id]" class="form-select form-select-sm">
                    <option value="">-- Select Master Course --</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="course_mappings[${courseIndex}][subject_name]" class="form-control form-control-sm" placeholder="Subject Name"></td>
            <td><input type="text" name="course_mappings[${courseIndex}][academic_year]" class="form-control form-control-sm" placeholder="2025-2026"></td>
            <td><input type="text" name="course_mappings[${courseIndex}][semester]" class="form-control form-control-sm" placeholder="Semester"></td>
            <td><input type="text" name="course_mappings[${courseIndex}][role]" class="form-control form-control-sm" placeholder="Role"></td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
        container.appendChild(tr);
        courseIndex++;
    };

    // Add Qualification Row
    window.addQualificationRow = function() {
        const container = document.getElementById('qualificationContainer');
        const tr = document.createElement('tr');
        tr.className = 'qual-row';
        tr.dataset.index = qualIndex;
        tr.innerHTML = `
            <td><input type="text" name="qualifications[${qualIndex}][degree]" class="form-control form-control-sm" placeholder="Degree"></td>
            <td><input type="text" name="qualifications[${qualIndex}][specialization]" class="form-control form-control-sm" placeholder="Specialization"></td>
            <td><input type="text" name="qualifications[${qualIndex}][institution]" class="form-control form-control-sm" placeholder="Institution"></td>
            <td><input type="text" name="qualifications[${qualIndex}][university]" class="form-control form-control-sm" placeholder="University"></td>
            <td><input type="text" name="qualifications[${qualIndex}][year]" class="form-control form-control-sm" placeholder="Year"></td>
            <td><input type="text" name="qualifications[${qualIndex}][grade_percentage]" class="form-control form-control-sm" placeholder="Grade"></td>
            <td><input type="file" name="qualifications[${qualIndex}][certificate_document]" class="form-control form-control-sm"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
        `;
        container.appendChild(tr);
        qualIndex++;
    };

    // Add Experience Row
    window.addExperienceRow = function() {
        const container = document.getElementById('experienceContainer');
        const tr = document.createElement('tr');
        tr.className = 'exp-row';
        tr.dataset.index = expIndex;
        tr.innerHTML = `
            <td><input type="text" name="experiences[${expIndex}][organization]" class="form-control form-control-sm" placeholder="Organization"></td>
            <td><input type="text" name="experiences[${expIndex}][designation]" class="form-control form-control-sm" placeholder="Designation"></td>
            <td><input type="date" name="experiences[${expIndex}][start_date]" class="form-control form-control-sm"></td>
            <td><input type="date" name="experiences[${expIndex}][end_date]" class="form-control form-control-sm"></td>
            <td class="text-center"><input type="checkbox" name="experiences[${expIndex}][currently_working]" value="1" class="form-check-input"></td>
            <td><input type="text" name="experiences[${expIndex}][description]" class="form-control form-control-sm" placeholder="Description"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
        `;
        container.appendChild(tr);
        expIndex++;
    };

    // Add Publication Row
    window.addPublicationRow = function() {
        const container = document.getElementById('publicationContainer');
        const tr = document.createElement('tr');
        tr.className = 'pub-row';
        tr.dataset.index = pubIndex;
        tr.innerHTML = `
            <td><input type="text" name="publications[${pubIndex}][title]" class="form-control form-control-sm" placeholder="Title"></td>
            <td>
                <select name="publications[${pubIndex}][publication_type]" class="form-select form-select-sm">
                    <option value="Journal">Journal</option>
                    <option value="Conference">Conference</option>
                    <option value="Book">Book</option>
                    <option value="Patent">Patent</option>
                    <option value="Article">Article</option>
                </select>
            </td>
            <td><input type="text" name="publications[${pubIndex}][journal_conference]" class="form-control form-control-sm" placeholder="Journal"></td>
            <td><input type="date" name="publications[${pubIndex}][publication_date]" class="form-control form-control-sm"></td>
            <td><input type="text" name="publications[${pubIndex}][doi]" class="form-control form-control-sm" placeholder="DOI"></td>
            <td><input type="number" name="publications[${pubIndex}][citation_count]" class="form-control form-control-sm" value="0"></td>
            <td><input type="file" name="publications[${pubIndex}][document_pdf]" class="form-control form-control-sm"></td>
            <td class="text-center"><input type="checkbox" name="publications[${pubIndex}][verified]" value="1" class="form-check-input"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
        `;
        container.appendChild(tr);
        pubIndex++;
    };

    // Add Achievement Row
    window.addAchievementRow = function() {
        const container = document.getElementById('achievementContainer');
        const tr = document.createElement('tr');
        tr.className = 'achieve-row';
        tr.dataset.index = achieveIndex;
        tr.innerHTML = `
            <td><input type="text" name="achievements[${achieveIndex}][title]" class="form-control form-control-sm" placeholder="Title"></td>
            <td>
                <select name="achievements[${achieveIndex}][achievement_type]" class="form-select form-select-sm">
                    <option value="Award">Award</option>
                    <option value="Honor">Honor</option>
                    <option value="Fellowship">Fellowship</option>
                    <option value="Recognition">Recognition</option>
                </select>
            </td>
            <td><input type="text" name="achievements[${achieveIndex}][organization]" class="form-control form-control-sm" placeholder="Organization"></td>
            <td><input type="text" name="achievements[${achieveIndex}][year]" class="form-control form-control-sm" placeholder="Year"></td>
            <td><input type="text" name="achievements[${achieveIndex}][description]" class="form-control form-control-sm" placeholder="Description"></td>
            <td><input type="file" name="achievements[${achieveIndex}][certificate_image]" class="form-control form-control-sm"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
        `;
        container.appendChild(tr);
        achieveIndex++;
    };

    // Add Certification Row
    window.addCertificationRow = function() {
        const container = document.getElementById('certificationContainer');
        const tr = document.createElement('tr');
        tr.className = 'cert-row';
        tr.dataset.index = certIndex;
        tr.innerHTML = `
            <td><input type="text" name="certifications[${certIndex}][certification_name]" class="form-control form-control-sm" placeholder="Certification Name"></td>
            <td><input type="text" name="certifications[${certIndex}][issuing_organization]" class="form-control form-control-sm" placeholder="Issuing Body"></td>
            <td><input type="date" name="certifications[${certIndex}][issue_date]" class="form-control form-control-sm"></td>
            <td><input type="date" name="certifications[${certIndex}][expiry_date]" class="form-control form-control-sm"></td>
            <td><input type="text" name="certifications[${certIndex}][credential_id]" class="form-control form-control-sm" placeholder="Credential ID"></td>
            <td><input type="file" name="certifications[${certIndex}][certificate_upload]" class="form-control form-control-sm"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
        `;
        container.appendChild(tr);
        certIndex++;
    };

    // Add Custom Field Row
    window.addCustomFieldRow = function(type = 'general', key = '', label = '', val = '') {
        const container = document.getElementById('customFieldsContainer');
        const tr = document.createElement('tr');
        tr.className = 'custom-field-row';
        tr.dataset.index = customIndex;
        tr.innerHTML = `
            <td>
                <select name="custom_fields[${customIndex}][institution_type]" class="form-select form-select-sm">
                    <option value="university" ${type === 'university' ? 'selected' : ''}>University</option>
                    <option value="school" ${type === 'school' ? 'selected' : ''}>School</option>
                    <option value="coaching" ${type === 'coaching' ? 'selected' : ''}>Coaching</option>
                    <option value="general" ${type === 'general' ? 'selected' : ''}>General</option>
                </select>
            </td>
            <td><input type="text" name="custom_fields[${customIndex}][field_key]" class="form-control form-control-sm" value="${key}" placeholder="e.g. h_index"></td>
            <td><input type="text" name="custom_fields[${customIndex}][field_label]" class="form-control form-control-sm" value="${label}" placeholder="e.g. h-index"></td>
            <td><input type="text" name="custom_fields[${customIndex}][field_value]" class="form-control form-control-sm" value="${val}" placeholder="Value"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="window.removeRow(this)"><i class="fas fa-trash-alt"></i></button></td>
        `;
        container.appendChild(tr);
        customIndex++;
    };

    // Custom Field Presets
    window.addCustomFieldPreset = function(type) {
        if (type === 'university') {
            window.addCustomFieldRow('university', 'google_scholar_citations', 'Google Scholar Citations', '');
            window.addCustomFieldRow('university', 'researchgate_score', 'ResearchGate Score', '');
            window.addCustomFieldRow('university', 'h_index', 'h-index', '');
        } else if (type === 'school') {
            window.addCustomFieldRow('school', 'classes_taught', 'Classes Taught (Grades)', '9th, 10th, 11th, 12th');
            window.addCustomFieldRow('school', 'board_specialization', 'Education Board', 'CBSE / ICSE');
            window.addCustomFieldRow('school', 'olympiad_experience', 'Olympiad Training Experience', 'Yes');
        } else if (type === 'coaching') {
            window.addCustomFieldRow('coaching', 'target_exams', 'Target Exams Mentored', 'JEE Advanced / NEET');
            window.addCustomFieldRow('coaching', 'air_ranks_mentored', 'Top AIR Ranks Produced', 'AIR 12, AIR 45');
            window.addCustomFieldRow('coaching', 'batches_taught', 'Flagship Batches Taught', 'Rankers Batch');
        }
    };
</script>
@endpush
@endsection
