@extends('admin.layouts.master')
@section('title', 'Edit Filtered Page')

@push('css')
<style>
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        background-color: #ffffff !important;
        transition: all 0.2s ease;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        font-size: 0.88rem !important;
        color: #1e293b !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
    }
    .select2-dropdown {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.88rem !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important;
    }
    .section-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-edit me-2"></i>Edit Filtered Page
            </h6>
            <a href="{{ route('admin.filtered-pages.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.filtered-pages.update', $filteredPage) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted">Page Title * (Frontend Display Title)</label>
                        <input type="text" name="title" id="pageTitle" class="form-control form-control-lg rounded-3 fs-6" value="{{ $filteredPage->title }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted">Sub Title</label>
                        <input type="text" name="sub_title" class="form-control rounded-3" value="{{ $filteredPage->sub_title }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted">Banner / Featured Image</label>
                        <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                        @if($filteredPage->image)
                            <div class="mt-2.5">
                                <img src="{{ asset($filteredPage->image) }}" alt="image" class="rounded-3 border shadow-sm" width="120">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Page Slug (Unique) *</label>
                        <input type="text" name="slug" id="pageSlug" class="form-control rounded-3 font-monospace" value="{{ $filteredPage->slug }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Category *</label>
                        <select name="category" class="form-select select2" id="categorySelect" required data-placeholder="Select Category">
                            <option value="">Select Category</option>
                            <option value="School" {{ $filteredPage->category == 'School' ? 'selected' : '' }}>School</option>
                            <option value="University" {{ $filteredPage->category == 'University' ? 'selected' : '' }}>University</option>
                            <option value="Coaching" {{ $filteredPage->category == 'Coaching' ? 'selected' : '' }}>Coaching</option>
                            <option value="Carrier Road Map" {{ $filteredPage->category == 'Carrier Road Map' ? 'selected' : '' }}>Carrier Road Map</option>
                            <option value="Exam" {{ $filteredPage->category == 'Exam' ? 'selected' : '' }}>Exam</option>
                            <option value="Scholarship" {{ $filteredPage->category == 'Scholarship' ? 'selected' : '' }}>Scholarship</option>
                        </select>
                    </div>
                </div>

                <!-- Fields specific for School -->
                <div id="schoolFields" class="section-box" style="display: {{ $filteredPage->category == 'School' ? 'block' : 'none' }};">
                    <div class="section-title">
                        <i class="fas fa-school text-primary"></i> School Specific Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Ownership Type</label>
                            <select name="ownership_type" class="form-select select2" data-placeholder="Select Ownership Type">
                                <option value="">Select Ownership Type</option>
                                <option value="Government" {{ $filteredPage->ownership_type == 'Government' ? 'selected' : '' }}>Government</option>
                                <option value="Private" {{ $filteredPage->ownership_type == 'Private' ? 'selected' : '' }}>Private</option>
                                <option value="Trust / Society" {{ $filteredPage->ownership_type == 'Trust / Society' ? 'selected' : '' }}>Trust / Society</option>
                                <option value="Minority" {{ $filteredPage->ownership_type == 'Minority' ? 'selected' : '' }}>Minority</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">School Type</label>
                            <select name="school_type_id" class="form-select select2" data-placeholder="Select School Type">
                                <option value="">Select School Type</option>
                                @foreach($schoolTypes as $type)
                                    <option value="{{ $type->id }}" {{ $filteredPage->school_type_id == $type->id ? 'selected' : '' }}>{{ $type->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Curriculum / Board</label>
                            <select name="curriculum" class="form-select select2" data-placeholder="Select Curriculum">
                                <option value="">Select Curriculum</option>
                                <option value="CBSE" {{ $filteredPage->curriculum == 'CBSE' ? 'selected' : '' }}>CBSE</option>
                                <option value="ICSE" {{ $filteredPage->curriculum == 'ICSE' ? 'selected' : '' }}>ICSE</option>
                                <option value="State Board" {{ $filteredPage->curriculum == 'State Board' ? 'selected' : '' }}>State Board</option>
                                <option value="IB" {{ $filteredPage->curriculum == 'IB' ? 'selected' : '' }}>IB</option>
                                <option value="IGCSE" {{ $filteredPage->curriculum == 'IGCSE' ? 'selected' : '' }}>IGCSE</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Select Course (Optional)</label>
                            <select name="course_id" class="form-select select2" data-placeholder="Select Course (Optional)">
                                <option value="">Select Course (Optional)</option>
                                @if(isset($courses))
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ $filteredPage->course_id == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Fields specific for University -->
                <div id="universityFields" class="section-box" style="display: {{ $filteredPage->category == 'University' ? 'block' : 'none' }};">
                    <div class="section-title">
                        <i class="fas fa-university text-primary"></i> University Specific Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">University Type</label>
                            <select name="university_type" class="form-select select2" data-placeholder="Select University Type">
                                <option value="">Select University Type</option>
                                <option value="Central" {{ $filteredPage->university_type == 'Central' ? 'selected' : '' }}>Central</option>
                                <option value="State" {{ $filteredPage->university_type == 'State' ? 'selected' : '' }}>State</option>
                                <option value="Deemed" {{ $filteredPage->university_type == 'Deemed' ? 'selected' : '' }}>Deemed</option>
                                <option value="Private" {{ $filteredPage->university_type == 'Private' ? 'selected' : '' }}>Private</option>
                                <option value="International" {{ $filteredPage->university_type == 'International' ? 'selected' : '' }}>International</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Browse by Degree Level</label>
                            <select name="degree" class="form-select select2" data-placeholder="Select Degree">
                                <option value="">Select Degree</option>
                                <option value="Diploma" {{ $filteredPage->degree == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                <option value="UG" {{ $filteredPage->degree == 'UG' ? 'selected' : '' }}>UG (Undergraduate)</option>
                                <option value="PG" {{ $filteredPage->degree == 'PG' ? 'selected' : '' }}>PG (Postgraduate)</option>
                                <option value="Doctoral" {{ $filteredPage->degree == 'Doctoral' ? 'selected' : '' }}>Doctoral (Ph.D)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Browse by Stream</label>
                            <select name="stream_id" class="form-select select2" data-placeholder="Select Stream">
                                <option value="">Select Stream</option>
                                @foreach($streams as $stream)
                                    <option value="{{ $stream->id }}" {{ $filteredPage->stream_id == $stream->id ? 'selected' : '' }}>{{ $stream->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Select Course (Optional)</label>
                            <select name="course_id" class="form-select select2" data-placeholder="Select Course (Optional)">
                                <option value="">Select Course (Optional)</option>
                                @if(isset($courses))
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ $filteredPage->course_id == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Fields specific for Coaching -->
                <div id="coachingFields" class="section-box" style="display: {{ $filteredPage->category == 'Coaching' ? 'block' : 'none' }};">
                    <div class="section-title">
                        <i class="fas fa-chalkboard-teacher text-primary"></i> Coaching Specific Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Coaching Category</label>
                            <select name="coaching_category_id" class="form-select select2" data-placeholder="Select Coaching Category">
                                <option value="">Select Coaching Category</option>
                                @if(isset($coachingCategories))
                                    @foreach($coachingCategories as $cc)
                                        <option value="{{ $cc->id }}" {{ $filteredPage->coaching_category_id == $cc->id ? 'selected' : '' }}>{{ $cc->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Program Mode</label>
                            <select name="program_type_id" class="form-select select2" data-placeholder="Select Program Mode">
                                <option value="">Select Program Mode</option>
                                @if(isset($programTypes))
                                    @foreach($programTypes as $pt)
                                        <option value="{{ $pt->id }}" {{ $filteredPage->program_type_id == $pt->id ? 'selected' : '' }}>{{ $pt->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Select Course (Optional)</label>
                            <select name="course_id" class="form-select select2" data-placeholder="Select Course (Optional)">
                                <option value="">Select Course (Optional)</option>
                                @if(isset($courses))
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ $filteredPage->course_id == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Shared Location Fields -->
                <div id="locationFields" class="section-box" style="display: {{ in_array($filteredPage->category, ['School', 'University', 'Coaching']) ? 'block' : 'none' }};">
                    <div class="section-title">
                        <i class="fas fa-map-marked-alt text-primary"></i> Target Location Filter
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">State</label>
                            <select name="state" id="stateFilter" class="form-select select2" data-selected="{{ $filteredPage->state }}" data-placeholder="Select State">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">City</label>
                            <select name="city" id="cityFilter" class="form-select select2" data-selected="{{ $filteredPage->city }}" data-placeholder="Select City">
                                <option value="">Select City</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-save me-1"></i> Update Filtered Page
                    </button>
                    <a href="{{ route('admin.filtered-pages.index') }}" class="btn btn-light border px-4 py-2 rounded-pill text-muted fw-semibold">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Initialize all Select2 dropdowns
    function initSelect2(selector = '.select2') {
        $(selector).each(function() {
            let placeholder = $(this).data('placeholder') || 'Select an option';
            $(this).select2({
                width: '100%',
                placeholder: placeholder,
                allowClear: true
            });
        });
    }
    initSelect2();

    function toggleFields() {
        let cat = $('#categorySelect').val();
        
        $('#schoolFields').hide();
        $('#schoolFields :input').prop('disabled', true);
        
        $('#universityFields').hide();
        $('#universityFields :input').prop('disabled', true);
        
        $('#coachingFields').hide();
        $('#coachingFields :input').prop('disabled', true);
        
        $('#locationFields').hide();
        $('#locationFields :input').prop('disabled', true);
        
        if (cat === 'School') {
            $('#schoolFields').show();
            $('#schoolFields :input').prop('disabled', false);
            $('#locationFields').show();
            $('#locationFields :input').prop('disabled', false);
        } else if (cat === 'University') {
            $('#universityFields').show();
            $('#universityFields :input').prop('disabled', false);
            $('#locationFields').show();
            $('#locationFields :input').prop('disabled', false);
        } else if (cat === 'Coaching') {
            $('#coachingFields').show();
            $('#coachingFields :input').prop('disabled', false);
            $('#locationFields').show();
            $('#locationFields :input').prop('disabled', false);
        }

        // Re-adjust select2 widths inside newly visible containers
        setTimeout(function() {
            $('.select2').each(function() {
                $(this).select2({
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Select an option',
                    allowClear: true
                });
            });
        }, 50);
    }

    $('#categorySelect').on('change', toggleFields);
    toggleFields();

    const API_BASE = 'https://countriesnow.space/api/v0.1';
    let country = 'India'; 
    let selectedState = $('#stateFilter').data('selected');
    let selectedCity = $('#cityFilter').data('selected');
    
    // Load states
    $.ajax({
        type: 'POST',
        url: API_BASE + '/countries/states',
        contentType: 'application/json',
        data: JSON.stringify({ country }),
        success: function(res){
            let html = '<option value="">Select State</option>';
            if (res.data && res.data.states) {
                res.data.states.forEach(s => {
                    let sel = (s.name == selectedState) ? 'selected' : '';
                    html += '<option value="'+s.name+'" '+sel+'>'+s.name+'</option>';
                });
            }
            $('#stateFilter').html(html).trigger('change.select2');
            
            if (selectedState) {
                loadCities(selectedState, selectedCity);
            }
        }
    });

    function loadCities(state, selCity = null) {
        $.ajax({
            type: 'POST',
            url: API_BASE + '/countries/state/cities',
            contentType: 'application/json',
            data: JSON.stringify({ country, state }),
            success: function(res){
                let html = '<option value="">Select City</option>';
                if (res.data) {
                    res.data.forEach(c => {
                        let sel = (c == selCity) ? 'selected' : '';
                        html += '<option value="'+c+'" '+sel+'>'+c+'</option>';
                    });
                }
                $('#cityFilter').html(html).trigger('change.select2');
            }
        });
    }

    $('#stateFilter').on('change', function() {
        let state = $(this).val();
        if (state) {
            loadCities(state);
        } else {
            $('#cityFilter').html('<option value="">Select City</option>').trigger('change.select2');
        }
    });
});
</script>
@endpush
