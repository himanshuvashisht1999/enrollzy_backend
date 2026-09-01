@extends('admin.layouts.master')
@section('title', 'Edit Filtered Page')
@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Filtered Page</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.filtered-pages.update', $filteredPage) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label>Page Title * (This will display on the frontend)</label>
                    <input type="text" name="title" class="form-control" value="{{ $filteredPage->title }}" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Sub Title</label>
                    <input type="text" name="sub_title" class="form-control" value="{{ $filteredPage->sub_title }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if($filteredPage->image)
                        <div class="mt-2">
                            <img src="{{ asset($filteredPage->image) }}" alt="image" width="100">
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label>Page Slug (Unique) *</label>
                    <input type="text" name="slug" class="form-control" value="{{ $filteredPage->slug }}" required>
                </div>
                <div class="col-md-6">
                    <label>Category *</label>
                    <select name="category" class="form-control" id="categorySelect" required>
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
            <div id="schoolFields" style="display: {{ $filteredPage->category == 'School' ? 'block' : 'none' }}; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>School Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Ownership Type</label>
                        <select name="ownership_type" class="form-control">
                            <option value="">Select Ownership Type</option>
                            <option value="Government" {{ $filteredPage->ownership_type == 'Government' ? 'selected' : '' }}>Government</option>
                            <option value="Private" {{ $filteredPage->ownership_type == 'Private' ? 'selected' : '' }}>Private</option>
                            <option value="Trust / Society" {{ $filteredPage->ownership_type == 'Trust / Society' ? 'selected' : '' }}>Trust / Society</option>
                            <option value="Minority" {{ $filteredPage->ownership_type == 'Minority' ? 'selected' : '' }}>Minority</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>School Type</label>
                        <select name="school_type_id" class="form-control">
                            <option value="">Select School Type</option>
                            @foreach($schoolTypes as $type)
                                <option value="{{ $type->id }}" {{ $filteredPage->school_type_id == $type->id ? 'selected' : '' }}>{{ $type->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Curriculum</label>
                        <select name="curriculum" class="form-control">
                            <option value="">Select Curriculum</option>
                            <option value="CBSE" {{ $filteredPage->curriculum == 'CBSE' ? 'selected' : '' }}>CBSE</option>
                            <option value="ICSE" {{ $filteredPage->curriculum == 'ICSE' ? 'selected' : '' }}>ICSE</option>
                            <option value="State Board" {{ $filteredPage->curriculum == 'State Board' ? 'selected' : '' }}>State Board</option>
                            <option value="IB" {{ $filteredPage->curriculum == 'IB' ? 'selected' : '' }}>IB</option>
                            <option value="IGCSE" {{ $filteredPage->curriculum == 'IGCSE' ? 'selected' : '' }}>IGCSE</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Select Course (Optional)</label>
                        <select name="course_id" class="form-control">
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
            <div id="universityFields" style="display: {{ $filteredPage->category == 'University' ? 'block' : 'none' }}; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>University Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>University Type</label>
                        <select name="university_type" class="form-control">
                            <option value="">Select University Type</option>
                            <option value="Central" {{ $filteredPage->university_type == 'Central' ? 'selected' : '' }}>Central</option>
                            <option value="State" {{ $filteredPage->university_type == 'State' ? 'selected' : '' }}>State</option>
                            <option value="Deemed" {{ $filteredPage->university_type == 'Deemed' ? 'selected' : '' }}>Deemed</option>
                            <option value="Private" {{ $filteredPage->university_type == 'Private' ? 'selected' : '' }}>Private</option>
                            <option value="International" {{ $filteredPage->university_type == 'International' ? 'selected' : '' }}>International</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Browse by Degree</label>
                        <select name="degree" class="form-control">
                            <option value="">Select Degree</option>
                            <option value="Diploma" {{ $filteredPage->degree == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                            <option value="UG" {{ $filteredPage->degree == 'UG' ? 'selected' : '' }}>UG</option>
                            <option value="PG" {{ $filteredPage->degree == 'PG' ? 'selected' : '' }}>PG</option>
                            <option value="Doctoral" {{ $filteredPage->degree == 'Doctoral' ? 'selected' : '' }}>Doctoral</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Browse by Stream</label>
                        <select name="stream_id" class="form-control">
                            <option value="">Select Stream</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream->id }}" {{ $filteredPage->stream_id == $stream->id ? 'selected' : '' }}>{{ $stream->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Select Course (Optional)</label>
                        <select name="course_id" class="form-control">
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
            <div id="coachingFields" style="display: {{ $filteredPage->category == 'Coaching' ? 'block' : 'none' }}; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>Coaching Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Coaching Category</label>
                        <select name="coaching_category_id" class="form-control">
                            <option value="">Select Coaching Category</option>
                            @if(isset($coachingCategories))
                                @foreach($coachingCategories as $cc)
                                    <option value="{{ $cc->id }}" {{ $filteredPage->coaching_category_id == $cc->id ? 'selected' : '' }}>{{ $cc->title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Program Mode</label>
                        <select name="program_type_id" class="form-control">
                            <option value="">Select Program Mode</option>
                            @if(isset($programTypes))
                                @foreach($programTypes as $pt)
                                    <option value="{{ $pt->id }}" {{ $filteredPage->program_type_id == $pt->id ? 'selected' : '' }}>{{ $pt->title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Select Course (Optional)</label>
                        <select name="course_id" class="form-control">
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
            <div id="locationFields" style="display: {{ in_array($filteredPage->category, ['School', 'University', 'Coaching']) ? 'block' : 'none' }}; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>Location Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>State</label>
                        <select name="state" id="stateFilter" class="form-control" data-selected="{{ $filteredPage->state }}">
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <select name="city" id="cityFilter" class="form-control" data-selected="{{ $filteredPage->city }}">
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.filtered-pages.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
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
            res.data.states.forEach(s => {
                let sel = (s.name == selectedState) ? 'selected' : '';
                html += '<option value="'+s.name+'" '+sel+'>'+s.name+'</option>';
            });
            $('#stateFilter').html(html);
            
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
                res.data.forEach(c => {
                    let sel = (c == selCity) ? 'selected' : '';
                    html += '<option value="'+c+'" '+sel+'>'+c+'</option>';
                });
                $('#cityFilter').html(html);
            }
        });
    }

    $('#stateFilter').on('change', function() {
        let state = $(this).val();
        if (state) {
            loadCities(state);
        } else {
            $('#cityFilter').html('<option value="">Select City</option>');
        }
    });
});
</script>
@endsection



