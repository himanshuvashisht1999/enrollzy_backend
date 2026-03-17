@extends('admin.layouts.master')

@section('title', 'Edit Student')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0">
                     <ul class="nav nav-tabs card-header-tabs" id="studentTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#st-identity">1. Identity</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-academic">2. Academic</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-coaching">3. Coaching</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-performance">4. Performance</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-achievements">5. Achievements</a></li>
                         <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-testimonial">6. Testimonial</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-digital">7. Digital</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-privacy">8. Privacy</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#st-admin">9. Admin</a></li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content mt-3">
                        
                        <!-- 1. IDENTITY -->
                        <div class="tab-pane fade show active" id="st-identity">
                            <h5 class="mb-3 text-primary">Basic Identity</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" value="{{ old('full_name', $student->full_name) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        @foreach(['Male', 'Female', 'Other'] as $gender)
                                            <option value="{{ $gender }}" {{ $student->gender == $gender ? 'selected' : '' }}>{{ $gender }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Year of Birth</label>
                                    <input type="number" class="form-control" name="year_of_birth" value="{{ $student->year_of_birth }}" placeholder="YYYY">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" value="{{ $student->city }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state" value="{{ $student->state }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Profile Photo</label>
                                    @if($student->profile_photo_url)
                                        <div class="mb-1">
                                            <img src="{{ asset($student->profile_photo_url) }}" width="50" class="rounded">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" name="img">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Short Intro</label>
                                    <textarea class="form-control" name="short_intro" rows="2">{{ $student->short_intro }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. ACADEMIC -->
                        <div class="tab-pane fade" id="st-academic">
                             <h5 class="mb-3 text-primary">Academic Background</h5>
                             <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Current Class</label>
                                    <input type="text" class="form-control" name="current_class" value="{{ $student->current_class }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">School Name</label>
                                    <input type="text" class="form-control" name="school_name" value="{{ $student->school_name }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Board</label>
                                    <select name="board" class="form-select">
                                        <option value="">Select</option>
                                        @foreach(['CBSE', 'ICSE', 'State Board', 'IB'] as $board)
                                            <option value="{{ $board }}" {{ $student->board == $board ? 'selected' : '' }}>{{ $board }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Prev. Year %</label>
                                    <input type="number" step="0.01" class="form-control" name="previous_year_percentage" value="{{ $student->previous_year_percentage }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Stream</label>
                                    <select name="stream" class="form-select">
                                        <option value="">Select</option>
                                        @foreach(['PCB', 'PCM', 'Commerce', 'Arts'] as $st)
                                            <option value="{{ $st }}" {{ $student->stream == $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Target Exam</label>
                                    <select name="competitive_exam_target" class="form-select">
                                        <option value="">Select</option>
                                        @foreach(['NEET', 'IIT-JEE', 'UPSC', 'NDA'] as $exam)
                                            <option value="{{ $exam }}" {{ $student->competitive_exam_target == $exam ? 'selected' : '' }}>{{ $exam }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                 <div class="col-md-3">
                                    <label class="form-label">Attempt Type</label>
                                    <select name="attempt_type" class="form-select">
                                        @foreach(['Fresher', 'Dropper'] as $type)
                                            <option value="{{ $type }}" {{ $student->attempt_type == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 3. COACHING -->
                        <div class="tab-pane fade" id="st-coaching">
                             <h5 class="mb-3 text-primary">Institute Association</h5>
                             <div class="row g-3">
                                 <div class="col-md-6">
                                     <label class="form-label">Institute (Organisation)</label>
                                     <select name="organisation_id" class="form-select select2">
                                         <option value="">Select Organisation</option>
                                         @foreach($organisations as $id => $name)
                                             <option value="{{ $id }}" {{ $student->organisation_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="col-md-6">
                                     <label class="form-label">Course Enrolled</label>
                                     <input type="text" class="form-control" name="course_enrolled" value="{{ $student->course_enrolled }}">
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">Batch Type</label>
                                     <select name="batch_type" class="form-select">
                                         @foreach(['Regular', 'Topper', 'Repeater'] as $batch)
                                             <option value="{{ $batch }}" {{ $student->batch_type == $batch ? 'selected' : '' }}>{{ $batch }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">Mode of Study</label>
                                      <select name="mode_of_study" class="form-select">
                                         @foreach(['Offline', 'Online', 'Hybrid'] as $mode)
                                             <option value="{{ $mode }}" {{ $student->mode_of_study == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">Admission Through</label>
                                     <select name="admission_through" class="form-select">
                                         @foreach(['Direct', 'Scholarship Test'] as $adm)
                                             <option value="{{ $adm }}" {{ $student->admission_through == $adm ? 'selected' : '' }}>{{ $adm }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                        </div>

                        <!-- 4. PERFORMANCE -->
                        <div class="tab-pane fade" id="st-performance">
                            <h5 class="mb-3 text-primary">Performance</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Avg. Test Score (%)</label>
                                    <input type="number" step="0.1" class="form-control" name="average_test_score" value="{{ $student->average_test_score }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Attendance %</label>
                                    <input type="number" step="0.1" class="form-control" name="attendance_percentage" value="{{ $student->attendance_percentage }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Improvement Ind.</label>
                                    <input type="text" class="form-control" name="academic_improvement_indicator" value="{{ $student->academic_improvement_indicator }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Test Scores Summary</label>
                                    <textarea class="form-control" name="test_scores_summary" rows="2">{{ $student->test_scores_summary }}</textarea>
                                </div>
                                <div class="col-md-6">
                                     <label class="form-label">Strengths</label>
                                     <select class="form-control select2-tags" name="strengths[]" multiple>
                                         @if($student->strengths)
                                             @foreach($student->strengths as $str)
                                                 <option value="{{ $str }}" selected>{{ $str }}</option>
                                             @endforeach
                                         @endif
                                     </select>
                                </div>
                                <div class="col-md-6">
                                     <label class="form-label">Weak Areas</label>
                                     <select class="form-control select2-tags" name="weak_areas[]" multiple>
                                         @if($student->weak_areas)
                                             @foreach($student->weak_areas as $wa)
                                                 <option value="{{ $wa }}" selected>{{ $wa }}</option>
                                             @endforeach
                                         @endif
                                     </select>
                                </div>
                            </div>
                        </div>

                         <!-- 5. ACHIEVEMENTS -->
                         <div class="tab-pane fade" id="st-achievements">
                             <h5 class="mb-3 text-primary">Achievements & Results</h5>
                             <div class="row g-3">
                                 <div class="col-md-4">
                                     <label class="form-label">Exam Attempted</label>
                                     <input type="text" class="form-control" name="exam_attempted" value="{{ $student->exam_attempted }}">
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">Exam Year</label>
                                     <input type="number" class="form-control" name="exam_year" value="{{ $student->exam_year }}">
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">Selection Status</label>
                                     <select name="selection_status" class="form-select">
                                         <option value="">Select</option>
                                         @foreach(['Selected', 'Not Selected', 'Awaiting'] as $status)
                                             <option value="{{ $status }}" {{ $student->selection_status == $status ? 'selected' : '' }}>{{ $status }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">Score/Percentile</label>
                                     <input type="text" class="form-control" name="exam_score" value="{{ $student->exam_score }}">
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">Rank (AIR)</label>
                                     <input type="text" class="form-control" name="exam_rank" value="{{ $student->exam_rank }}">
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label">College Allotted</label>
                                     <input type="text" class="form-control" name="college_allotted" value="{{ $student->college_allotted }}">
                                 </div>
                                 <div class="col-md-12">
                                     <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="result_verified" value="1" {{ $student->result_verified ? 'checked' : '' }}>
                                        <label class="form-check-label">Result Verified?</label>
                                    </div>
                                 </div>
                             </div>
                         </div>
                         
                          <!-- 6. TESTIMONIAL -->
                         <div class="tab-pane fade" id="st-testimonial">
                            <h5 class="mb-3 text-primary">Testimonial & Experience</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Institute Rating (0-5)</label>
                                    <input type="number" step="0.1" max="5" class="form-control" name="rating_for_institute" value="{{ $student->rating_for_institute }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Faculty Rating (0-5)</label>
                                    <input type="number" step="0.1" max="5" class="form-control" name="rating_for_faculty" value="{{ $student->rating_for_faculty }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prep Duration (Months)</label>
                                    <input type="number" class="form-control" name="preparation_duration_months" value="{{ $student->preparation_duration_months }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Study Hours/Day</label>
                                    <input type="number" step="0.5" class="form-control" name="study_hours_per_day" value="{{ $student->study_hours_per_day }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Student Testimonial</label>
                                    <textarea class="form-control" name="student_testimonial" rows="4">{{ $student->student_testimonial }}</textarea>
                                </div>
                                <div class="col-md-12">
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="would_recommend" value="1" {{ $student->would_recommend ? 'checked' : '' }}>
                                        <label class="form-check-label">Would Recommend?</label>
                                    </div>
                                </div>
                            </div>
                         </div>
                         
                         <!-- 7. DIGITAL -->
                         <div class="tab-pane fade" id="st-digital">
                             <h5 class="mb-3 text-primary">Digital & Community</h5>
                              <div class="row g-3">
                                 <div class="col-md-12">
                                     <label class="form-label">Study Groups Joined</label>
                                     <select class="form-control select2-tags" name="study_groups_joined[]" multiple>
                                         @if($student->study_groups_joined)
                                             @foreach($student->study_groups_joined as $grp)
                                                 <option value="{{ $grp }}" selected>{{ $grp }}</option>
                                             @endforeach
                                         @endif
                                     </select>
                                 </div>
                                 <div class="col-md-6">
                                     <label class="form-label">Mentor Assigned</label>
                                     <input type="text" class="form-control" name="mentor_assigned" value="{{ $student->mentor_assigned }}">
                                 </div>
                                  <div class="col-md-6">
                                     <label class="form-label">Doubt Sessions Attended</label>
                                     <input type="number" class="form-control" name="doubt_sessions_attended" value="{{ $student->doubt_sessions_attended }}">
                                 </div>
                                 <div class="col-md-12">
                                      <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="discussion_forum_participation" value="1" {{ $student->discussion_forum_participation ? 'checked' : '' }}>
                                        <label class="form-check-label">Forum Participation?</label>
                                    </div>
                                 </div>
                             </div>
                         </div>
                         
                         <!-- 8. PRIVACY -->
                        <div class="tab-pane fade" id="st-privacy">
                            <h5 class="mb-3 text-primary">Privacy Controls</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Profile Visibility</label>
                                     <select name="profile_visibility" class="form-select">
                                         @foreach(['Private', 'Public', 'Limited'] as $vis)
                                             <option value="{{ $vis }}" {{ $student->profile_visibility == $vis ? 'selected' : '' }}>{{ $vis }}</option>
                                         @endforeach
                                     </select>
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Fields Visible to Public</label>
                                     <select class="form-control select2-tags" name="fields_visible_public[]" multiple>
                                          @if($student->fields_visible_public)
                                             @foreach($student->fields_visible_public as $fvp)
                                                 <option value="{{ $fvp }}" selected>{{ $fvp }}</option>
                                             @endforeach
                                         @endif
                                         <option value="full_name">Full Name</option>
                                         <option value="test_scores">Test Scores</option>
                                         <option value="achievements">Achievements</option>
                                         <option value="testimonial">Testimonial</option>
                                     </select>
                                </div>
                                <div class="col-md-4">
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="contact_visible" value="1" {{ $student->contact_visible ? 'checked' : '' }}>
                                        <label class="form-check-label">Contact Visible</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="testimonial_visible" value="1" {{ $student->testimonial_visible ? 'checked' : '' }}>
                                        <label class="form-check-label">Testimonial Visible</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="consent_for_data_use" value="1" {{ $student->consent_for_data_use ? 'checked' : '' }}>
                                        <label class="form-check-label">Data Consent Given</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="profile_indexing_allowed" value="1" {{ $student->profile_indexing_allowed ? 'checked' : '' }}>
                                        <label class="form-check-label">Allow SEO Indexing</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 9. ADMIN -->
                        <div class="tab-pane fade" id="st-admin">
                             <h5 class="mb-3 text-primary">Admin System</h5>
                             <div class="row g-3">
                                 <div class="col-md-6">
                                     <label class="form-label">Verification Status</label>
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="profile_verified" value="1" {{ $student->profile_verified ? 'checked' : '' }}>
                                        <label class="form-check-label">Profile Verified</label>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                     <label class="form-label">Status</label>
                                     <select name="status" class="form-select">
                                         @foreach(['Active', 'Inactive', 'Archived'] as $s)
                                             <option value="{{ $s }}" {{ $student->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="col-md-6">
                                     <label class="form-label">Data Source</label>
                                     <input type="text" class="form-control" name="data_source" value="{{ $student->data_source }}">
                                 </div>
                                 <div class="col-md-6">
                                     <label class="form-label">Confidence Score</label>
                                     <input type="number" step="0.1" class="form-control" name="confidence_score" value="{{ $student->confidence_score }}">
                                 </div>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white text-end py-3">
                    <a href="{{ route('students.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Update Student</button>
                </div>
            </div>
             @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single, .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #dee2e6;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.select2-tags').select2({
            tags: true,
            tokenSeparators: [','],
            placeholder: "Type and enter to add",
            width: '100%'
        });
    });
</script>
@endpush
