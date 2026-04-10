@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <form id="updateLeaveTypeForm" method="POST" action="{{ route('admin.customer.update', $userData->id) }}">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"> Edit General </h6>
                </div>
                <div class="card-body row">
                    @csrf
                    @method('PATCH')
                    
                    <div class="col-md-4 form-group">
                        <!-- <label for="" id="">Name</label> -->
                        <input type="text" value="{{$userData->name}}" class="form-control" name="name" placeholder="Student Name">
                    </div>
                    <div class="col-md-4 form-group">
                        <!-- <label for="" id="">Email</label> -->
                        <input type="email" value="{{$userData->email}}" class="form-control" name="email" placeholder="Email Id">
                    </div>
                    <div class="col-md-4 form-group">
                        <!-- <label for="" id="">Phone</label> -->
                        <input type="text" value="{{$userData->phone}}" class="form-control" name="phone" placeholder="Mobile Number">
                    </div>
                    @php
                        $selectedCountry = old('country', $userData->country ?? 'India');
                        $selectedState   = old('state',   $userData->state   ?? '');
                        $selectedCity    = old('city',    $userData->city    ?? '');
                    @endphp

                    <div class="col-md-4 form-group">
                        <!-- <label for="countrySelect">Country</label> -->
                        <select name="country"
                                id="countrySelect"
                                class="form-control"
                                required
                                data-selected="{{ $selectedCountry }}">
                            <option value="">Select Country</option>
                            {{-- Options will be loaded by JS --}}
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <!-- <label for="stateSelect">State</label> -->
                        <select name="state"
                                id="stateSelect"
                                class="form-control"
                                required
                                data-selected="{{ $selectedState }}">
                            <option value="">Select State</option>
                            {{-- Options will be loaded by JS --}}
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <!-- <label for="citySelect">City</label> -->
                        <select name="city"
                                id="citySelect"
                                class="form-control"
                                required
                                data-selected="{{ $selectedCity }}">
                            <option value="">Select City</option>
                            {{-- Options will be loaded by JS --}}
                        </select>
                    </div>

                    



                    <!-- <div class="col-md-4 form-group">
                        <label for="designation">Institutes</label>
                        <select name="institute" class="form-control" >
                        <option desabled selected>Select Institutes</option>
                            @foreach ($Institutes as $designat)
                                <option value="{{ $designat->id }}"
                                {{ old('marital_status', $userData->institute_id) == $designat->id ? 'selected' : '' }}>
                                    {{ $designat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> -->
                    <div class="col-md-4 form-group">
                        <!-- <label for="department">Status</label> -->
                                    <select name="status" id="status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                    </div>
                    @foreach($CustomerFields as $single_data)
                    <div class="col-md-4 form-group">
                        <!-- <label for="" id="">{{$single_data->label}}</label> -->
                        <input type="text" value="{{ $UserCustomerField[$single_data->id] ?? '' }}" class="form-control" name="customer_fields[{{$single_data->id}}]" placeholder="{{$single_data->label}}">
                    </div>
                    @endforeach

                    <div class="col-md-12 form-group">
                        <!-- <label for="department">Customer Categories</label> -->

                        <div id="category-row" class="row">
                            @foreach($categoryLevels as $levelIndex => $level)
                                <div class="col-md-4 mb-2 category-wrapper" data-level="{{ $levelIndex }}">
                                    <select class="form-control category-select" data-level="{{ $levelIndex }}">
                                        <option value="">
                                            {{ $levelIndex == 0 ? 'Select category' : 'Select sub category' }}
                                        </option>
                                        @foreach($level['categories'] as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ $level['selected'] == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>

                        {{-- Final leaf category id (for form submit) --}}
                        <input type="hidden" name="category" id="final_category_id"
                            value="{{ $userData->category_id }}">
                    </div>
                </div>
            </form>
            <div class="card-footer text-right">
                <a href="{{ route('admin.leaveSetting.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="updateLeaveTypeForm" class="btn btn-primary btn-sm">Update Customer</a>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Call the function to handle the initial state when the page loads
            toggleLeaveFields();
            // Event listener for allotment_type select change
            $("select[name='allotment_type']").change(function() {
                toggleLeaveFields();
            });
            // Function to toggle the visibility of leave fields and labels
            function toggleLeaveFields() {
                var allotmentType = $("select[name='allotment_type']").val(); // Get selected allotment type
                if (allotmentType == "monthly") {
                    // Show monthly leave, hide yearly leave, and change label
                    $("#yearly_allotment").addClass("d-none"); // Hide yearly leave input
                    $("label[for='monthly_leave']").text("Monthly Leave"); // Change label back to "Monthly Leave"
                } else if (allotmentType == "yearly") {
                    // Show yearly leave, change label for monthly leave
                    $("#yearly_allotment").removeClass("d-none"); // Show yearly leave input
                    $("label[for='monthly_leave']").text("Monthly Limit"); // Change label to "Monthly Limit"
                } else {
                    // Default state if no allotment type selected
                    $("#yearly_allotment").addClass("d-none"); // Hide yearly leave input
                    $("label[for='monthly_leave']").text("Monthly Leave"); // Reset label to "Monthly Leave"
                }
            }
        });
        // ---------------------- jQuery end here
        $(document).ready(function() {
            $('#genderSelect').select2({
                theme: 'bootstrap4',
            });
            $('#maritalSelect').select2({
                theme: 'bootstrap4',
            });
            $('#departmentSelect').select2({
                theme: 'bootstrap4',
            });
            $('#designationSelect').select2({
                theme: 'bootstrap4',
            });
        });

        $(document).ready(function () {

            // When any category select changes
            $(document).on('change', '.category-select', function () {
                var selectedId = $(this).val();
                var level = parseInt($(this).data('level'));

                // Remove all deeper levels when current selection changes
                $('#category-row .category-wrapper').each(function () {
                    var thisLevel = parseInt($(this).data('level'));
                    if (thisLevel > level) {
                        $(this).remove();
                    }
                });

                // If nothing selected, clear final hidden value
                if (!selectedId) {
                    $('#final_category_id').val('');
                    return;
                }

                // Set final selected category (by default current one)
                $('#final_category_id').val(selectedId);

                // Get children of selected category
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.customer.getChildren') }}",
                    dataType: 'json',
                    data: {
                        parent_id: selectedId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status == 1 && response.data.length > 0) {
                            // Build a new col-md-4 with child select
                            var nextLevel = level + 1;
                            var html = '<div class="col-md-4 mb-2 category-wrapper" data-level="' + nextLevel + '">';
                            html += '<select class="form-control category-select" data-level="' + nextLevel + '">';
                            html += '<option value="">Select sub category</option>';

                            $.each(response.data, function (i, cat) {
                                html += '<option value="' + cat.id + '">' + cat.name + '</option>';
                            });

                            html += '</select></div>';

                            // Append the child column
                            $('#category-row').append(html);
                        }
                        // If no children, then current selected is the final category (already in hidden input)
                    }
                });
            });

        });

    </script>

    <script>
        $(document).ready(function () {

            // =================== Country / State / City for EDIT (any country) ===================

            const API_BASE = 'https://countriesnow.space/api/v0.1';

            // Pre-selected values coming from Blade (via data-selected)
            const selectedCountry = $('#countrySelect').data('selected') ? String($('#countrySelect').data('selected')) : '';
            const selectedState   = $('#stateSelect').data('selected')   ? String($('#stateSelect').data('selected'))   : '';
            const selectedCity    = $('#citySelect').data('selected')    ? String($('#citySelect').data('selected'))    : '';

            // 1) Load all countries on page load
            loadCountries(selectedCountry, selectedState, selectedCity);

            // 2) When country changes, load its states
            $('#countrySelect').on('change', function () {
                const countryName = $(this).val();

                // Reset state & city when country changes
                $('#stateSelect').html('<option value="">Select State</option>');
                $('#citySelect').html('<option value="">Select City</option>');

                if (countryName) {
                    loadStates(countryName, null, null); // no preselect when changed manually
                }
            });

            // 3) When state changes, load its cities
            $('#stateSelect').on('change', function () {
                const countryName = $('#countrySelect').val();
                const stateName   = $(this).val();

                $('#citySelect').html('<option value="">Select City</option>');

                if (countryName && stateName) {
                    loadCities(countryName, stateName, null); // no preselect when changed manually
                }
            });

            function loadCountries(preCountry, preState, preCity) {
                $('#countrySelect').html('<option value="">Loading countries...</option>');

                $.ajax({
                    type: 'GET',
                    url: API_BASE + '/countries',
                    dataType: 'json',
                    success: function (res) {
                        if (!res.error && res.data) {
                            let options = '<option value="">Select Country</option>';
                            $.each(res.data, function (i, c) {
                                options += '<option value="' + c.country + '">' + c.country + '</option>';
                            });
                            $('#countrySelect').html(options);

                            // Prefer preCountry from DB/old() if exists, otherwise default to India
                            let countryToSelect = preCountry || 'India';

                            if (countryToSelect) {
                                $('#countrySelect').val(countryToSelect);

                                // If we have a preselected country, load its states with preselect
                                loadStates(countryToSelect, preState, preCity);
                            }
                        } else {
                            $('#countrySelect').html('<option value="">Could not load countries</option>');
                            console.error('Error loading countries:', res);
                        }
                    },
                    error: function (xhr, status, err) {
                        $('#countrySelect').html('<option value="">Could not load countries</option>');
                        console.error('AJAX error loading countries:', err);
                    }
                });
            }

            function loadStates(countryName, preState, preCity) {
                $('#stateSelect').html('<option value="">Loading states...</option>');
                $('#citySelect').html('<option value="">Select City</option>');

                $.ajax({
                    type: 'POST',
                    url: API_BASE + '/countries/states',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        country: countryName
                    }),
                    success: function (res) {
                        if (!res.error && res.data && res.data.states) {
                            let options = '<option value="">Select State</option>';
                            $.each(res.data.states, function (i, state) {
                                const name = state.name;
                                options += '<option value="' + name + '">' + name + '</option>';
                            });
                            $('#stateSelect').html(options);

                            if (preState) {
                                $('#stateSelect').val(preState);
                                // Load cities with preselected city if present
                                loadCities(countryName, preState, preCity);
                            }
                        } else {
                            $('#stateSelect').html('<option value="">Could not load states</option>');
                            console.error('Error loading states:', res);
                        }
                    },
                    error: function (xhr, status, err) {
                        $('#stateSelect').html('<option value="">Could not load states</option>');
                        console.error('AJAX error loading states:', err);
                    }
                });
            }

            function loadCities(countryName, stateName, preCity) {
                $('#citySelect').html('<option value="">Loading cities...</option>');

                $.ajax({
                    type: 'POST',
                    url: API_BASE + '/countries/state/cities',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        country: countryName,
                        state: stateName
                    }),
                    success: function (res) {
                        function removeAccents(str) {
                            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        }
                        if (!res.error && res.data) {
                            let options = '<option value="">Select City</option>';
                            $.each(res.data, function (i, cityName) {
                                var cleanCity = removeAccents(cityName);
                                options += '<option value="' + cleanCity + '">' + cleanCity + '</option>';
                            });
                            $('#citySelect').html(options);

                            if (preCity) {
                                $('#citySelect').val(preCity);
                            }
                        } else {
                            $('#citySelect').html('<option value="">Could not load cities</option>');
                            console.error('Error loading cities:', res);
                        }
                    },
                    error: function (xhr, status, err) {
                        $('#citySelect').html('<option value="">Could not load cities</option>');
                        console.error('AJAX error loading cities:', err);
                    }
                });
            }

            // Optional: if you want select2 styling on these also
            // $('#countrySelect, #stateSelect, #citySelect').select2({ theme: 'bootstrap4' });

        });
    </script>

@endsection
