@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        @can('staff-add')
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Create Organizations</h6>
                </div>
                <div class="card-body">
                    <form id="AddStaffForm" method="POST" action="{{ route('admin.organization.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 form-group mb-4">
                                <label for="name">Organization Name
                                </label>
                                <input type="text" class="form-control" name="name" placeholder="Organization Name"
                                    id="name" value="{{ old('name') }}">
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="email"> Email </label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                    placeholder="Email">
                            </div>
                            <div class="col-md-4 form-group mb-4">
                                <label for="phone"> Mobile </label>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                                    placeholder="Mobile">
                            </div>
                            <div class="col-md-12 form-group mb-4">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address" value="{{ old('address') }}">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-sm btn-primary" form="AddStaffForm"> Save </button>
                </div>
            </div>
        @endcan
    </div>
@endsection
@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="{{ URL::asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>

    <!-- Page level custom scripts -->
    <script>
        $('#photo').filemanager('image');
        $(document).ready(function() {
            $('#description').summernote();
            $('#workingDays').select2({
                theme: 'bootstrap4',
            });
        });
        //   -------------------------------------- jQuery end here
        $('#validateUsername').keyup(function(e) {
            e.preventDefault();
            $('#successValidate').addClass('d-none');
            $('#errorValidate').addClass('d-none');
            if ($(this).val().trim().length > 2 && $(this).val().trim() !== "") {
                var username = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.staff.validate_username') }}",
                    datatype: 'json',
                    data: {
                        'username': username,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status == 1) {
                            $('#successValidate').removeClass('d-none');
                            $('#errorValidate').addClass('d-none');
                        } else if (response.status == 0) {
                            $('#successValidate').addClass('d-none');
                            $('#errorValidate').removeClass('d-none');
                        }
                    },
                });
            }
        });

        $(document).ready(function() {
            // Validate the DOB and Joining Date
            $("#joining_date", ).change(function() {
                const dob = new Date($("#dob").val());
                const doj = new Date($(this).val());
                // Calculate the age difference between DOB and DOJ (Date of Joining)
                const age = (doj - dob) / (1000 * 3600 * 24 * 365.25); // Convert milliseconds to years
                if (age < 16) {
                    alert("Date of Joining must be at least 16 years after Date of Birth.");
                    $("#joining_date").val(''); // Clear the invalid date
                    return false;
                }
            });

            // Validate the Joining Date and Probation End Date
            $("#probation_end_date").change(function() {
                const doj = new Date($("#joining_date").val());
                const probationEndDate = new Date($(this).val());

                if (probationEndDate <= doj) {
                    alert("Probation End Date must be at least 1 day after Date of Joining.");
                    $("#probation_end_date").val(''); // Clear the invalid date
                }
            });
        });
    </script>
@endsection
