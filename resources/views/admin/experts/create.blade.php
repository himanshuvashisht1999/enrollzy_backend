@extends('admin.layouts.master')

@section('title', 'Add New Expert')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <form action="{{ route('experts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
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
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Role (Category) <span class="text-danger">*</span></label>
                                    <select name="expert_category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        @foreach(\App\Models\ExpertCategory::where('is_active', true)->get() as $cat)
                                            <option value="{{ $cat->id }}" {{ old('expert_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                 <div class="col-md-4">
                                    <label class="form-label">Designation</label>
                                    <select name="designation" class="form-select">
                                        <option value="">Select Designation</option>
                                        <option value="Senior Faculty">Senior Faculty</option>
                                        <option value="Faculty">Faculty</option>
                                        <option value="HOD">HOD</option>
                                        <option value="Mentor">Mentor</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender (Optional)</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date of Birth (Private)</label>
                                    <input type="date" class="form-control" name="date_of_birth">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Profile Photo <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="img" required>
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Subject Specialization (Select Multiple)</label>
                                     <select class="form-control select2-tags" name="subject_specialization[]" multiple>
                                         <option value="Physics">Physics</option>
                                         <option value="Chemistry">Chemistry</option>
                                         <option value="Biology">Biology</option>
                                         <option value="Maths">Maths</option>
                                         <option value="English">English</option>
                                     </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Short Bio (150-200 chars)</label>
                                    <textarea class="form-control" name="short_bio" rows="2" maxlength="255"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Detailed Bio</label>
                                    <textarea class="form-control" name="detailed_bio" rows="4"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. PROFESSIONAL CREDENTIALS -->
                        <div class="tab-pane fade" id="ex-credentials">
                            <h5 class="mb-3 text-primary">Professional Credentials</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Highest Qualification</label>
                                    <input type="text" class="form-control" name="highest_qualification" placeholder="e.g. PhD in Physics">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Total Experience (Years)</label>
                                    <input type="number" step="0.1" class="form-control" name="years_of_experience_total">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Exp. Current Institute (Years)</label>
                                    <input type="number" step="0.1" class="form-control" name="years_of_experience_current_institute">
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Other Qualifications (Add Multiple)</label>
                                     <div id="qualifications-container">
                                         <!-- Dynamic Rows -->
                                     </div>
                                     <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-qualification-btn">
                                         <i class="fas fa-plus me-1"></i> Add Qualification
                                     </button>
                                </div>

                                <!-- Template for Qualification Row -->
                                <template id="qualification-row-template">
                                    <div class="row g-2 mb-2 qualification-row align-items-end">
                                        <div class="col-md-4">
                                            <input type="text" name="other_qualifications[INDEX][name]" class="form-control form-control-sm" placeholder="Degree/Course (e.g. MBA)">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="other_qualifications[INDEX][institute]" class="form-control form-control-sm" placeholder="Institute/University">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="other_qualifications[INDEX][year]" class="form-control form-control-sm" placeholder="Year">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-danger remove-qualification-btn" title="Remove">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <div class="col-md-12">
                                     <label class="form-label">Certifications (Tag)</label>
                                     <select class="form-control select2-tags" name="certifications[]" multiple></select>
                                </div>
                                 <div class="col-md-12">
                                     <label class="form-label">Exams Cleared by Students (Tag)</label>
                                     <select class="form-control select2-tags" name="exams_cleared[]" multiple>
                                        <option value="IIT-JEE">IIT-JEE</option>
                                        <option value="NEET">NEET</option>
                                        <option value="GATE">GATE</option>
                                        <option value="NDA">NDA</option>
                                     </select>
                                </div>
                                 <div class="col-md-12">
                                     <label class="form-label">Notable Achievements (Array)</label>
                                     <select class="form-control select2-tags" name="notable_achievements[]" multiple></select>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="industry_experience" value="1">
                                        <label class="form-check-label">Has Industry Experience?</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. INSTITUTE ASSOCIATION -->
                        <div class="tab-pane fade" id="ex-institute">
                            <h5 class="mb-3 text-primary">Institute Association</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Institute Name</label>
                                    <input type="text" class="form-control" name="current_institute_name">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Faculty Type</label>
                                    <select name="faculty_type" class="form-select">
                                        <option value="Full-time">Full-time</option>
                                        <option value="Part-time">Part-time</option>
                                        <option value="Visiting">Visiting</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Joining Year</label>
                                    <input type="number" class="form-control" name="joining_year" placeholder="YYYY">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Courses Taught (Array)</label>
                                    <select class="form-control select2-tags" name="courses_taught[]" multiple></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Target Batches (Select)</label>
                                    <select class="form-control select2-tags" name="target_batches[]" multiple>
                                        <option value="Class 11">Class 11</option>
                                        <option value="Class 12">Class 12</option>
                                        <option value="Dropper">Dropper</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Avg. Batch Size Handled</label>
                                    <input type="number" class="form-control" name="average_batch_size_handled">
                                </div>
                            </div>
                        </div>

                         <!-- 4. TEACHING PROFILE -->
                         <div class="tab-pane fade" id="ex-teaching">
                            <h5 class="mb-3 text-primary">Teaching Profile</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Teaching Style</label>
                                    <select name="teaching_style" class="form-select">
                                        <option value="Conceptual">Conceptual</option>
                                        <option value="Exam-oriented">Exam-oriented</option>
                                        <option value="Application-based">Application-based</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Language of Teaching</label>
                                     <select class="form-control select2-tags" name="language_of_teaching[]" multiple>
                                        <option value="English">English</option>
                                        <option value="Hindi">Hindi</option>
                                        <option value="Hinglish">Hinglish</option>
                                     </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lecture Mode</label>
                                     <select name="lecture_mode" class="form-select">
                                        <option value="Offline">Offline</option>
                                        <option value="Online">Online</option>
                                        <option value="Hybrid">Hybrid</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Weekly Classes Count</label>
                                    <input type="number" class="form-control" name="weekly_classes_count">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-2 border rounded">
                                        <input class="form-check-input" type="checkbox" name="doubt_solving_sessions" value="1">
                                        <label class="form-check-label px-2">Doubt Solving Sessions</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-2 border rounded">
                                        <input class="form-check-input" type="checkbox" name="one_to_one_mentoring" value="1">
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
                                    <input type="number" class="form-control" name="students_selected_count">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Best Result Year</label>
                                    <input type="number" class="form-control" name="best_result_year" placeholder="YYYY">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Result Verification Source</label>
                                    <input type="text" class="form-control" name="result_verification_source">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Top Rank Students (JSON Input)</label>
                                    <textarea class="form-control" name="top_rank_students" rows="3" placeholder='[{"name": "Student A", "rank": "AIR 1"}, ...]'></textarea>
                                    <small class="text-muted">Enter valid JSON or leave empty.</small>
                                </div>
                            </div>
                        </div>

                        <!-- 6. CONTENT -->
                        <div class="tab-pane fade" id="ex-content">
                            <h5 class="mb-3 text-primary">Content & Digital Presence</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Intro Video URL</label>
                                    <input type="url" class="form-control" name="intro_video_url">
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Demo Lecture Videos (URL Array)</label>
                                     <select class="form-control select2-tags" name="demo_lecture_videos[]" multiple></select>
                                </div>
                                <div class="col-md-12">
                                     <label class="form-label">Articles Written (URL Array)</label>
                                     <select class="form-control select2-tags" name="articles_written[]" multiple></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">YouTube Channel URL</label>
                                    <input type="url" class="form-control" name="youtube_channel_url">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">LinkedIn URL</label>
                                    <input type="url" class="form-control" name="linkedin_profile_url">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Instagram URL</label>
                                    <input type="url" class="form-control" name="instagram_profile_url">
                                </div>
                                 <div class="col-md-4">
                                    <label class="form-label">Telegram URL</label>
                                    <input type="url" class="form-control" name="telegram_channel_url">
                                </div>
                            </div>
                        </div>

                         <!-- 7. RATINGS -->
                         <div class="tab-pane fade" id="ex-ratings">
                            <h5 class="mb-3 text-primary">Ratings, Reviews & Trust</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Average Rating</label>
                                    <input type="number" step="0.1" class="form-control" name="rating" value="5">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Total Reviews</label>
                                    <input type="number" class="form-control" name="total_reviews" value="0">
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="verified_student_reviews_only" value="1">
                                        <label class="form-check-label">Verified Student Reviews Only</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 8. CONTACT -->
                        <div class="tab-pane fade" id="ex-contact">
                             <h5 class="mb-3 text-primary">Contact & Visibility</h5>
                              <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number (Private)</label>
                                    <input type="text" class="form-control" name="contact_number">
                                </div>
                                 <div class="col-md-6">
                                    <label class="form-label">Profile Visibility</label>
                                    <select name="profile_visibility" class="form-select">
                                        <option value="Public">Public</option>
                                        <option value="Institute-only">Institute-only</option>
                                        <option value="Private">Private</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="public_contact_allowed" value="1">
                                        <label class="form-check-label">Allow Public Contact</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="profile_claimed" value="1">
                                        <label class="form-check-label">Profile Claimed</label>
                                    </div>
                                </div>

                                <div class="col-12"><hr></div>
                                <h6 class="text-secondary">SEO META</h6>
                                <div class="col-md-6">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" name="meta_title">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Canonical URL</label>
                                    <input type="text" class="form-control" name="canonical_url">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea class="form-control" name="meta_description" rows="2"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Focus Keywords (Tag)</label>
                                    <select class="form-control select2-tags" name="focus_keywords[]" multiple></select>
                                </div>
                             </div>
                        </div>

                         <!-- 9. ADMIN -->
                         <div class="tab-pane fade" id="ex-admin">
                             <h5 class="mb-3 text-primary">Admin & System</h5>
                             <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Data Source</label>
                                    <input type="text" class="form-control" name="data_source" value="Manual">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confidence Score</label>
                                    <input type="number" step="0.1" class="form-control" name="confidence_score">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Verification Status</label>
                                    <select name="verification_status" class="form-select">
                                        <option value="Pending">Pending</option>
                                        <option value="Verified">Verified</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Archived">Archived</option>
                                    </select>
                                </div>
                             </div>
                         </div>
                    </div>
                </div>

                <div class="card-footer bg-white text-end py-3">
                    <a href="{{ route('experts.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Create Profile</button>
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
        $('.select2-tags').select2({
            tags: true,
            tokenSeparators: [','],
            placeholder: "Type and enter to add",
            width: '100%'
        });

        // Qualification Repeater Logic
        let qualIndex = 0;
        const container = $('#qualifications-container');
        const template = document.getElementById('qualification-row-template').innerHTML;

        $('#add-qualification-btn').click(function() {
            let rowHtml = template.replace(/INDEX/g, qualIndex++);
            container.append(rowHtml);
        });

        $(document).on('click', '.remove-qualification-btn', function() {
            $(this).closest('.qualification-row').remove();
        });
        
        // Add one empty row by default
        $('#add-qualification-btn').trigger('click');
    });
</script>
@endpush
