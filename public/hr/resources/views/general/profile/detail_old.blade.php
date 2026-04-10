@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h5 class="page-header-title d-flex">
                            <div class="page-header-icon">
                                <i class="fas fa-user fa-sm fa-fw mx-3"></i>
                            </div>
                            Account Settings&nbsp; - &nbsp; <span id="selectedPageTag"> Profile </span>
                        </h5>
                    </div>
                </div>
            </div>
        </header>
        <!-- Main page content-->
        <div class="px-4 mt-4">
            <!-- Account page navigation-->
            <nav class="nav nav-borders">
                <a class="nav-link active ms-0" href="javascript:;" id="btnProfile">Profile</a>
                <a class="nav-link" href="javascript:;" id="btnSecurity">Security</a>
                <!-- <a class="nav-link" href="javascript:;" id="btnNotifications">Notifications</a> -->
            </nav>
            <hr class="mt-0 mb-4" />
            <div class="row" id="sectionProfile">
                <div class="col-xl-4">
                    <!-- Profile picture card-->
                    <div class="card mb-4 mb-xl-0">
                        <div class="card-header">Profile Picture</div>
                        <div class="card-body text-center">
                            <!-- Profile picture image-->
                            <img class="img-account-profile rounded-circle mb-2"
                                src="assets/img/illustrations/profiles/profile-1.png" alt="" />
                            <!-- Profile picture help block-->
                            <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                            <!-- Profile picture upload button-->
                            <button class="btn btn-primary" type="button">Upload new image</button>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <!-- Account details card-->
                    <div class="card mb-4">
                        <div class="card-header">Account Details</div>
                        <div class="card-body">
                            <form name="UpdateFormData" id="UpdateFormData">
                                @csrf
                                <div class="row gx-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="small mb-1">Username (this will use in your login
                                            detials as username)</label>
                                        <input class="form-control" type="text" name="username"
                                            placeholder="Enter your username" value="{{ $profile->username }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1">Full name</label>
                                        <input class="form-control" type="text" name="name"
                                            placeholder="Enter your last name" value="{{ $profile->name }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1">Email address</label>
                                        <input class="form-control" type="email" name="email"
                                            placeholder="Enter your email address" value="{{ $profile->email }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1">Phone number</label>
                                        <input class="form-control" type="text" name="phone"
                                            placeholder="Enter your phone number" value="{{ $profile->phone }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1">Birthday</label>
                                        <input class="form-control" type="date" name="dob" name="birthday"
                                            placeholder="DD/MM/YYYY" value="{{ $profile->dob ?? date('Y-m-d') }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1">Gender</label>
                                        <select name="gender" class="form-control">
                                            <option disabled>Select Gender</option>
                                            <option {{ $profile->gender == 'male' ? 'selected' : '' }} value="male">Male
                                            </option>
                                            <option {{ $profile->gender == 'female' ? 'selected' : '' }} value="female">
                                                Female</option>
                                            <option {{ $profile->gender == 'other' ? 'selected' : '' }} value="other">Other
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <a href="javascript:;" class="btn btn-primary" id="updateProfile">Save changes</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            {{-- --------------------------- --}}
            <div class="row d-none" id="sectionSecurity">
                <div class="col-lg-8">
                    <!-- Change password card-->
                    <div class="card mb-4">
                        <div class="card-header">Change Password</div>
                        <div class="card-body">
                            <form id="passwordForm" name="passwordForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="small mb-1" for="currentPassword">Current Password</label>
                                    <input class="form-control" name="currentPassword" type="password"
                                        placeholder="Enter current password" autocomplete="disabled" />
                                </div>
                                <div class="mb-3">
                                    <label class="small mb-1" for="newPassword">New Password</label>
                                    <input class="form-control" id="newPassword" name="password" type="password"
                                        placeholder="Enter new password" autocomplete="disabled" />
                                </div>
                                <div class="mb-3">
                                    <label class="small mb-1" for="confirmPassword">Confirm Password</label>
                                    <input class="form-control" id="confirmPassword" name="password_confirmation"
                                        type="password" placeholder="Confirm new password" autocomplete="disabled" />
                                </div>
                            </form>
                            <a href="javascript:;" class="btn btn-primary" id="updatePassword">Save</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <!-- Two factor authentication card-->
                    <div class="card mb-4">
                        <div class="card-header">Two-Factor Authentication</div>
                        <div class="card-body">
                            <p>Add another level of security to your account by enabling two-factor authentication. We will
                                send you a text message to verify your login attempts on unrecognized devices and browsers.
                            </p>
                            <form>
                                <div class="form-check">
                                    <input class="form-check-input" id="twoFactorOn" type="radio" name="twoFactor"
                                        checked="" />
                                    <label class="form-check-label" for="twoFactorOn">On</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" id="twoFactorOff" type="radio" name="twoFactor" />
                                    <label class="form-check-label" for="twoFactorOff">Off</label>
                                </div>
                                <div class="mt-3">
                                    <label class="small mb-1" for="twoFactorSMS">SMS Number</label>
                                    <input class="form-control" id="twoFactorSMS" type="tel"
                                        placeholder="Enter a phone number" value="555-123-4567" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            {{-- --------------------------- --}}
            
        </div>
        <!-- Other Configuration Setting Row -->
    </div>
@endsection
@section('push_script')
    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function() {
            $('#btnProfile').click(function(event) {
                event.preventDefault();
                $('#sectionProfile').removeClass('d-none');
                $('#selectedPageTag').text('Profile');
                $('#sectionSecurity, #sectionNotifications').addClass('d-none');
            });

            $('#btnSecurity').click(function(event) {
                event.preventDefault();
                $('#sectionSecurity').removeClass('d-none');
                $('#selectedPageTag').text('Security');
                $('#sectionProfile, #sectionNotifications').addClass('d-none');
            });

            $('#btnNotifications').click(function(event) {
                event.preventDefault();
                $('#sectionNotifications').removeClass('d-none');
                $('#selectedPageTag').text('Notifications');
                $('#sectionProfile, #sectionSecurity').addClass('d-none');
            });
        });
        // -------------------------------------------- end functional jQuery here
        $('#updateProfile').click(function() {
            formdata = $('#UpdateFormData').serializeArray();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.profile.update_profile') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        window.location.reload();
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
                url: "{{ route('admin.profile.update_password') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.message, "Success");
                        window.location.reload();
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        });
    </script>
@endsection
