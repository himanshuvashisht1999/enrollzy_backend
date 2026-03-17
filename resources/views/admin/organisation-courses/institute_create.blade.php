@extends('admin.layouts.master')

@section('title', 'Add Institute - ' . $organisation->name)


@section('content')

<h3 class="fw-bold mb-4">Add Institute Academic Profile</h3>

<form method="POST" action="{{ route('admin.organisation-courses.school-store') }}">
    @csrf

    <input type="hidden" name="organisation_id" value="{{ $organisation->id }}">
    <input type="hidden" name="academic_unit_type" value="CoachingCentre">

    {{-- A. Core Academic Identity --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Core Academic Identity</div>
            <div class="card-body row g-3">

            {{-- <div class="col-md-6">
                <label>Coaching Centre Name</label>
                <input type="text" name="academic_unit_name" class="form-control"
                       value="{{ old('academic_unit_name') }}">
            </div>

            <div class="col-md-6">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug') }}">
            </div> --}}

            <div class="col-md-6">
                <label>Institute / School Name</label>
                    <input type="text"
                        name="academic_unit_name"
                        id="academic_unit_name"
                        class="form-control"
                        value="{{ old('academic_unit_name') }}"
                        placeholder="Enter Institute Name">
                </div>

                <div class="col-md-6">
                    <label>Slug</label>
                    <input type="text"
                        name="slug"
                        id="slug"
                        class="form-control"
                        value="{{ old('slug') }}"
                        placeholder="auto-generated-slug">
                </div>


            <div class="col-md-4">
                <label>Delivery Mode</label>
                <select name="delivery_mode" class="form-select">
                    <option value="Offline" {{ old('delivery_mode')=='Offline'?'selected':'' }}>Offline</option>
                    <option value="Online" {{ old('delivery_mode')=='Online'?'selected':'' }}>Online</option>
                    <option value="Hybrid" {{ old('delivery_mode')=='Hybrid'?'selected':'' }}>Hybrid</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Established Year</label>
                <input type="number" name="established_year" class="form-control"
                       value="{{ old('established_year') }}">
            </div>

            <div class="col-md-12">
                <label>About Coaching Centre</label>
                <textarea name="about_academic_unit" class="form-control"
                          rows="3">{{ old('about_academic_unit') }}</textarea>
            </div>

        </div>
    </div>

    {{-- B. Exam & Course Focus --}}
    @php
        $exams = old('exams_prepared_for', []);
        $targets = old('target_classes', []);
    @endphp

    <div class="card mb-4">
        <div class="card-header fw-bold">Exam & Course Focus</div>
        <div class="card-body row g-3">

            <div class="col-md-12">
                <label>Exams Prepared For</label><br>
                @foreach(['NEET','IIT JEE','NDA','Foundation'] as $exam)
                    <label class="me-3">
                        <input type="checkbox" name="exams_prepared_for[]" value="{{ $exam }}"
                               {{ in_array($exam,$exams)?'checked':'' }}>
                        {{ $exam }}
                    </label>
                @endforeach
            </div>

            <div class="col-md-12">
                <label>Target Classes</label><br>
                @foreach(['9','10','11','12','Dropper'] as $class)
                    <label class="me-3">
                        <input type="checkbox" name="target_classes[]" value="{{ $class }}"
                               {{ in_array($class,$targets)?'checked':'' }}>
                        {{ $class }}
                    </label>
                @endforeach
            </div>

            <div class="col-md-6">
                <label>Medium of Instruction (Comma separated)</label>
                <input type="text" name="medium_of_instruction" class="form-control"
                       value="{{ old('medium_of_instruction') }}"
                       placeholder="English, Hindi">
            </div>

            <div class="col-md-4">
                <label>Integrated Schooling Available</label>
                <select name="integrated_schooling_available" class="form-select">
                    <option value="1" {{ old('integrated_schooling_available')=='1'?'selected':'' }}>Yes</option>
                    <option value="0" {{ old('integrated_schooling_available')=='0'?'selected':'' }}>No</option>
                </select>
            </div>

        </div>
    </div>

    {{-- C. Batch Structure --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Batch Structure</div>
        <div class="card-body row g-3">

            <div class="col-md-3"><input class="form-control" name="total_batches" value="{{ old('total_batches') }}" placeholder="Total Batches"></div>
            <div class="col-md-3"><input class="form-control" name="average_batch_size" value="{{ old('average_batch_size') }}" placeholder="Average Batch Size"></div>
            <div class="col-md-3"><input class="form-control" name="min_batch_size" value="{{ old('min_batch_size') }}" placeholder="Min Batch Size"></div>
            <div class="col-md-3"><input class="form-control" name="max_batch_size" value="{{ old('max_batch_size') }}" placeholder="Max Batch Size"></div>

            <div class="col-md-3"><input class="form-control" name="student_teacher_ratio" value="{{ old('student_teacher_ratio') }}" placeholder="Student Teacher Ratio"></div>

            <div class="col-md-3">
                <select name="separate_batches_for_droppers" class="form-select">
                    <option value="1" {{ old('separate_batches_for_droppers')=='1'?'selected':'' }}>Droppers Batch – Yes</option>
                    <option value="0" {{ old('separate_batches_for_droppers')=='0'?'selected':'' }}>Droppers Batch – No</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="merit_based_batching" class="form-select">
                    <option value="1" {{ old('merit_based_batching')=='1'?'selected':'' }}>Merit Based – Yes</option>
                    <option value="0" {{ old('merit_based_batching')=='0'?'selected':'' }}>Merit Based – No</option>
                </select>
            </div>

        </div>
    </div>

    {{-- I. Reviews, SEO & Admin --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Reviews, SEO & Admin</div>
        <div class="card-body row g-3">

            <div class="col-md-3"><input class="form-control" name="average_rating" value="{{ old('average_rating') }}" placeholder="Average Rating"></div>
            <div class="col-md-3"><input class="form-control" name="total_reviews" value="{{ old('total_reviews') }}" placeholder="Total Reviews"></div>

            <div class="col-md-6"><input class="form-control" name="meta_title" value="{{ old('meta_title') }}" placeholder="Meta Title"></div>

            <div class="col-md-12">
                <textarea class="form-control" name="meta_description" placeholder="Meta Description">{{ old('meta_description') }}</textarea>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="1" {{ old('status')=='1'?'selected':'' }}>Active</option>
                    <option value="0" {{ old('status')=='0'?'selected':'' }}>Inactive</option>
                </select>
            </div>

        </div>
    </div>

    <div class="text-end">
        <button class="btn btn-primary btn-lg">Save Coaching Centre</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg">Cancel</a>
    </div>

</form>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('academic_unit_name');
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


