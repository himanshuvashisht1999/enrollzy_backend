@extends('admin.layouts.master')
@section('title', 'Create Filtered Page')
@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Create Filtered Page</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.filtered-pages.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Page Slug (Unique) *</label>
                    <input type="text" name="slug" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Category *</label>
                    <select name="category" class="form-control" id="categorySelect" required>
                        <option value="">Select Category</option>
                        <option value="School">School</option>
                        <option value="University">University</option>
                        <option value="Coaching">Coaching</option>
                        <option value="Carrier Road Map">Carrier Road Map</option>
                        <option value="Exam">Exam</option>
                        <option value="Schoolship">Schoolship</option>
                    </select>
                </div>
            </div>

            <!-- Fields specific for School -->
            <div id="schoolFields" style="display: none; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>School Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Ownership Type</label>
                        <select name="ownership_type" class="form-control">
                                                        <option value="">Select Ownership Type</option>
                            <option value="Government">Government</option>
                            <option value="Private">Private</option>
                            <option value="Trust / Society">Trust / Society</option>
                            <option value="Minority">Minority</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>School Type</label>
                        <select name="school_type_id" class="form-control">
                            <option value="">Select School Type</option>
                            @foreach($schoolTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Curriculum</label>
                        <select name="curriculum" class="form-control">
                            <option value="">Select Curriculum</option>
                            <option value="CBSE">CBSE</option>
                            <option value="ICSE">ICSE</option>
                            <option value="State Board">State Board</option>
                            <option value="IB">IB</option>
                            <option value="IGCSE">IGCSE</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Fields specific for University -->
            <div id="universityFields" style="display: none; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>University Details</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>University Type</label>
                        <select name="university_type" class="form-control">
                            <option value="">Select University Type</option>
                            <option value="Central">Central</option>
                            <option value="State">State</option>
                            <option value="Deemed">Deemed</option>
                            <option value="Private">Private</option>
                            <option value="International">International</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Browse by Degree</label>
                        <select name="degree" class="form-control">
                            <option value="">Select Degree</option>
                            <option value="Diploma">Diploma</option>
                            <option value="UG">UG</option>
                            <option value="PG">PG</option>
                            <option value="Doctoral">Doctoral</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Browse by Stream</label>
                        <select name="stream_id" class="form-control">
                            <option value="">Select Stream</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream->id }}">{{ $stream->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Shared Location Fields -->
            <div id="locationFields" style="display: none; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>Location Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>State</label>
                        <select name="state" id="stateFilter" class="form-control">
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <select name="city" id="cityFilter" class="form-control">
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
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
        $('#universityFields').hide();
        $('#locationFields').hide();
        
        if (cat === 'School') {
            $('#schoolFields').show();
            $('#locationFields').show();
        } else if (cat === 'University') {
            $('#universityFields').show();
            $('#locationFields').show();
        }
    }

    $('#categorySelect').on('change', toggleFields);
    toggleFields(); // run on load just in case

    const API_BASE = 'https://countriesnow.space/api/v0.1';
    let country = 'India'; 
    
    // Load states
    $.ajax({
        type: 'POST',
        url: API_BASE + '/countries/states',
        contentType: 'application/json',
        data: JSON.stringify({ country }),
        success: function(res){
            let html = '<option value="">Select State</option>';
            res.data.states.forEach(s => {
                html += '<option value="'+s.name+'">'+s.name+'</option>';
            });
            $('#stateFilter').html(html);
        }
    });

    // Load cities on state change
    $('#stateFilter').on('change', function() {
        let state = $(this).val();
        if (state) {
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/state/cities',
                contentType: 'application/json',
                data: JSON.stringify({ country, state }),
                success: function(res){
                    let html = '<option value="">Select City</option>';
                    res.data.forEach(c => {
                        html += '<option value="'+c+'">'+c+'</option>';
                    });
                    $('#cityFilter').html(html);
                }
            });
        } else {
            $('#cityFilter').html('<option value="">Select City</option>');
        }
    });
});
</script>
@endsection

