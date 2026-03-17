@extends('admin.layouts.master')

@section('title', 'Add School - ' . $organisation->name)

@section('content')

    <h3 class="fw-bold mb-4">Add School Academic Profile</h3>

    <form method="POST" action="{{ route('admin.organisation-courses.school-store') }}">
        @csrf
        <input type="hidden" name="organisation_id" value="{{ $organisation->id }}">
        <input type="hidden" name="academic_unit_type" value="School">

        {{-- A. Core Academic Identity --}}
        <div class="card mb-4">
            <div class="card-header fw-bold">Core Academic Identity</div>
            <div class="card-body row g-3">

                <div class="col-md-6">
                    <label>School Name</label>
                    <input type="text" name="school_name" id="school_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label>Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control">
                </div>

                <div class="col-md-4">
                    <label>School Type</label>
                    <select name="school_type" class="form-select">
                        <option>Day School</option>
                        <option>Boarding School</option>
                        <option>Day-cum-Boarding</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Established Year</label>
                    <input type="number" name="established_year" class="form-control">
                </div>

                <div class="col-md-12">
                    <label>About School</label>
                    <textarea name="about_school" class="form-control"></textarea>
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
                        <option>CBSE</option>
                        <option>ICSE</option>
                        <option>State Board</option>
                        <option>IB</option>
                        <option>IGCSE</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Affiliation Number</label>
                    <input type="text" name="board_affiliation_number" class="form-control">
                </div>

                <div class="col-md-2">
                    <label>Valid From</label>
                    <input type="date" name="affiliation_valid_from" class="form-control">
                </div>

                <div class="col-md-2">
                    <label>Valid To</label>
                    <input type="date" name="affiliation_valid_to" class="form-control">
                </div>

                <div class="col-md-6">
                    <label>Medium of Instruction</label>
                    <input type="text" name="medium_of_instruction" class="form-control">
                </div>

                <div class="col-md-6">
                    <label>Grade Range</label>
                    <input type="text" name="grade_range" class="form-control" placeholder="Pre-Primary to XII">
                </div>

                <div class="col-md-12">
                    <label>Streams Offered</label><br>
                    <label><input type="checkbox" name="streams_offered[]" value="Science"> Science</label>
                    <label class="ms-3"><input type="checkbox" name="streams_offered[]" value="Commerce"> Commerce</label>
                    <label class="ms-3"><input type="checkbox" name="streams_offered[]" value="Humanities">
                        Humanities</label>
                </div>

            </div>
        </div>

        {{-- C. Faculty & Student Strength --}}
        <div class="card mb-4">
            <div class="card-header fw-bold">Faculty & Student Strength</div>
            <div class="card-body row g-3">

                <div class="col-md-3"><input class="form-control" name="total_teachers" placeholder="Total Teachers"></div>
                <div class="col-md-3"><input class="form-control" name="trained_teachers_percentage"
                        placeholder="Trained Teachers %"></div>
                <div class="col-md-3"><input class="form-control" name="average_teacher_experience_years"
                        placeholder="Avg Experience (Years)"></div>
                <div class="col-md-3"><input class="form-control" name="student_strength" placeholder="Student Strength">
                </div>

                <div class="col-md-3">
                    <label>Special Educator</label>
                    <select name="special_educator_available" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>School Counsellor</label>
                    <select name="school_counsellor_available" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- D. Academic Delivery --}}
        <div class="card mb-4">
            <div class="card-header fw-bold">Academic Delivery</div>
            <div class="card-body row g-3">

                <div class="col-md-4"><input class="form-control" name="average_class_size"
                        placeholder="Average Class Size"></div>

                <div class="col-md-4">
                    <select name="assessment_pattern" class="form-select">
                        <option>Term-based</option>
                        <option>Semester-based</option>
                        <option>Continuous Evaluation</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <select name="remedial_classes_available" class="form-select">
                        <option value="1">Remedial Classes – Yes</option>
                        <option value="0">Remedial Classes – No</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- H. SEO & Admin --}}
        <div class="card mb-4">
            <div class="card-header fw-bold">Reviews, SEO & Admin</div>
            <div class="card-body row g-3">

                <div class="col-md-4"><input class="form-control" name="average_rating" placeholder="Average Rating">
                </div>
                <div class="col-md-4"><input class="form-control" name="total_reviews" placeholder="Total Reviews">
                </div>

                <div class="col-md-4">
                    <select name="verified_reviews_only" class="form-select">
                        <option value="1">Verified Only</option>
                        <option value="0">All Reviews</option>
                    </select>
                </div>

                <div class="col-md-6"><input class="form-control" name="meta_title" placeholder="Meta Title"></div>
                <div class="col-md-6">
                    <textarea class="form-control" name="meta_description" placeholder="Meta Description"></textarea>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

            </div>
        </div>

        <div class="text-end">
            <button class="btn btn-primary btn-lg">Save School</button>
        </div>

    </form>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('school_name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                let slug = this.value
                    .toLowerCase()                 // lowercase
                    .trim()                        // extra spaces remove
                    .replace(/[^a-z0-9\s-]/g, '')  // special chars remove
                    .replace(/\s+/g, '-')          // space → -
                    .replace(/-+/g, '-');          // multiple - → single -

                slugInput.value = slug;
            });
        }
    });
</script>

