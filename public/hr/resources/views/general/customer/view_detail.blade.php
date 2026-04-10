@extends('layouts.app')
@section('push_css')
    <style>
        .ui-helper-hidden-accessible {
            display: none;
        }

        .ui-menu {
            position: relative;
            list-style: none;
            background-color: white;
            border-radius: 18px;
            z-index: 999999999;
            box-shadow: 5px 0px 8px white;
            width: 214px;
            line-height: 200%;
            font-size: 16px;
            padding: 15px 0;
            border: solid 1px grey;
            overflow: scroll;
            max-height: 500px;
        }

        .ui-menu .ui-menu-item {
            margin: none;
            padding-left: 15px;
            padding-right: 10px;
        }

        .ui-menu .ui-menu-item:hover {
            background-color: rgb(0, 0, 0);
            color: white;
        }

        @keyframes expandismo {
            from {
                width: 40px;
                margin-left: 49%;
            }

            to {
                width: 73%;
                margin-left: 15%;
            }
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Customer Detail</h6>
            </div>
            @can('customer-read')
                <div class="card-body">
                    <div class="px-4 mt-4">
                        <!-- Account page navigation-->
                        <nav class="nav nav-borders">
                            <a class="nav-link NewNavButtomn btn-sm btn-primary mx-3" href="javascript:;"
                                id="btnProfile">Profile</a>
                            <a class="nav-link NewNavButtomn btn-sm btn-dark mx-3" href="javascript:;"
                                id="btnSecurity">Security</a>
                            <a class="nav-link NewNavButtomn btn-sm btn-dark mx-3" href="javascript:;"
                                id="btnNotifications">Notifications</a>
                        </nav>
                        <hr class="mt-0 mb-4" />
                        <div id="sectionProfile" class="section">
                            <form name="UpdateFormData" id="UpdateFormData">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                <div class="row">
                                    <div class="col-xl-4">
                                        <!-- Profile picture card-->
                                        <div class="card mb-4 mb-xl-0">
                                            <div class="card-header">Profile Picture</div>
                                            <div class="card-body text-center">
                                                <!-- Profile picture image-->
                                                <img id="profileImagePreview" class="img-account-profile rounded-circle mb-2"
                                                    src="{{ env('WEB_URL') }}/profile/{{ $customer->profile_image ?? '\storage\photos\Image_not_available.png' }}"
                                                    alt="" style="height: 175px" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-8">
                                        <!-- Account details card-->
                                        <div class="card">
                                            <div class="card-header">Profile Details</div>
                                            <div class="card-body">
                                                <div class="row gx-3 ">
                                                    <div class="col-md-6">
                                                        <label class="small mb-1">Full name</label>
                                                        <input class="form-control" type="text" name="name"
                                                            placeholder="Enter Full Name" value="{{ $customer->name }}" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="small mb-1">Email address</label>
                                                        <input class="form-control" type="email" name="email"
                                                            placeholder="Enter email address" value="{{ $customer->email }}"
                                                            readonly />
                                                        @if ($customer->email_verified_at)
                                                            <span class="badge badge-success"> Verified</span>
                                                        @else
                                                            <span class="badge badge-danger"> Not Verified</span>
                                                        @endif
                                                        <a href="javascript:;" class="badge badge-primary" data-toggle="modal"
                                                            data-target="#updateEmailModel"> Change/Verify Email</a>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="small mb-1">Phone number</label>
                                                        <input class="form-control" type="text" name="phone"
                                                            placeholder="Enter phone number" value="{{ $customer->phone }}"
                                                            readonly />
                                                        @if ($customer->phone_verified_at)
                                                            <span class="badge badge-success"> Verified</span>
                                                        @else
                                                            <span class="badge badge-danger">Not Verified</span>
                                                        @endif
                                                        <a href="javascript:;" class="badge badge-primary" data-toggle="modal"
                                                            data-target="#updateMobileModel"> Change/Verify Mobile</a>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="small mb-1">Birthday</label>
                                                        <input class="form-control" type="date" name="dob"
                                                            placeholder="DD/MM/YYYY"
                                                            value="{{ $customer->dob ?? date('Y-m-d') }}" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <div class="row gx-3 mb-3">
                                            <div class="col-md-4">
                                                <label class="small mb-1">Father Name</label>
                                                <input class="form-control" type="text" name="father_name"
                                                    placeholder="Enter Father Name" value="{{ $customer->father_name }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Gender</label>
                                                <select name="gender" class="form-control">
                                                    <option disabled>Select Gender</option>
                                                    <option {{ $customer->gender == 'male' ? 'selected' : '' }} value="male">
                                                        Male
                                                    </option>
                                                    <option {{ $customer->gender == 'female' ? 'selected' : '' }}
                                                        value="female">Female
                                                    </option>
                                                    <option {{ $customer->gender == 'other' ? 'selected' : '' }}
                                                        value="other">Other
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Support Pin</label>
                                                <input class="form-control" type="text" name="support_pin"
                                                    placeholder="Enter Support Pin" value="{{ $customer->support_pin }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Pin Code</label>
                                                <input class="form-control getPinCodeDetails" type="text" name="pincode"
                                                    placeholder="Enter pincode" value="{{ $customer->pincode }}"
                                                    maxlength="6" minlength="6" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Address</label>
                                                <input class="form-control Address" type="text" name="address"
                                                    placeholder="Enter Address" value="{{ $customer->address }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Landmark</label>
                                                <input class="form-control Landmark" type="text" name="landmark"
                                                    placeholder="Enter landmark" value="{{ $customer->landmark }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Tehsil</label>
                                                <input class="form-control Tehsil" type="text" name="tehsil"
                                                    placeholder="Enter tehsil" value="{{ $customer->tehsil }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1"> District </label>
                                                <input class="form-control District" type="text" name="district"
                                                    placeholder="Enter district" value="{{ $customer->district }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">City</label>
                                                <input class="form-control City" type="text" name="city"
                                                    placeholder="Enter city" value="{{ $customer->city }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">State</label>
                                                <input class="form-control State" type="text" name="state"
                                                    placeholder="Enter state" value="{{ $customer->state }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Customer Category</label>
                                                <select name="categoryid" class="form-control">
                                                    <option selected disabled>Select Category</option>
                                                    @foreach ($customerCategory as $custcat)
                                                        <option value="{{ $custcat->id }}"
                                                            {{ $custcat->id == $customer->categoryid ? 'selected' : '' }}>
                                                            {{ $custcat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Institute </label>
                                                <select name="instituteid" class="form-control">
                                                    <option selected disabled>Select Institute</option>
                                                    @foreach ($institutes as $instute)
                                                        <option value="{{ $instute->id }}"
                                                            {{ $instute->id == $customer->instituteid ? 'selected' : '' }}>
                                                            {{ $instute->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">User Type</label>
                                                <select name="user_type" class="form-control">
                                                    <option {{ $customer->user_type == 'standard' ? 'selected' : '' }}
                                                        value="standard">
                                                        Standard
                                                    </option>
                                                    <option {{ $customer->user_type == 'credit' ? 'selected' : '' }}
                                                        value="credit">
                                                        Credit
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Status</label>
                                                <select name="status" class="form-control">
                                                    <option selected disabled> Select Status</option>
                                                    <option {{ $customer->status == 'active' ? 'selected' : '' }}
                                                        value="active"> Active
                                                    </option>
                                                    <option {{ $customer->status == 'inactive' ? 'selected' : '' }}
                                                        value="inactive"> In
                                                        Active</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="small mb-1">About</label>
                                                <textarea name="about" class="form-control" rows="4" placeholder="About Info...">{{ $customer->about }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @can('customer-edit')
                                    <a href="javascript:;" class="btn btn-primary" id="updateProfile">Update</a>
                                @endcan
                            </form>
                        </div>
                        {{-- --------------------------- --}}
                        <div id="sectionSecurity" class="section row d-none">
                            <div class="col-lg-8">
                                <!-- Change password card-->
                                <div class="card mb-4">
                                    <div class="card-header">Change Password</div>
                                    <div class="card-body">
                                        <form id="passwordForm" name="passwordForm">
                                            @csrf
                                            <div class="mb-3">
                                                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                                <label class="small mb-1" for="newPassword">New Password</label>
                                                <input class="form-control" id="newPassword" name="password" type="password"
                                                    placeholder="Enter new password" autocomplete="disabled" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1" for="confirmPassword">Confirm Password</label>
                                                <input class="form-control" id="confirmPassword" name="password_confirmation"
                                                    type="password" placeholder="Confirm new password"
                                                    autocomplete="disabled" />
                                            </div>
                                        </form>
                                        @can('customer-edit')
                                            <a href="javascript:;" class="btn btn-primary" id="updatePassword">Save</a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- --------------------------- --}}
                        <div id="sectionNotifications" class="section row d-none">
                            <div class="col-lg-8">
                                <div class="card card-header-actions mb-4">
                                    <div class="card-header">
                                        Email Notifications
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" id="flexSwitchCheckChecked" type="checkbox"
                                                checked="" />
                                            <label class="form-check-label" for="flexSwitchCheckChecked"></label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form>
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputNotificationEmail">Default notification
                                                    email</label>
                                                <input class="form-control" id="inputNotificationEmail" type="email"
                                                    value="name@example.com" disabled="" />
                                            </div>
                                            <div class="mb-0">
                                                <label class="small mb-2">Choose which types of email updates you
                                                    receive</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkAccountChanges" type="checkbox"
                                                        checked="" />
                                                    <label class="form-check-label" for="checkAccountChanges">Changes made to
                                                        your
                                                        account</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkAccountGroups" type="checkbox"
                                                        checked="" />
                                                    <label class="form-check-label" for="checkAccountGroups">Changes are made
                                                        to
                                                        groups you're part of</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkProductUpdates" type="checkbox"
                                                        checked="" />
                                                    <label class="form-check-label" for="checkProductUpdates">Product updates
                                                        for
                                                        products you've purchased or starred</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkProductNew" type="checkbox"
                                                        checked="" />
                                                    <label class="form-check-label" for="checkProductNew">Information on new
                                                        products
                                                        and services</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkPromotional" type="checkbox" />
                                                    <label class="form-check-label" for="checkPromotional">Marketing and
                                                        promotional
                                                        offers</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" id="checkSecurity" type="checkbox"
                                                        checked="" disabled="" />
                                                    <label class="form-check-label" for="checkSecurity">Security
                                                        alerts</label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- SMS push notifications card-->
                                <div class="card card-header-actions mb-4">
                                    <div class="card-header">
                                        Push Notifications
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" id="smsToggleSwitch" type="checkbox"
                                                checked="" />
                                            <label class="form-check-label" for="smsToggleSwitch"></label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form>
                                            <!-- Form Group (default SMS number)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputNotificationSms">Default SMS
                                                    number</label>
                                                <input class="form-control" id="inputNotificationSms" type="tel"
                                                    value="123-456-7890" disabled="" />
                                            </div>
                                            <!-- Form Group (SMS updates checkboxes)-->
                                            <div class="mb-0">
                                                <label class="small mb-2">Choose which types of push notifications you
                                                    receive</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkSmsComment" type="checkbox"
                                                        checked="" />
                                                    <label class="form-check-label" for="checkSmsComment">Someone comments on
                                                        your
                                                        post</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkSmsShare" type="checkbox" />
                                                    <label class="form-check-label" for="checkSmsShare">Someone shares your
                                                        post</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkSmsFollow" type="checkbox"
                                                        checked="" />
                                                    <label class="form-check-label" for="checkSmsFollow">A user follows your
                                                        account</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" id="checkSmsGroup" type="checkbox" />
                                                    <label class="form-check-label" for="checkSmsGroup">New posts are made in
                                                        groups
                                                        you're part of</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" id="checkSmsPrivateMessage"
                                                        type="checkbox" checked="" />
                                                    <label class="form-check-label" for="checkSmsPrivateMessage">You receive a
                                                        private
                                                        message</label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <!-- Notifications preferences card-->
                                <div class="card">
                                    <div class="card-header">Notification Preferences</div>
                                    <div class="card-body">
                                        <form>
                                            <!-- Form Group (notification preference checkboxes)-->
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" id="checkAutoGroup" type="checkbox"
                                                    checked="" />
                                                <label class="form-check-label" for="checkAutoGroup">Automatically subscribe
                                                    to group
                                                    notifications</label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" id="checkAutoProduct" type="checkbox" />
                                                <label class="form-check-label" for="checkAutoProduct">Automatically subscribe
                                                    to new
                                                    product notifications</label>
                                            </div>
                                            <!-- Submit button-->
                                            <button class="btn btn-danger-soft text-danger">Unsubscribe from all
                                                notifications</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
        @can('customer-edit')
            {{-- All models will be ehre  --}}
            <div class="modal fade" id="updateEmailModel" tabindex="-1" role="dialog" aria-labelledby="updateEmailModel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" id="EmailModelSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Email Address</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="emailEntryForm" name="emailEntryForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label for="name"> Email Address</label>
                                        <input type="text" class="form-control" name="email"
                                            placeholder="email address" value="{{ $customer->email }}">
                                        <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" id="sendEmailFormBtn" href="javascript:;"> Send Mail </a>
                        </div>
                    </div>
                    <div class="modal-content d-none" id="EmailOTPSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Email Address</h5>
                        </div>
                        <div class="modal-body">
                            <form id="emailOTPForm" name="emailOTPForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label for="name"> OTP</label>
                                        <input type="text" class="form-control" name="otp">
                                        <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-primary" id="ResendBtnEmail" href="javascript:;"> Resend Mail </a>
                            <a class="btn btn-primary" id="verifyOtpBtn" href="javascript:;"> Verify </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="updateMobileModel" tabindex="-1" role="dialog"
                aria-labelledby="updateMobileModel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" id="MobileModelSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Mobile Number</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="mobileEntryForm" name="mobileEntryForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label for="name"> Mobile Number</label>
                                        <input type="text" class="form-control" name="phone"
                                            placeholder="mobile number" value="{{ $customer->phone }}">
                                        <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" id="sendMobileFormBtn" href="javascript:;"> Send OTP </a>
                        </div>
                    </div>
                    <div class="modal-content d-none" id="MobileOTPSection">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Mobile Number</h5>
                        </div>
                        <div class="modal-body">
                            <form id="mobileOTPForm" name="mobileOTPForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label for="name"> OTP</label>
                                        <input type="text" class="form-control" name="otp">
                                        <input type="hidden" name="user_id" value="{{ $customer->id }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-primary" id="ResendBtnMobile" href="javascript:;"> Resend OTP </a>
                            <a class="btn btn-primary" id="verifyMobileOtpBtn" href="javascript:;"> Verify </a>
                        </div>
                    </div>
                </div>
            </div>
            {{-- All models will be ehre  --}}
        @endcan

    </div>
@endsection
@section('push_script')
    <script defer src="{{ URL::asset('admin/js/jquery-ui.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            function handleNavigation(sectionToShow, btnToActivate) {
                $('.section').addClass('d-none');
                $(`#${sectionToShow}`).removeClass('d-none');
                $('#selectedPageTag').text(sectionToShow.replace('section', ''));
                $('.NewNavButtomn').removeClass('btn-primary').addClass('btn-dark');
                $(`#${btnToActivate}`).removeClass('btn-dark').addClass('btn-primary');
            }
            $('#btnProfile').click(function(event) {
                event.preventDefault();
                handleNavigation('sectionProfile', 'btnProfile');
            });
            $('#btnSecurity').click(function(event) {
                event.preventDefault();
                handleNavigation('sectionSecurity', 'btnSecurity');
            });
            $('#btnNotifications').click(function(event) {
                event.preventDefault();
                handleNavigation('sectionNotifications', 'btnNotifications');
            });
        });
        // -------------------------------------------- end functional jQuery here
        $('#updateProfile').click(function() {
            var formdataValue = $('#UpdateFormData')[0];
            var formData = new FormData(formdataValue);
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer.update_profile') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // ------------------------------------------- end jQuery code here
        $('#updatePassword').click(function() {
            formdata = $('#passwordForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer.update_password') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // ----------- Email Updateding jQuery start
        $('#sendEmailFormBtn').click(function() {
            var formData = $('#emailEntryForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer.sendVerificationEmail') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        $('#EmailModelSection').addClass('d-none');
                        $('#EmailOTPSection').removeClass('d-none');
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -------------------------------------------
        $('#ResendBtnEmail').click(function() {
            $('#EmailModelSection').removeClass('d-none');
            $('#EmailOTPSection').addClass('d-none');
        });
        // -------------------------------------------
        $('#verifyOtpBtn').click(function() {
            var formData = $('#emailOTPForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer.verifyOTPforEMail') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -------------------------------  end jQuery code here
        // ----------- Mobile Updateding jQuery start
        $('#sendMobileFormBtn').click(function() {
            var formData = $('#mobileEntryForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer.sendVerificationmobile') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        $('#MobileModelSection').addClass('d-none');
                        $('#MobileOTPSection').removeClass('d-none');
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -------------------------------------------
        $('#ResendBtnMobile').click(function() {
            $('#MobileModelSection').removeClass('d-none');
            $('#MobileOTPSection').addClass('d-none');
        });
        // -------------------------------------------
        $('#verifyMobileOtpBtn').click(function() {
            var formData = $('#mobileOTPForm').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.customer.verifyOTPforMobile') }}",
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
        // -------------------------------  end jQuery code here
        $(document).ready(function() {
            $(".getPinCodeDetails").autocomplete({
                source: "{{ route('admin.fetchPinCode') }}",
                minLength: 5,
                select: function(event, ui) {
                    console.log(ui);
                    console.log(ui.item);
                    $(".getPinCodeDetails").val(ui.item.Pincode);
                    $(".Address").val(ui.item.Name);
                    $(".Landmark").val(ui.item.Block);
                    $(".Tehsil").val(ui.item.District);
                    $(".District").val(ui.item.District);
                    $(".City").val(ui.item.District);
                    $(".State").val(ui.item.State);
                    return false; // Prevent default action
                },
                search: function(event, ui) {
                    $(".fullpage-loader").show();
                },
                response: function(event, ui) {
                    $(".fullpage-loader").hide();
                }
            }).autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div>" + item.Name + " / " + item.State + " / " + item.Pincode + "</div>")
                    .appendTo(ul);
            };
        });
    </script>
@endsection
