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
                <a class="nav-link" href="javascript:;" id="attendance_photo">Attendance Photo</a>
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
            <div class="row d-none" id="sectionAttandance">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">Capture Attendance Image</div>
                        <div class="card-body">
                            <!-- Camera UI -->
                            <div id="cameraWrapper">
                                <video id="attendanceVideo" autoplay playsinline style="width:100%; max-height:320px; background:#000; display:none;"></video>
                                <canvas id="attendanceCanvas" style="display:none;"></canvas>

                                <div id="cameraControls" class="mt-3 text-center">
                                    <button id="startCameraBtn" class="btn btn-outline-primary">Open Camera</button>
                                    <button id="captureBtn" class="btn btn-primary" style="display:none;">Capture</button>
                                    <button id="retakeBtn" class="btn btn-secondary" style="display:none;">Retake</button>
                                    <button id="uploadCapturedBtn" class="btn btn-success" style="display:none;">Upload</button>
                                </div>

                                <div id="attendancePreview" class="mt-3" style="display:none;">
                                    <label class="small mb-1">Preview</label>
                                    <div>
                                        <img id="attendancePreviewImg" src="" alt="preview" class="rounded shadow-sm" style="max-width:200px; max-height:200px; object-fit:cover;">
                                    </div>
                                </div>

                                <div id="cameraMessage" class="small text-muted mt-2"></div>

                                <!-- Hidden form for graceful fallback / server route reference -->
                                <form id="attendanceFormFallback" style="display:none" action="{{ route('admin.profile.uploadAttendanceImage') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="image" id="attendance_image_fallback" accept="image/*">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">Uploaded Attendance Images</div>
                        <div class="card-body" id="attendanceGallery">
                            @if(isset($attendance_images) && count($attendance_images) > 0)
                                <div class="row" id="attendanceGalleryRow">
                                    @foreach($attendance_images as $image)
                                        <div class="col-6 mb-3 text-center attendance-item" id="attendance-item-{{ $image->id }}">
                                            <img src="{{ asset('assets/user_attendance/' . $image->image) }}"
                                                class="img-fluid rounded shadow-sm"
                                                alt="Attendance Image"
                                                style="height:100px; width:100px; object-fit:cover;">
                                            <div class="small text-muted mt-1">{{ $image->created_at->format('d M Y') }}</div>
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-danger btn-delete-attendance" data-id="{{ $image->id }}">Delete</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center" id="attendanceEmptyText">No attendance images uploaded yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>



            
        </div>
        <!-- Other Configuration Setting Row -->
    </div>
@endsection
@section('push_script')
<script>
$(function() {
    const video = document.getElementById('attendanceVideo');
    const canvas = document.getElementById('attendanceCanvas');
    const startBtn = $('#startCameraBtn');
    const captureBtn = $('#captureBtn');
    const retakeBtn = $('#retakeBtn');
    const uploadBtn = $('#uploadCapturedBtn');
    const previewWrap = $('#attendancePreview');
    const previewImg = $('#attendancePreviewImg');
    const cameraMessage = $('#cameraMessage');

    let stream = null;
    let capturedBlob = null;

    function showMessage(msg, isError = false) {
        cameraMessage.text(msg);
        cameraMessage.toggleClass('text-danger', isError);
    }

    async function openCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
            video.srcObject = stream;
            await video.play();
            $(video).show();
            captureBtn.show();
            startBtn.hide();
            showMessage('Camera started. Position your face and press Capture.');
        } catch (err) {
            console.error('Camera open error', err);
            showMessage('Unable to open camera. Use the file upload fallback.', true);
            // show fallback file input
            $('#attendance_image_fallback').parent().show();
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
        try { video.pause(); } catch(e) {}
        video.srcObject = null;
        $(video).hide();
    }

    function captureFrame() {
        // draw current video frame to canvas and convert to blob
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // show preview (dataURL)
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        previewImg.attr('src', dataUrl);
        previewWrap.show();
        retakeBtn.show();
        uploadBtn.show();
        captureBtn.hide();

        // convert dataURL to blob for upload
        const arr = dataUrl.split(','), mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) { u8arr[n] = bstr.charCodeAt(n); }
        capturedBlob = new Blob([u8arr], { type: mime });
    }

    function retake() {
        capturedBlob = null;
        previewWrap.hide();
        retakeBtn.hide();
        uploadBtn.hide();
        captureBtn.show();
        showMessage('Position face and capture again.');
    }

    async function uploadCaptured() {
        if (!capturedBlob) {
            toastr["error"]('No captured image to upload', 'Error');
            return;
        }

        const fd = new FormData();
        fd.append('_token', $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val());
        // append as 'image' so your controller receives same input
        fd.append('image', capturedBlob, 'attendance_' + Date.now() + '.jpg');

        uploadBtn.prop('disabled', true).text('Uploading...');
        showMessage('Uploading image...');

        try {
            const res = await $.ajax({
                url: "{{ route('admin.profile.uploadAttendanceImage') }}",
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
            });

            if (res.status == 1) {
                toastr["success"](res.message || 'Uploaded', "Success");
                // reset UI
                retake();
                stopCamera();
                startBtn.show();
                // reload gallery area
                $('#attendanceGallery').load(location.href + " #attendanceGallery > *");
            } else {
                toastr["error"](res.message || 'Upload failed', "Error");
            }
        } catch (err) {
            console.error('Upload error', err);
            toastr["error"]("Something went wrong while uploading.", "Error");
        } finally {
            uploadBtn.prop('disabled', false).text('Upload');
            showMessage('');
        }
    }

    // Events
    startBtn.on('click', function(e) {
        e.preventDefault();
        openCamera();
    });

    captureBtn.on('click', function(e) {
        e.preventDefault();
        captureFrame();
    });

    retakeBtn.on('click', function(e) {
        e.preventDefault();
        retake();
    });

    uploadBtn.on('click', function(e) {
        e.preventDefault();
        uploadCaptured();
    });

    // Fallback file input: upload selected file using same AJAX call
    $(document).on('change', '#attendance_image_fallback', function(e) {
        const file = this.files && this.files[0];
        if (!file) return;
        // perform same checks as before
        if (file.size > 5 * 1024 * 1024) {
            toastr["error"]("File too large. Max 5 MB.", "Error");
            $(this).val('');
            return;
        }
        // show preview
        const reader = new FileReader();
        reader.onload = function(ev) {
            previewImg.attr('src', ev.target.result);
            previewWrap.show();
            uploadBtn.show();
            retakeBtn.show();
            captureBtn.hide();
            startBtn.hide();
        };
        reader.readAsDataURL(file);

        // convert to blob and set capturedBlob for upload
        capturedBlob = file;
    });

    // Delete handler (reuse your existing AJAX delete logic)
    $(document).on('click', '.btn-delete-attendance', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this image?')) return;
        $.ajax({
            url: "{{ route('admin.profile.deleteAttendanceImage') }}",
            type: 'POST',
            data: { id: id, _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.status == 1) {
                    toastr["success"](res.message || 'Deleted', "Success");
                    $('#attendance-item-' + id).remove();
                    if ($('#attendanceGalleryRow').children().length === 0) {
                        $('#attendanceGallery').html('<p class="text-muted text-center" id="attendanceEmptyText">No attendance images uploaded yet.</p>');
                    }
                } else {
                    toastr["error"](res.message || 'Delete failed', "Error");
                }
            },
            error: function() {
                toastr["error"]("Something went wrong while deleting.", "Error");
            }
        });
    });

    // clean up when user navigates away from Attendance section
    // optional: stop camera if user switches tabs in that page
    $(document).on('click', '#btnProfile, #btnSecurity, #attendance_photo', function(){
        // When user switches away from attendance, stop camera to free webcam
        stopCamera();
        $('#attendancePreview').hide();
        retake();
        startBtn.show();
    });

});
</script>

    <!-- Page level custom scripts -->

    <script>
        $(document).ready(function() {
            $('#btnProfile').click(function(event) {
                event.preventDefault();
                $('#sectionProfile').removeClass('d-none');
                $('#selectedPageTag').text('Profile');
                $('#sectionSecurity, #sectionNotifications,#sectionAttandance').addClass('d-none');
            });

            $('#btnSecurity').click(function(event) {
                event.preventDefault();
                $('#sectionSecurity').removeClass('d-none');
                $('#selectedPageTag').text('Security');
                $('#sectionProfile, #sectionNotifications,#sectionAttandance').addClass('d-none');
            });

            $('#btnNotifications').click(function(event) {
                event.preventDefault();
                $('#sectionNotifications').removeClass('d-none');
                $('#selectedPageTag').text('Notifications');
                $('#sectionProfile, #sectionSecurity,#sectionAttandance').addClass('d-none');
            });

            $('#attendance_photo').click(function(event) {
                event.preventDefault();
                $('#sectionAttandance').removeClass('d-none');
                $('#sectionProfile, #sectionSecurity,#sectionNotifications').addClass('d-none');
                $('#selectedPageTag').text('Attendance Photo');
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
