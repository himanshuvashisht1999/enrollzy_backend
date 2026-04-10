@extends('layouts.app')
@section('push_css')
<link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
<style>
    .radio-group {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    input[type="radio"] {
        transform: scale(1.3);
        margin-right: 5px;
    }

    label {
        font-size: 1rem;
        cursor: pointer;
    }

</style>
<div class="container-fluid">
    <div class="form-group col-lg-3">
        <div class="radio-group">
            <div>
                <input type="radio" id="option1" name="group" value="1" {{ $request->group == 1 ? 'checked' : '' }}>
                <label for="option1">Admin Data</label>
            </div>
            <div>
                <input type="radio" id="option2" name="group" value="2" {{ $request->group == 2 ? 'checked' : '' }}>
                <label for="option2">Private Data</label>
            </div>
        </div>
    </div>
    @if($request->group && $request->group == 2)
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Add Calling User</h6>
            <div>
            <a class="btn btn-sm btn-primary" href="javascript:;" data-toggle="modal"
                data-target="#uploadCsvDataCalling">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Data
            </a>
            <a href="javascript:void(0);" onclick="downloadCSV()">
    <i class="fas fa-plus fa-sm text-white-50"></i> Download Sample CSV File
</a>
        </div>
        </div>
    </div>
    @endif
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Find Calling User</h6>

        </div>
        <div class="card-body">
            <form id="sort_product" class="row" action="{{ route('admin.calling.index') }}" name="sort_product">
                @csrf
                <input type="hidden" name="group" value="1">
                <div class="form-group col-lg-3">
                    <select name="category" class="form-control m-2">
                        <option value="">Select Categories</option>

                        @php
                            // Recursive function INSIDE Blade
                            function renderCategoryOptions($category, $request, $level = 0) {
                                foreach ($category as $cat) {
                                    echo '<option value="'.$cat->id.'"'.
                                        ($request->category == $cat->id ? ' selected' : '').
                                        '>';

                                    // Indentation for hierarchy
                                    echo str_repeat("— ", $level).$cat->name;
                                    echo '</option>';

                                    // If category has children
                                    if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                        renderCategoryOptions($cat->childrenRecursive, $request, $level + 1);
                                    }
                                }
                            }
                        @endphp

                        {{-- Call the recursive function --}}
                        @php renderCategoryOptions($category, $request); @endphp

                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <select name="country" class="form-control m-2" id="countryFilter">
                        <option value="">Select Country</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <select name="state" class="form-control m-2" id="stateFilter">
                        <option value="">Select State</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <select name="city" class="form-control m-2" id="cityFilter">
                        <option value="">Select City</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <span>User Without Status</span>
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                            class="custom-control-input"
                            id="toggleUserWithoutStatus"
                            name="user_with_out_status"
                            value="1"
                            {{ $user_with_out_status == 1 ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggleUserWithoutStatus">
                            <span id="toggleLabel">{{ $user_with_out_status == 1 ? 'Yes' : 'No' }}</span>
                        </label>
                    </div>
                </div>
                <div class="form-group col-lg-3">

                    <label class="d-block">Sequence Calling</label>

                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                            class="custom-control-input"
                            id="toggleSequence"
                            name="sequence_mode"
                            value="1"
                            {{ request('sequence_mode') == 1 ? 'checked' : '' }}>

                        <label class="custom-control-label" for="toggleSequence">
                            <span id="sequenceLabel">
                                {{ request('sequence_mode') == 1 ? 'ON (Pending only)' : 'OFF (Normal)' }}
                            </span>
                        </label>
                    </div>

                </div>
                <div class="form-group col-lg-3" style="display:none;" >
                    <select name="institute" class="form-control m-2" id="selectAuthorID">
                        <option value="">Select Institutes</option>
                        @foreach($institutes as $institute)

                        <option value="{{$institute->id}}"
                            {{ $request->institute == $institute->id ? 'selected' : '' }}>{{$institute->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <button class="btn btn-primary" type="submit" id="ubmitSearchButton">Search</button>
                    <a href="{{ route('admin.calling.index') }}" class="btn btn-info">Reset</a>
                    <a href="javascript:void(0)" id="restartBtn" class="btn btn-secondary"
                    data-base-url="{{ route('admin.calling.index') }}" >
                    Re-Start
                    </a>
                </div>
                

            </form>
        </div>
    </div>
    @if($data)
    <div class="card shadow mb-4" id="DataTableTable">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
            <p1>Total number of contacts are {{$count}}</p1>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Category</th>
                            <th>City</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $key=>$item)
                        <tr>
                            <td>#{{$key+1}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{$item->phone}}</td>
                            <td>{{ $item->category?->full_path ?? '—' }}</td>
                            <td>{{ $item->city ?? '—' }}</td>
                            <td>
                                <!-- <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button> -->
                                <a class="btn btn-sm btn-primary" href="javascript:;" data-toggle="modal"
                                    data-target="#openUpdateCallingStatusModal" data-userid="{{$item->id}}"
                                    data-username="{{$item->name}}" data-userphone="{{$item->phone}}"
                                    data-category="{{$request->category}}"
                                    data-institute="{{$request->institute}}">Update Status
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif


</div>

{{-- ----------------------Modal for Add new Product----------------------------------------- --}}
<div class="modal fade" id="openUpdateCallingStatusModal" tabindex="-1" role="dialog"
    aria-labelledby="openUpdateCallingStatusModal" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="openAddProductModalData">Update Calling Status</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('admin.calling.create') }}" method="post" name="sort_product" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" class="form-control" name="group_id" value="{{$request->group}}">
                    <input type="hidden" class="form-control" name="user_id">
                    <input type="hidden" class="form-control" name="user_phone">
                    <input type="hidden" class="form-control" name="category">
                    <input type="hidden" class="form-control" name="institute">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label for="name"> Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Name" readonly>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label for="name">Call Status</label>
                            <select name="call_status" class="form-control" id="product_type" required>
                                <option selected disabled>Select</option>
                                @foreach($CallingStatus as $CallStatus)
                                    <option value="{{ $CallStatus->id }}" data-require-date="{{ $CallStatus->date_require }}">{{ $CallStatus->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden date field -->
                        <div class="col-lg-3 form-group" id="date-field" style="display: none;">
                            <label for="call_date">Reminder Date</label>
                            <input type="date" name="call_date" class="form-control" id="call_date">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label for="image"> Image</label>
                            <input type="file" class="form-control" name="image" placeholder="">
                        </div>
                        <div class="col-lg-12 form-group">
                            <label for="name"> Comments</label>
                            <textarea id="message" name="comment" class="form-control" rows="4" cols="50"
                                placeholder="Add Comments Here..."></textarea>
                        </div>

                        <div class="col-lg-6 form-group">
                            <label>
                                <input type="checkbox" id="is_whatsapp_message">
                                Want to send whatsapp message ?
                            </label>
                        </div>


                        <div id="whatsapp_fields" style="display:none;">

                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label for="name">Template</label>
                                    <select name="whatsapp_template_id" class="form-control" id="whatsapp_template_id">
                                        <option value="">Select</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" data-caption="{{ $template->caption }}" data-message="{{ $template->message }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="batch_gap">Caption</label>
                                    <input type="text" class="form-control" name="caption" value="" id="caption">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="image">Image</label>
                                    <input type="file" class="form-control" name="image_whatsapp" accept=".jpg, .jpeg, .png">
                                    <small id="fileHelp" class="form-text text-muted">
                                        Upload an image (jpg, jpeg, png) not exceeding 2MB.
                                    </small>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="start_time">Start Time</label>
                                    <input type="datetime-local" class="form-control" name="start_time" value="{{ now()->format('Y-m-d\TH:i') }}">
                                </div>     
                                
                                <div class="col-md-12 form-group">
                                    <label for="name"> Message</label>
                                    
                                    <textarea name="message" class="form-control"  id="message-editor"  placeholder="Enter message"></textarea>
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <input class="btn btn-primary" type="submit" id="addProductBtn" href="javascript:;">
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ----------------------Modal for Add new Callind User ----------------------------------------- --}}
<div class="modal fade" id="uploadCsvDataCalling" tabindex="-1" role="dialog" aria-labelledby="uploadCsvDataCalling"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="openAddProductModalData">Upload CSV File</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('admin.calling.csv.store') }}" method="POST" enctype="multipart/form-data">

                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label for="name"> Upload File</label>
                            <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <input class="btn btn-primary" type="submit" id="addProductBtn" href="javascript:;">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('push_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script>

function downloadCSV() {
    const link = document.createElement('a');
    link.href = '/samplecsv/sample.csv';  // Path relative to the public directory
    link.download = 'sample.csv';  // Optional: specify the filename when downloading
    link.click();
}
    // When the modal is about to be shown
    $('#openUpdateCallingStatusModal').on('show.bs.modal', function (e) {
        // Get the name from the button's data-name attribute
        var userid = $(e.relatedTarget).data('userid');
        var username = $(e.relatedTarget).data('username');
        var userphone = $(e.relatedTarget).data('userphone');
        var category = $(e.relatedTarget).data('category');
        var institute = $(e.relatedTarget).data('institute');


        // Set the value of the input field with the name "name" inside the modal
        $(this).find('input[name="user_id"]').val(userid);
        $(this).find('input[name="name"]').val(username);
        $(this).find('input[name="user_phone"]').val(userphone);
        $(this).find('input[name="category"]').val(category);
        $(this).find('input[name="institute"]').val(institute);


    });
    const radios = document.querySelectorAll('input[type="radio"][name="group"]');

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            const selectedValue = this.value;
            const newUrl = window.location.protocol + "//" + window.location.host + window.location
                .pathname + "?group=" + selectedValue;
            window.location.href = newUrl; // Refresh the page with the new URL
        });
    });
    document.getElementById('product_type').addEventListener('change', function() {
        // Get the selected option
        var selectedOption = this.options[this.selectedIndex];

        // Get the value of the data-require-date attribute
        var dateRequired = selectedOption.getAttribute('data-require-date');

        // Get the date field container
        var dateField = document.getElementById('date-field');

        // Show or hide the date input based on the date_require value
        if (dateRequired === 'yes') {
            dateField.style.display = 'block';
        } else {
            dateField.style.display = 'none';
        }
    });

</script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('message-editor', {});

    // Run after DOM + CKEditor are ready
    document.addEventListener('DOMContentLoaded', function () {
        var templateSelect = document.getElementById('whatsapp_template_id');
        var captionInput   = document.getElementById('caption');

        templateSelect.addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];

            var caption = selectedOption.getAttribute('data-caption') || '';
            var message = selectedOption.getAttribute('data-message') || '';

            // Set caption input
            captionInput.value = caption;

            // Set CKEditor content
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].setData(message);
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

    var whatsappToggle = document.getElementById('is_whatsapp_message');
    var whatsappFields = document.getElementById('whatsapp_fields');

    whatsappToggle.addEventListener('change', function () {
        if (this.checked) {
            whatsappFields.style.display = 'block';
        } else {
            whatsappFields.style.display = 'none';

            // Also clear the fields when hidden
            document.getElementById('caption').value = "";
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].setData("");
            }
        }
    });

});

document.getElementById('call_date').addEventListener('click', function () {
    this.showPicker(); // opens the date picker on full field click
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var restartBtn = document.getElementById('restartBtn');
    if (!restartBtn) return;

    restartBtn.addEventListener('click', function(e) {
        var baseUrl = this.getAttribute('data-base-url');
        var group = this.getAttribute('data-current-group') || '1';

        // Get the selected category from the <select name="category">
        var categorySelect = document.querySelector('select[name="category"]');
        var category = categorySelect ? categorySelect.value : '';


        // If you require the user to first select a category, enforce it:
        if (!category) {
            alert('Please select a category first before clicking Re-Start.');
            // optionally focus the category select:
            if (categorySelect) categorySelect.focus();
            return;
        }

        // Build query params safely
        var params = new URLSearchParams();
        params.set('group', group);
        params.set('category', category);

        // Redirect
        window.location.href = baseUrl + '?' + params.toString();
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleUserWithoutStatus');
    const label  = document.getElementById('toggleLabel');

    toggle.addEventListener('change', function () {
        if (this.checked) {
            this.value = 1;
            label.textContent = "Yes";
        } else {
            // Send "0" when OFF
            this.value = 0;
            label.textContent = "No";
        }
    });
});
</script>

<script>
$(document).ready(function () {

    const API_BASE = 'https://countriesnow.space/api/v0.1';

    loadCountries();

    $('#countryFilter').on('change', function () {
        const country = $(this).val();
        $('#stateFilter').html('<option value="">Select State</option>');
        $('#cityFilter').html('<option value="">Select City</option>');
        if (country) loadStates(country);
    });

    $('#stateFilter').on('change', function () {
        const country = $('#countryFilter').val();
        const state   = $(this).val();
        $('#cityFilter').html('<option value="">Select City</option>');
        if (country && state) loadCities(country, state);
    });

    function loadCountries() {
        $.get(API_BASE + '/countries', function(res){
            var html = '<option value="">Select Country</option>';
            var selectedCountry = "{{ request('country') ?? '' }}";

            res.data.forEach(c => {
                var selected = (c.country === selectedCountry) ? 'selected' : '';
                html += `<option value="${c.country}" ${selected}>${c.country}</option>`;
            });

            $('#countryFilter').html(html);

            if (selectedCountry) {
                loadStates(selectedCountry);
            }
        });
    }

    function loadStates(country) {

        $.ajax({
            type: 'POST',
            url: API_BASE + '/countries/states',
            contentType: 'application/json',
            data: JSON.stringify({ country }),
            success: function(res){

                var html = '<option value="">Select State</option>';
                var selectedState = "{{ request('state') ?? '' }}";

                res.data.states.forEach(s => {
                    var selected = (s.name === selectedState) ? 'selected' : '';
                    html += `<option value="${s.name}" ${selected}>${s.name}</option>`;
                });

                $('#stateFilter').html(html);

                if (selectedState) {
                    loadCities(country, selectedState);
                }
            }
        });
    }


    function loadCities(country, state) {

        $.ajax({
            type: 'POST',
            url: API_BASE + '/countries/state/cities',
            contentType: 'application/json',
            data: JSON.stringify({ country, state }),
            success: function(res){

                var html = '<option value="">Select City</option>';
                var selectedCity = "{{ request('city') ?? '' }}";

                res.data.forEach(city => {
                    var selected = (city === selectedCity) ? 'selected' : '';
                    html += `<option value="${city}" ${selected}>${city}</option>`;
                });

                $('#cityFilter').html(html);
            }
        });
    }


});
</script>

@endpush
