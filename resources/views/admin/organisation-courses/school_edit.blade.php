@extends('admin.layouts.master')

@section('title', 'Edit School - ' . $school->school_name)

@section('content')

<h3 class="fw-bold mb-4">Edit School Academic Profile</h3>

<form method="POST" action="{{ route('admin.organisation-school.update', $school->id) }}">
    @csrf
    @method('PUT')

    <input type="hidden" name="organisation_id" value="{{ $school->organisation_id }}">
    <input type="hidden" name="academic_unit_type" value="School">

    {{-- A. Core Academic Identity --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Core Academic Identity</div>
        <div class="card-body row g-3">

            <div class="col-md-6">
                <label>School Name</label>
                <input type="text" name="school_name" class="form-control"
                       value="{{ old('school_name', $school->school_name) }}" required>
            </div>

            <div class="col-md-6">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug', $school->slug) }}">
            </div>

            <div class="col-md-4">
                <label>School Type</label>
                <select name="school_type" class="form-select">
                    <option value="Day School" {{ old('school_type',$school->school_type)=='Day School'?'selected':'' }}>Day School</option>
                    <option value="Boarding School" {{ old('school_type',$school->school_type)=='Boarding School'?'selected':'' }}>Boarding School</option>
                    <option value="Day-cum-Boarding" {{ old('school_type',$school->school_type)=='Day-cum-Boarding'?'selected':'' }}>Day-cum-Boarding</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Established Year</label>
                <input type="number" name="established_year" class="form-control"
                       value="{{ old('established_year', $school->established_year) }}">
            </div>

            <div class="col-md-12">
                <label>About School</label>
                <textarea name="about_school" class="form-control">{{ old('about_school', $school->about_school) }}</textarea>
            </div>

        </div>
    </div>

    {{-- B. Board & Affiliation --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Board & Affiliation</div>
        <div class="card-body row g-3">

            <div class="col-md-4">
                <label>Education Board</label>
                <select name="education_board" class="form-select">
                    <option {{ old('education_board',$school->education_board)=='CBSE'?'selected':'' }}>CBSE</option>
                    <option {{ old('education_board',$school->education_board)=='ICSE'?'selected':'' }}>ICSE</option>
                    <option {{ old('education_board',$school->education_board)=='State Board'?'selected':'' }}>State Board</option>
                    <option {{ old('education_board',$school->education_board)=='IB'?'selected':'' }}>IB</option>
                    <option {{ old('education_board',$school->education_board)=='IGCSE'?'selected':'' }}>IGCSE</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Affiliation Number</label>
                <input type="text" name="board_affiliation_number" class="form-control"
                       value="{{ old('board_affiliation_number', $school->board_affiliation_number) }}">
            </div>

            <div class="col-md-2">
                <label>Valid From</label>
                <input type="date" name="affiliation_valid_from" class="form-control"
                       value="{{ old('affiliation_valid_from', $school->affiliation_valid_from) }}">
            </div>

            <div class="col-md-2">
                <label>Valid To</label>
                <input type="date" name="affiliation_valid_to" class="form-control"
                       value="{{ old('affiliation_valid_to', $school->affiliation_valid_to) }}">
            </div>

            <div class="col-md-6">
                <label>Medium of Instruction</label>
                <input type="text" name="medium_of_instruction" class="form-control"
                       value="{{ old('medium_of_instruction', $school->medium_of_instruction) }}">
            </div>

            <div class="col-md-6">
                <label>Grade Range</label>
                <input type="text" name="grade_range" class="form-control"
                       value="{{ old('grade_range', $school->grade_range) }}">
            </div>

            <div class="col-md-12">
                <label>Streams Offered</label><br>
                @php
                    $streams = old('streams_offered', $school->streams_offered ?? []);
                @endphp

                <label><input type="checkbox" name="streams_offered[]" value="Science" {{ in_array('Science',$streams)?'checked':'' }}> Science</label>
                <label class="ms-3"><input type="checkbox" name="streams_offered[]" value="Commerce" {{ in_array('Commerce',$streams)?'checked':'' }}> Commerce</label>
                <label class="ms-3"><input type="checkbox" name="streams_offered[]" value="Humanities" {{ in_array('Humanities',$streams)?'checked':'' }}> Humanities</label>
            </div>

        </div>
    </div>

    {{-- C. Faculty & Student Strength --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Faculty & Student Strength</div>
        <div class="card-body row g-3">

            <div class="col-md-3"><input class="form-control" name="total_teachers" value="{{ old('total_teachers',$school->total_teachers) }}" placeholder="Total Teachers"></div>
            <div class="col-md-3"><input class="form-control" name="trained_teachers_percentage" value="{{ old('trained_teachers_percentage',$school->trained_teachers_percentage) }}" placeholder="Trained Teachers %"></div>
            <div class="col-md-3"><input class="form-control" name="average_teacher_experience_years" value="{{ old('average_teacher_experience_years',$school->average_teacher_experience_years) }}" placeholder="Avg Experience (Years)"></div>
            <div class="col-md-3"><input class="form-control" name="student_strength" value="{{ old('student_strength',$school->student_strength) }}" placeholder="Student Strength"></div>

            <div class="col-md-3">
                <label>Special Educator</label>
                <select name="special_educator_available" class="form-select">
                    <option value="1" {{ old('special_educator_available',$school->special_educator_available)==1?'selected':'' }}>Yes</option>
                    <option value="0" {{ old('special_educator_available',$school->special_educator_available)==0?'selected':'' }}>No</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>School Counsellor</label>
                <select name="school_counsellor_available" class="form-select">
                    <option value="1" {{ old('school_counsellor_available',$school->school_counsellor_available)==1?'selected':'' }}>Yes</option>
                    <option value="0" {{ old('school_counsellor_available',$school->school_counsellor_available)==0?'selected':'' }}>No</option>
                </select>
            </div>

        </div>
    </div>

    {{-- D. Academic Delivery --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Academic Delivery</div>
        <div class="card-body row g-3">

            <div class="col-md-4"><input class="form-control" name="average_class_size" value="{{ old('average_class_size',$school->average_class_size) }}"></div>

            <div class="col-md-4">
                <select name="assessment_pattern" class="form-select">
                    <option {{ old('assessment_pattern',$school->assessment_pattern)=='Term-based'?'selected':'' }}>Term-based</option>
                    <option {{ old('assessment_pattern',$school->assessment_pattern)=='Semester-based'?'selected':'' }}>Semester-based</option>
                    <option {{ old('assessment_pattern',$school->assessment_pattern)=='Continuous Evaluation'?'selected':'' }}>Continuous Evaluation</option>
                </select>
            </div>

            <div class="col-md-4">
                <select name="remedial_classes_available" class="form-select">
                    <option value="1" {{ old('remedial_classes_available',$school->remedial_classes_available)==1?'selected':'' }}>Remedial Classes – Yes</option>
                    <option value="0" {{ old('remedial_classes_available',$school->remedial_classes_available)==0?'selected':'' }}>Remedial Classes – No</option>
                </select>
            </div>

        </div>
    </div>

    {{-- H. SEO & Admin --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Reviews, SEO & Admin</div>
        <div class="card-body row g-3">

            <div class="col-md-4"><input class="form-control" name="average_rating" value="{{ old('average_rating',$school->average_rating) }}"></div>
            <div class="col-md-4"><input class="form-control" name="total_reviews" value="{{ old('total_reviews',$school->total_reviews) }}"></div>

            <div class="col-md-4">
                <select name="verified_reviews_only" class="form-select">
                    <option value="1" {{ old('verified_reviews_only',$school->verified_reviews_only)==1?'selected':'' }}>Verified Only</option>
                    <option value="0" {{ old('verified_reviews_only',$school->verified_reviews_only)==0?'selected':'' }}>All Reviews</option>
                </select>
            </div>

            <div class="col-md-6"><input class="form-control" name="meta_title" value="{{ old('meta_title',$school->meta_title) }}"></div>
            <div class="col-md-6"><textarea class="form-control" name="meta_description">{{ old('meta_description',$school->meta_description) }}</textarea></div>

            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="1" {{ old('status',$school->status)==1?'selected':'' }}>Active</option>
                    <option value="0" {{ old('status',$school->status)==0?'selected':'' }}>Inactive</option>
                </select>
            </div>

        </div>
    </div>

    <div class="text-end">
        <button class="btn btn-primary btn-lg">Update School</button>
        <a href="{{ route('admin.organisation-courses.index',['organisation_id'=>$school->organisation_id]) }}" class="btn btn-secondary btn-lg">Cancel</a>
    </div>

</form>
@endsection
