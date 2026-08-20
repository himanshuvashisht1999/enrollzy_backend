import os

create_code = r'''@extends('admin.layouts.master')
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
                            <option value="Private">Private</option>
                            <option value="Public">Public</option>
                            <option value="Government">Government</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>School Type</label>
                        <select name="school_type_id" class="form-control">
                            <option value="">Select School Type</option>
                            @foreach( as )
                                <option value="{{ ->id }}">{{ ->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
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
                    <div class="col-md-4 mb-3">
                        <label>State</label>
                        <select name="state" id="stateFilter" class="form-control">
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
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

@push('scripts')
<script>
    document.getElementById('categorySelect').addEventListener('change', function() {
        if (this.value === 'School') {
            document.getElementById('schoolFields').style.display = 'block';
        } else {
            document.getElementById('schoolFields').style.display = 'none';
        }
    });

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
            #stateFilter.html(html);
        }
    });

    // Load cities on state change
    #stateFilter.on('change', function() {
        let state = .val();
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
                    #cityFilter.html(html);
                }
            });
        } else {
            #cityFilter.html('<option value="">Select City</option>');
        }
    });
</script>
@endpush
'''

edit_code = r'''@extends('admin.layouts.master')
@section('title', 'Edit Filtered Page')
@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Filtered Page</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.filtered-pages.update', ) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Page Slug (Unique) *</label>
                    <input type="text" name="slug" class="form-control" value="{{ ->slug }}" required>
                </div>
                <div class="col-md-6">
                    <label>Category *</label>
                    <select name="category" class="form-control" id="categorySelect" required>
                        <option value="">Select Category</option>
                        <option value="School" {{ ->category == 'School' ? 'selected' : '' }}>School</option>
                        <option value="University" {{ ->category == 'University' ? 'selected' : '' }}>University</option>
                        <option value="Coaching" {{ ->category == 'Coaching' ? 'selected' : '' }}>Coaching</option>
                        <option value="Carrier Road Map" {{ ->category == 'Carrier Road Map' ? 'selected' : '' }}>Carrier Road Map</option>
                        <option value="Exam" {{ ->category == 'Exam' ? 'selected' : '' }}>Exam</option>
                        <option value="Schoolship" {{ ->category == 'Schoolship' ? 'selected' : '' }}>Schoolship</option>
                    </select>
                </div>
            </div>

            <!-- Fields specific for School -->
            <div id="schoolFields" style="display: {{ ->category == 'School' ? 'block' : 'none' }}; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h5>School Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Ownership Type</label>
                        <select name="ownership_type" class="form-control">
                            <option value="">Select Ownership Type</option>
                            <option value="Private" {{ ->ownership_type == 'Private' ? 'selected' : '' }}>Private</option>
                            <option value="Public" {{ ->ownership_type == 'Public' ? 'selected' : '' }}>Public</option>
                            <option value="Government" {{ ->ownership_type == 'Government' ? 'selected' : '' }}>Government</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>School Type</label>
                        <select name="school_type_id" class="form-control">
                            <option value="">Select School Type</option>
                            @foreach( as )
                                <option value="{{ ->id }}" {{ ->school_type_id == ->id ? 'selected' : '' }}>{{ ->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Curriculum</label>
                        <select name="curriculum" class="form-control">
                            <option value="">Select Curriculum</option>
                            <option value="CBSE" {{ ->curriculum == 'CBSE' ? 'selected' : '' }}>CBSE</option>
                            <option value="ICSE" {{ ->curriculum == 'ICSE' ? 'selected' : '' }}>ICSE</option>
                            <option value="State Board" {{ ->curriculum == 'State Board' ? 'selected' : '' }}>State Board</option>
                            <option value="IB" {{ ->curriculum == 'IB' ? 'selected' : '' }}>IB</option>
                            <option value="IGCSE" {{ ->curriculum == 'IGCSE' ? 'selected' : '' }}>IGCSE</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>State</label>
                        <select name="state" id="stateFilter" class="form-control" data-selected="{{ ->state }}">
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>City</label>
                        <select name="city" id="cityFilter" class="form-control" data-selected="{{ ->city }}">
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

@push('scripts')
<script>
    document.getElementById('categorySelect').addEventListener('change', function() {
        if (this.value === 'School') {
            document.getElementById('schoolFields').style.display = 'block';
        } else {
            document.getElementById('schoolFields').style.display = 'none';
        }
    });

    const API_BASE = 'https://countriesnow.space/api/v0.1';
    let country = 'India'; 
    let selectedState = #stateFilter.data('selected');
    let selectedCity = #cityFilter.data('selected');
    
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
            #stateFilter.html(html);
            
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
                #cityFilter.html(html);
            }
        });
    }

    #stateFilter.on('change', function() {
        let state = .val();
        if (state) {
            loadCities(state);
        } else {
            #cityFilter.html('<option value="">Select City</option>');
        }
    });
</script>
@endpush
'''

with open('resources/views/admin/filtered-pages/create.blade.php', 'w') as f:
    f.write(create_code)

with open('resources/views/admin/filtered-pages/edit.blade.php', 'w') as f:
    f.write(edit_code)

