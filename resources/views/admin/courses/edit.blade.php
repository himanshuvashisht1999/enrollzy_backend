@extends('admin.layouts.master')

@section('title', 'Edit Master Course')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.courses.index') }}" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
    <h3 class="fw-bold mt-2">Edit Master Course</h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Course Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Course Name</label>
                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $course->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Slug (Manual Update)</label>
                            <input type="text"
                                   name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $course->slug) }}"
                                   required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Slug will not automatically change if you update the name.</small>
                        </div>

                        {{-- Program Level --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Program Level</label>
                            <select name="program_level_id" class="form-select select2">
                                <option value="">-- Select Level --</option>
                                @foreach($programLevels as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('program_level_id', $course->program_level_id) == $level->id ? 'selected' : '' }}>
                                        {{ $level->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Stream Offered --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Stream Offered</label>
                            <select name="stream_offered_id" class="form-select select2">
                                <option value="">-- Select Stream --</option>
                                @foreach($streamOffereds as $stream)
                                    <option value="{{ $stream->id }}"
                                        {{ old('stream_offered_id', $course->stream_offered_id) == $stream->id ? 'selected' : '' }}>
                                        {{ $stream->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Discipline --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Discipline</label>
                            <select name="discipline_id" class="form-select select2">
                                <option value="">-- Select Discipline --</option>
                                @foreach($disciplines as $disc)
                                    <option value="{{ $disc->id }}"
                                        {{ old('discipline_id', $course->discipline_id) == $disc->id ? 'selected' : '' }}>
                                        {{ $disc->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Program Modes --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Program Modes</label>
                            <select name="program_types[]" class="form-select select2" multiple="multiple">
                                @if(isset($programTypes))
                                    @foreach($programTypes as $type)
                                        <option value="{{ $type->id }}" {{ in_array($type->id, old('program_types', $course->programTypes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $type->title }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Select one or more program modes.</small>
                        </div>

                        {{-- Duration --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Duration</label>
                            <input type="text"
                                   name="duration"
                                   class="form-control"
                                   value="{{ old('duration', $course->duration) }}"
                                   placeholder="e.g. 2 Years">
                        </div>

                        {{-- Course Full Form --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Course Full Form</label>
                            <input type="text" name="full_form" class="form-control" value="{{ old('full_form', $course->full_form) }}" placeholder="e.g. Master of Business Administration">
                        </div>

                        {{-- Course Type --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Course Type</label>
                            <select name="course_type_id" class="form-select select2">
                                <option value="">-- Select Course Type --</option>
                                @if(isset($courseTypes))
                                    @foreach($courseTypes as $ct)
                                        <option value="{{ $ct->id }}" {{ old('course_type_id', $course->course_type_id) == $ct->id ? 'selected' : '' }}>
                                            {{ $ct->title }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Common Entrance Exams --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Common Entrance Exams</label>
                            <select name="common_entrance_exams[]" class="form-select select2" multiple="multiple">
                                @if(isset($exams))
                                    @php
                                        $selectedExams = old('common_entrance_exams', is_array($course->common_entrance_exams) ? $course->common_entrance_exams : (is_string($course->common_entrance_exams) ? json_decode($course->common_entrance_exams, true) ?? [] : [$course->common_entrance_exams]));
                                        if(!is_array($selectedExams)) $selectedExams = [];
                                    @endphp
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" {{ in_array($exam->id, $selectedExams) ? 'selected' : '' }}>
                                            {{ $exam->name ?? $exam->title }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Common Specializations --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Common Specializations</label>
                            <select name="common_specializations[]" class="form-select select2" multiple="multiple">
                                @if(isset($specializations))
                                    @php
                                        $selectedSpecs = old('common_specializations', is_array($course->common_specializations) ? $course->common_specializations : (is_string($course->common_specializations) ? json_decode($course->common_specializations, true) ?? [] : [$course->common_specializations]));
                                        if(!is_array($selectedSpecs)) $selectedSpecs = [];
                                    @endphp
                                    @foreach($specializations as $spec)
                                        <option value="{{ $spec->id }}" {{ in_array($spec->id, $selectedSpecs) ? 'selected' : '' }}>
                                            {{ $spec->title }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Average Salary Range --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Average Salary Range</label>
                            <input type="text" name="average_salary_range" class="form-control" value="{{ old('average_salary_range', $course->average_salary_range) }}" placeholder="e.g. 5 LPA - 10 LPA">
                        </div>

                        {{-- Related Courses --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Related Courses</label>
                            <select name="related_courses[]" class="form-select select2" multiple="multiple">
                                @if(isset($allCourses))
                                    @php
                                        $selectedRelated = old('related_courses', is_array($course->related_courses) ? $course->related_courses : (is_string($course->related_courses) ? json_decode($course->related_courses, true) ?? [] : [$course->related_courses]));
                                        if(!is_array($selectedRelated)) $selectedRelated = [];
                                    @endphp
                                    @foreach($allCourses as $relCourse)
                                        <option value="{{ $relCourse->id }}" {{ in_array($relCourse->id, $selectedRelated) ? 'selected' : '' }}>
                                            {{ $relCourse->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Select other courses related to this course.</small>
                        </div>

                        {{-- Overview --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Overview</label>
                            <textarea name="overview" class="form-control editor">{{ old('overview', $course->overview) }}</textarea>
                        </div>

                        {{-- Generic Eligibility --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Generic Eligibility</label>
                            <textarea name="generic_eligibility" class="form-control editor">{{ old('generic_eligibility', $course->generic_eligibility) }}</textarea>
                        </div>

                        {{-- Core Curriculum --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Core Curriculum / Subjects</label>
                            <textarea name="core_curriculum" class="form-control editor">{{ old('core_curriculum', $course->core_curriculum) }}</textarea>
                        </div>

                        {{-- Skills Gained --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Skills Gained</label>
                            <textarea name="skills_gained" class="form-control editor">{{ old('skills_gained', $course->skills_gained) }}</textarea>
                        </div>

                        {{-- Career Scope --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Career Scope & Job Roles</label>
                            <textarea name="career_scope" class="form-control editor">{{ old('career_scope', $course->career_scope) }}</textarea>
                        </div>

                        {{-- Higher Education Options --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Higher Education Options</label>
                            <textarea name="higher_education_options" class="form-control editor">{{ old('higher_education_options', $course->higher_education_options) }}</textarea>
                        </div>

                        {{-- Course Comparison --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Course Comparison</label>
                            <textarea name="course_comparison" class="form-control editor">{{ old('course_comparison', $course->course_comparison) }}</textarea>
                        </div>

                        {{-- Pros & Cons --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Pros & Cons / Who Should Take This</label>
                            <textarea name="pros_cons" class="form-control editor">{{ old('pros_cons', $course->pros_cons) }}</textarea>
                        </div>

                        {{-- Course FAQs Section --}}
                        <div class="col-12 mt-4">
                            <div class="card border border-light-subtle shadow-none bg-light bg-opacity-50">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="fas fa-question-circle text-primary me-2"></i>Course FAQs
                                        </h6>
                                        <small class="text-muted">Frequently asked questions and answers for this course.</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-faq-btn">
                                        <i class="fas fa-plus me-1"></i> Add FAQ
                                    </button>
                                </div>
                                <div class="card-body p-3">
                                    @php
                                        $currentFaqs = old('faqs', $course->faqs ?? []);
                                        if (is_string($currentFaqs)) {
                                            $currentFaqs = json_decode($currentFaqs, true) ?? [];
                                        }
                                        if (!is_array($currentFaqs)) {
                                            $currentFaqs = [];
                                        }
                                    @endphp
                                    <div id="course_faqs_container">
                                        @foreach($currentFaqs as $fIndex => $faqItem)
                                            <div class="card mb-2 faq-item-card border border-light-subtle shadow-none rounded-3 bg-white">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                                                        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 faq-number" style="font-size: 0.8rem;">
                                                            <i class="fas fa-question-circle me-1"></i>FAQ #{{ $loop->iteration }}
                                                        </span>
                                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 remove-faq-btn" style="font-size: 0.78rem;" title="Remove this FAQ">
                                                            <i class="fas fa-trash-alt me-1"></i> Remove
                                                        </button>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold text-secondary mb-1">Question <span class="text-danger">*</span></label>
                                                            <input type="text" name="faqs[{{ $fIndex }}][question]" class="form-control form-control-sm" placeholder="e.g. What is the eligibility criteria for this course?" value="{{ $faqItem['question'] ?? '' }}" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold text-secondary mb-1">Answer <span class="text-danger">*</span></label>
                                                            <textarea name="faqs[{{ $fIndex }}][answer]" class="form-control form-control-sm" rows="2" placeholder="Provide a detailed and helpful answer..." required>{{ $faqItem['answer'] ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Add More Button directly below the last FAQ --}}
                                    <div id="add_more_container" class="mt-2 text-start" style="{{ count($currentFaqs) > 0 ? '' : 'display: none;' }}">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-more-faq-btn">
                                            <i class="fas fa-plus me-1"></i> Add More FAQ
                                        </button>
                                    </div>

                                    <div id="no_faqs_message" class="text-center py-4 text-muted border border-dashed rounded-3 bg-white" style="{{ count($currentFaqs) > 0 ? 'display: none;' : '' }}">
                                        <i class="fas fa-comments fa-2x mb-2 text-muted opacity-50 d-block"></i>
                                        <p class="mb-2 small">No FAQs added yet for this course.</p>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="empty-add-faq-btn">
                                            <i class="fas fa-plus me-1"></i> Add First FAQ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sort Order --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number"
                                   name="sort_order"
                                   class="form-control"
                                   value="{{ old('sort_order', $course->sort_order) }}">
                        </div>

                        {{-- Status --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status', $course->status) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $course->status) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        {{-- Is Show On Website --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Is Show On website</label>
                            <select name="is_show_on_website" class="form-select @error('is_show_on_website') is-invalid @enderror">
                                <option value="1" {{ old('is_show_on_website', $course->is_show_on_website ?? 1) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_show_on_website', $course->is_show_on_website ?? 1) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_show_on_website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Update Course
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        if (typeof initializeTinyMCE === 'function') {
            initializeTinyMCE('.editor');
        }

        let faqIndex = {{ count($currentFaqs) }};

        function updateFaqNumbers() {
            const count = $('#course_faqs_container .faq-item-card').length;
            $('#course_faqs_container .faq-item-card').each(function(idx) {
                $(this).find('.faq-number').html('<i class="fas fa-question-circle me-1 text-primary"></i>FAQ #' + (idx + 1));
            });
            if (count === 0) {
                $('#no_faqs_message').show();
                $('#add_more_container').hide();
            } else {
                $('#no_faqs_message').hide();
                $('#add_more_container').show();
            }
        }

        function addFaqRow(q = '', a = '') {
            const template = `
                <div class="card mb-2 faq-item-card border border-light-subtle shadow-none rounded-3 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                            <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 faq-number" style="font-size: 0.8rem;">
                                <i class="fas fa-question-circle me-1"></i>FAQ
                            </span>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 remove-faq-btn" style="font-size: 0.78rem;" title="Remove this FAQ">
                                <i class="fas fa-trash-alt me-1"></i> Remove
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Question <span class="text-danger">*</span></label>
                                <input type="text" name="faqs[${faqIndex}][question]" class="form-control form-control-sm" placeholder="e.g. What is the eligibility criteria for this course?" value="${q}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Answer <span class="text-danger">*</span></label>
                                <textarea name="faqs[${faqIndex}][answer]" class="form-control form-control-sm" rows="2" placeholder="Provide a detailed and helpful answer..." required>${a}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#course_faqs_container').append(template);
            faqIndex++;
            updateFaqNumbers();
        }

        $(document).on('click', '#add-faq-btn, #add-more-faq-btn, #empty-add-faq-btn', function() {
            addFaqRow();
        });

        $(document).on('click', '.remove-faq-btn', function() {
            $(this).closest('.faq-item-card').fadeOut(150, function() {
                $(this).remove();
                updateFaqNumbers();
            });
        });
    });
</script>
@endpush
