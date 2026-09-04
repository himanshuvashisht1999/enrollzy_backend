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

                        {{-- Program Level --}}
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
    });
</script>
@endpush
