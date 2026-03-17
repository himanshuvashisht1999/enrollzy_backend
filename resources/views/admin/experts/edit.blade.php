@extends('admin.layouts.master')

@section('title', 'Edit Expert Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <form action="{{ route('experts.update', $expert->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0">
                     <ul class="nav nav-tabs card-header-tabs" id="expertTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#ex-basic">1. Identity</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-credentials">2. Credentials</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-institute">3. Institute</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-teaching">4. Teaching</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-performance">5. Performance</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-content">6. Content</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-ratings">7. Ratings</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-contact">8. Contact & SEO</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-admin">9. Admin</a></li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content mt-3">
                        
                        <!-- 1. BASIC IDENTITY -->
                        <div class="tab-pane fade show active" id="ex-basic">
                            <h5 class="mb-3 text-primary">Basic Identity</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $expert->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email', $expert->email) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Role (Category) <span class="text-danger">*</span></label>
                                    <select name="expert_category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        @foreach(\App\Models\ExpertCategory::where('is_active', true)->get() as $cat)
                                            <option value="{{ $cat->id }}" {{ (old('expert_category_id') ?? $expert->expert_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                 <div class="col-md-4">
                                    <label class="form-label">Designation</label>
                                    <select name="designation" class="form-select">
                                        <option value="">Select Designation</option>
                                        @foreach(['Senior Faculty', 'Faculty', 'HOD', 'Mentor'] as $desig)
                                            <option value="{{ $desig }}" {{ $expert->designation == $desig ? 'selected' : '' }}>{{ $desig }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        @foreach(['Male', 'Female', 'Other'] as $gender)
                                            <option value="{{ $gender }}" {{ $expert->gender == $gender ? 'selected' : '' }}>{{ $gender }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password (Leave empty to keep current)</label>
                                    <input type="password" class="form-control" name="password">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $expert->date_of_birth?->format('Y-m-d')) }}" placeholder="YYYY-MM-DD">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Profile Photo</label>
                                    @if($expert->img)
                                        <div class="mb-2">
                                            <br><img src="{{ str_starts_with($expert->img, 'http') ? $expert->img : asset($expert->img) }}" width="60" class="rounded">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" name="img">
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Subject Specialization</label>
                                     <select class="form-control select2-tags" name="subject_specialization[]" multiple>
                                         @if($expert->subject_specialization)
                                             @foreach($expert->subject_specialization as $spec)
                                                 <option value="{{ $spec }}" selected>{{ $spec }}</option>
                                             @endforeach
                                         @endif
                                         <option value="Physics">Physics</option>
                                         <option value="Chemistry">Chemistry</option>
                                         <option value="Biology">Biology</option>
                                         <option value="Maths">Maths</option>
                                     </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Short Bio</label>
                                    <textarea class="form-control" name="short_bio" rows="2" maxlength="255">{{ old('short_bio', $expert->short_bio) }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Detailed Bio</label>
                                    <textarea class="form-control" name="detailed_bio" rows="4">{{ old('detailed_bio', $expert->detailed_bio) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. CREDENTIALS -->
                        <div class="tab-pane fade" id="ex-credentials">
                            <h5 class="mb-3 text-primary">Professional Credentials</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Highest Qualification</label>
                                    <input type="text" class="form-control" name="highest_qualification" value="{{ $expert->highest_qualification }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Total Exp</label>
                                    <input type="number" step="0.1" class="form-control" name="years_of_experience_total" value="{{ $expert->years_of_experience_total }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Current Inst. Exp</label>
                                    <input type="number" step="0.1" class="form-control" name="years_of_experience_current_institute" value="{{ $expert->years_of_experience_current_institute }}">
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Other Qualifications</label>
                                     <div id="qualifications-container">
                                         @if($expert->other_qualifications && is_array($expert->other_qualifications))
                                            @foreach($expert->other_qualifications as $index => $qual)
                                                @php
                                                    // Handle Legacy String format vs New Object format
                                                    $name = is_array($qual) ? ($qual['name'] ?? '') : $qual;
                                                    $institute = is_array($qual) ? ($qual['institute'] ?? '') : '';
                                                    $year = is_array($qual) ? ($qual['year'] ?? '') : '';
                                                @endphp
                                                <div class="row g-2 mb-2 qualification-row align-items-end">
                                                    <div class="col-md-4">
                                                        <input type="text" name="other_qualifications[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $name }}" placeholder="Degree">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" name="other_qualifications[{{ $index }}][institute]" class="form-control form-control-sm" value="{{ $institute }}" placeholder="Institute">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" name="other_qualifications[{{ $index }}][year]" class="form-control form-control-sm" value="{{ $year }}" placeholder="Year">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-sm btn-danger remove-qualification-btn"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                         @endif
                                     </div>
                                     <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-qualification-btn">
                                         <i class="fas fa-plus me-1"></i> Add Qualification
                                     </button>

                                    <!-- Template -->
                                    <template id="qualification-row-template">
                                        <div class="row g-2 mb-2 qualification-row align-items-end">
                                            <div class="col-md-4">
                                                <input type="text" name="other_qualifications[INDEX][name]" class="form-control form-control-sm" placeholder="Degree">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="other_qualifications[INDEX][institute]" class="form-control form-control-sm" placeholder="Institute">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" name="other_qualifications[INDEX][year]" class="form-control form-control-sm" placeholder="Year">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-sm btn-danger remove-qualification-btn"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Certifications</label>
                                     <select class="form-control select2-tags" name="certifications[]" multiple>
                                         @if($expert->certifications)
                                             @foreach($expert->certifications as $cert)
                                                 <option value="{{ $cert }}" selected>{{ $cert }}</option>
                                             @endforeach
                                         @endif
                                     </select>
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Exams Cleared by Students</label>
                                     <select class="form-control select2-tags" name="exams_cleared[]" multiple>
                                         @if($expert->exams_cleared)
                                             @foreach($expert->exams_cleared as $ex)
                                                 <option value="{{ $ex }}" selected>{{ $ex }}</option>
                                             @endforeach
                                         @endif
                                        <option value="IIT-JEE">IIT-JEE</option>
                                        <option value="NEET">NEET</option>
                                     </select>
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Notable Achievements</label>
                                     <select class="form-control select2-tags" name="notable_achievements[]" multiple>
                                         @if($expert->notable_achievements)
                                             @foreach($expert->notable_achievements as $ach)
                                                 <option value="{{ $ach }}" selected>{{ $ach }}</option>
                                             @endforeach
                                         @endif
                                     </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="industry_experience" value="1" {{ $expert->industry_experience ? 'checked' : '' }}>
                                        <label class="form-check-label">Has Industry Experience?</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. INSTITUTE -->
                         <div class="tab-pane fade" id="ex-institute">
                            <h5 class="mb-3 text-primary">Institute Association</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Institute Name</label>
                                    <input type="text" class="form-control" name="current_institute_name" value="{{ $expert->current_institute_name }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Faculty Type</label>
                                    <select name="faculty_type" class="form-select">
                                        @foreach(['Full-time', 'Part-time', 'Visiting'] as $ft)
                                            <option value="{{ $ft }}" {{ $expert->faculty_type == $ft ? 'selected' : '' }}>{{ $ft }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Joining Year</label>
                                    <input type="number" class="form-control" name="joining_year" value="{{ $expert->joining_year }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Courses Taught</label>
                                    <select class="form-control select2-tags" name="courses_taught[]" multiple>
                                         @if($expert->courses_taught)
                                             @foreach($expert->courses_taught as $ct)
                                                 <option value="{{ $ct }}" selected>{{ $ct }}</option>
                                             @endforeach
                                         @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Target Batches</label>
                                    <select class="form-control select2-tags" name="target_batches[]" multiple>
                                         @if($expert->target_batches)
                                             @foreach($expert->target_batches as $tb)
                                                 <option value="{{ $tb }}" selected>{{ $tb }}</option>
                                             @endforeach
                                         @endif
                                        <option value="Class 11">Class 11</option>
                                        <option value="Class 12">Class 12</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Avg. Batch Size</label>
                                    <input type="number" class="form-control" name="average_batch_size_handled" value="{{ $expert->average_batch_size_handled }}">
                                </div>
                             </div>
                         </div>

                        <!-- 4. TEACHING -->
                        <div class="tab-pane fade" id="ex-teaching">
                            <h5 class="mb-3 text-primary">Teaching Profile</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Teaching Style</label>
                                    <select name="teaching_style" class="form-select">
                                        <option value="">Select</option>
                                        @foreach(['Conceptual', 'Exam-oriented', 'Application-based'] as $style)
                                            <option value="{{ $style }}" {{ $expert->teaching_style == $style ? 'selected' : '' }}>{{ $style }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Language</label>
                                     <select class="form-control select2-tags" name="language_of_teaching[]" multiple>
                                         @if($expert->language_of_teaching)
                                             @foreach($expert->language_of_teaching as $lang)
                                                 <option value="{{ $lang }}" selected>{{ $lang }}</option>
                                             @endforeach
                                         @endif
                                        <option value="English">English</option>
                                        <option value="Hindi">Hindi</option>
                                     </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lecture Mode</label>
                                     <select name="lecture_mode" class="form-select">
                                        @foreach(['Offline', 'Online', 'Hybrid'] as $mode)
                                            <option value="{{ $mode }}" {{ $expert->lecture_mode == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Weekly Classes</label>
                                    <input type="number" class="form-control" name="weekly_classes_count" value="{{ $expert->weekly_classes_count }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-2 border rounded">
                                        <input class="form-check-input" type="checkbox" name="doubt_solving_sessions" value="1" {{ $expert->doubt_solving_sessions ? 'checked' : '' }}>
                                        <label class="form-check-label px-2">Doubt Solving Sessions</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-2 border rounded">
                                        <input class="form-check-input" type="checkbox" name="one_to_one_mentoring" value="1" {{ $expert->one_to_one_mentoring ? 'checked' : '' }}>
                                        <label class="form-check-label px-2">One-to-One Mentoring</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. PERFORMANCE -->
                        <div class="tab-pane fade" id="ex-performance">
                            <h5 class="mb-3 text-primary">Performance & Impact</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Students Selected Count</label>
                                    <input type="number" class="form-control" name="students_selected_count" value="{{ $expert->students_selected_count }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Best Result Year</label>
                                    <input type="number" class="form-control" name="best_result_year" value="{{ $expert->best_result_year }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Verification Source</label>
                                    <input type="text" class="form-control" name="result_verification_source" value="{{ $expert->result_verification_source }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Top Rank Students (JSON)</label>
                                    <textarea class="form-control" name="top_rank_students" rows="3">{{ is_array($expert->top_rank_students) ? json_encode($expert->top_rank_students) : $expert->top_rank_students }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 6. CONTENT -->
                        <div class="tab-pane fade" id="ex-content">
                            <h5 class="mb-3 text-primary">Content & Digital</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Intro Video URL</label>
                                    <input type="url" class="form-control" name="intro_video_url" value="{{ $expert->intro_video_url }}">
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Demo Lecture Videos (URL Array)</label>
                                     <select class="form-control select2-tags" name="demo_lecture_videos[]" multiple>
                                         @if($expert->demo_lecture_videos)
                                             @foreach($expert->demo_lecture_videos as $vid)
                                                 <option value="{{ $vid }}" selected>{{ $vid }}</option>
                                             @endforeach
                                         @endif
                                     </select>
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Articles Written (URL Array)</label>
                                     <select class="form-control select2-tags" name="articles_written[]" multiple>
                                          @if($expert->articles_written)
                                             @foreach($expert->articles_written as $art)
                                                 <option value="{{ $art }}" selected>{{ $art }}</option>
                                             @endforeach
                                         @endif
                                     </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">YouTube URL</label>
                                    <input type="url" class="form-control" name="youtube_channel_url" value="{{ $expert->youtube_channel_url }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">LinkedIn</label>
                                    <input type="url" class="form-control" name="linkedin_profile_url" value="{{ $expert->linkedin_profile_url }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Instagram</label>
                                    <input type="url" class="form-control" name="instagram_profile_url" value="{{ $expert->instagram_profile_url }}">
                                </div>
                                 <div class="col-md-4">
                                    <label class="form-label">Telegram</label>
                                    <input type="url" class="form-control" name="telegram_channel_url" value="{{ $expert->telegram_channel_url }}">
                                </div>
                            </div>
                        </div>
                        
                        <!-- 7. RATINGS -->
                        <div class="tab-pane fade" id="ex-ratings">
                            <h5 class="mb-3 text-primary">Ratings & Reviews</h5>
                             <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Average Rating</label>
                                    <input type="number" step="0.1" class="form-control" name="rating" value="{{ $expert->rating }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Total Reviews</label>
                                    <input type="number" class="form-control" name="total_reviews" value="{{ $expert->total_reviews }}">
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="verified_student_reviews_only" value="1" {{ $expert->verified_student_reviews_only ? 'checked' : '' }}>
                                        <label class="form-check-label">Verified Student Reviews Only</label>
                                    </div>
                                </div>
                             </div>
                        </div>
                        
                        <!-- 8. CONTACT -->
                         <div class="tab-pane fade" id="ex-contact">
                             <h5 class="mb-3 text-primary">Contact & SEO</h5>
                             <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Phone (Private)</label>
                                    <input type="text" class="form-control" name="contact_number" value="{{ $expert->contact_number }}">
                                </div>
                                 <div class="col-md-6">
                                    <label class="form-label">Visibility</label>
                                    <select name="profile_visibility" class="form-select">
                                        @foreach(['Public', 'Institute-only', 'Private'] as $vis)
                                            <option value="{{ $vis }}" {{ $expert->profile_visibility == $vis ? 'selected' : '' }}>{{ $vis }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="public_contact_allowed" value="1" {{ $expert->public_contact_allowed ? 'checked' : '' }}>
                                        <label class="form-check-label">Allow Public Contact</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="profile_claimed" value="1" {{ $expert->profile_claimed ? 'checked' : '' }}>
                                        <label class="form-check-label">Profile Claimed</label>
                                    </div>
                                </div>

                                <div class="col-12"><hr></div>
                                <div class="col-md-6">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" name="meta_title" value="{{ $expert->meta_title }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Canonical URL</label>
                                    <input type="text" class="form-control" name="canonical_url" value="{{ $expert->canonical_url }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea class="form-control" name="meta_description" rows="2">{{ $expert->meta_description }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Focus Keywords</label>
                                    <select class="form-control select2-tags" name="focus_keywords[]" multiple>
                                         @if($expert->focus_keywords)
                                             @foreach($expert->focus_keywords as $fk)
                                                 <option value="{{ $fk }}" selected>{{ $fk }}</option>
                                             @endforeach
                                         @endif
                                    </select>
                                </div>
                             </div>
                         </div>
                         
                         <!-- 9. ADMIN -->
                         <div class="tab-pane fade" id="ex-admin">
                             <h5 class="mb-3 text-primary">Admin System</h5>
                              <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Data Source</label>
                                    <input type="text" class="form-control" name="data_source" value="{{ $expert->data_source }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confidence Score</label>
                                    <input type="number" step="0.1" class="form-control" name="confidence_score" value="{{ $expert->confidence_score }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Verification</label>
                                    <select name="verification_status" class="form-select">
                                        @foreach(['Pending', 'Verified', 'Rejected'] as $v)
                                            <option value="{{ $v }}" {{ $expert->verification_status == $v ? 'selected' : '' }}>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        @foreach(['Active', 'Inactive', 'Archived'] as $s)
                                            <option value="{{ $s }}" {{ $expert->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>
                             </div>
                            </div>
                        </div>

                        <!-- 10. COMMISSION -->
                        <div class="tab-pane fade" id="ex-commission">
                            <h5 class="mb-3 text-primary">Commission Settings</h5>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info border-0 shadow-sm">
                                        <h6 class="alert-heading fw-bold"><i class="fas fa-info-circle me-2"></i> How Commission Works</h6>
                                        <p class="mb-0 small">
                                            The system resolves commission in this order: <br>
                                            <strong>Booking Override > Expert Specific > Category Specific > Global Default</strong>.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="text-uppercase text-muted small fw-bold">Current Expert Configuration</h6>
                                            @if($activeCommission)
                                                <h3 class="text-primary fw-bold mt-2">
                                                    {{ $activeCommission->commission_type == 'percentage' ? floatval($activeCommission->commission_value) . '%' : '₹' . floatval($activeCommission->commission_value) }}
                                                </h3>
                                                <span class="badge bg-success mb-2">Active Override</span>
                                                <p class="small text-muted mb-0">Reason: {{ $activeCommission->reason ?? 'N/A' }}</p>
                                                <p class="small text-muted">Created: {{ $activeCommission->created_at->format('d M, Y') }}</p>
                                            @else
                                                <h3 class="text-secondary fw-bold mt-2">Default</h3>
                                                <span class="badge bg-secondary mb-2">Using Global/Category Policy</span>
                                                <p class="small text-muted mb-0">No specific override set for this expert.</p>
                                            @endif
                                            
                                            <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#commissionModal">
                                                <i class="fas fa-cog me-1"></i> Configure Commission
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
                 <div class="card-footer bg-white text-end py-3">
                    <a href="{{ route('experts.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Update Profile</button>
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
<!-- Commission Modal -->
<div class="modal fade" id="commissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Configure Expert Commission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('experts.commission.update', $expert->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Define a specific commission rule for this expert. This will <strong>override</strong> Global and Category policies.
                    </p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Commission Type</label>
                        <select name="commission_type" class="form-select">
                            <option value="percentage">Percentage (%)</option>
                            <option value="flat_fee">Flat Fee (₹)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Value</label>
                        <input type="number" step="0.01" name="commission_value" class="form-control" required placeholder="e.g. 10">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason / Note</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Contract 2024">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Commission</button>
                </div>
            </form>
        </div>
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
        $('.select2-tags').select2({
            tags: true,
            tokenSeparators: [','],
            placeholder: "Type and enter to add",
            width: '100%'
        });

        // Qualification Repeater Logic
        let qualIndex = {{ is_array($expert->other_qualifications) ? count($expert->other_qualifications) : 0 }};
        const container = $('#qualifications-container');
        const template = document.getElementById('qualification-row-template').innerHTML;

        $('#add-qualification-btn').click(function() {
            let rowHtml = template.replace(/INDEX/g, qualIndex++);
            container.append(rowHtml);
        });

        $(document).on('click', '.remove-qualification-btn', function() {
            $(this).closest('.qualification-row').remove();
        });
    });
</script>
@endpush
