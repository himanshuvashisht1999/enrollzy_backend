@extends('admin.layouts.master')

@section('title', 'Edit Consultant')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card { border-radius: 12px; border: none; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); }
        .section-head { font-size: 0.9rem; font-weight: 700; color: #4e73df; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem; border-bottom: 2px solid #4e73df; display: inline-block; padding-bottom: 3px; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #555; }
        .photo-preview-wrapper { width: 120px; height: 120px; border: 2px dashed #ddd; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; background: #f9f9f9; position: relative; }
        .photo-preview-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .doc-upload-item { background: #fcfcfc; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 10px; }
        .existing-doc { font-size: 0.7rem; color: #4e73df; display: block; margin-top: 4px; text-decoration: none; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <form action="{{ route('admin.consultants.update', encrypt($consultant->id)) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">Edit Consultant: {{ $consultant->full_name }}</h5>
                <span class="badge bg-soft-primary text-primary rounded-pill px-3">ID: {{ $consultant->consultant_id }}</span>
            </div>
            <div class="card-body p-4">
                
                <!-- 1. Basic Details -->
                <div class="section-head">1. Basic Consultant Details</div>
                <div class="row g-3 mb-5">
                    <div class="col-md-2 text-center">
                        <div class="photo-preview-wrapper mx-auto mb-2" onclick="$('#profile_photo').click()">
                            @if($consultant->image)
                                <img id="photo_preview" src="{{ asset($consultant->image) }}">
                                <div id="photo_placeholder" style="display:none;">
                                    <i class="fas fa-camera text-muted mb-1"></i><br>
                                    <small class="text-muted" style="font-size: 0.65rem;">Change Photo</small>
                                </div>
                            @else
                                <div id="photo_placeholder" class="text-center">
                                    <i class="fas fa-camera text-muted mb-1"></i><br>
                                    <small class="text-muted" style="font-size: 0.65rem;">Profile Photo</small>
                                </div>
                                <img id="photo_preview" style="display:none; width: 100%; height: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <input type="file" name="image" id="profile_photo" class="d-none" accept="image/*">
                    </div>
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $consultant->full_name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Business Name</label>
                                <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $consultant->business_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $consultant->email) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mobile Number *</label>
                                <input type="number" name="phone" class="form-control" value="{{ old('phone', $consultant->phone) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Alternate Mobile</label>
                                <input type="number" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $consultant->alternate_mobile) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select</option>
                                    <option value="Male" {{ $consultant->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $consultant->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ $consultant->gender == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="{{ old('dob', $consultant->dob) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password (Leave blank to keep current)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Business Details -->
                <div class="section-head">2. Business Details</div>
                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <label class="form-label">Consultant Type</label>
                        <select name="consultant_type" class="form-select">
                            <option value="">Select Type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->name }}" {{ $consultant->consultant_type == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                            @if($types->isEmpty())
                                @foreach(['Individual', 'Agency', 'Freelancer', 'Institute'] as $type)
                                    <option value="{{ $type }}" {{ $consultant->consultant_type == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GST Registered?</label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_gst_registered" id="gst_yes" value="1" {{ $consultant->is_gst_registered ? 'checked' : '' }}>
                                <label class="form-check-label" for="gst_yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_gst_registered" id="gst_no" value="0" {{ !$consultant->is_gst_registered ? 'checked' : '' }}>
                                <label class="form-check-label" for="gst_no">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3" id="gst_number_container" style="{{ $consultant->is_gst_registered ? '' : 'display:none;' }}">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $consultant->gst_number) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PAN Number *</label>
                        <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $consultant->pan_number) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aadhaar Number</label>
                        <input type="number" name="aadhaar_number" class="form-control" value="{{ old('aadhaar_number', $consultant->aadhaar_number) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" name="years_of_experience" class="form-control" value="{{ old('years_of_experience', $consultant->years_of_experience) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Team Size</label>
                        <input type="number" name="team_size" class="form-control" value="{{ old('team_size', $consultant->team_size) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pincode</label>
                        <input type="number" name="pincode" id="pincode" class="form-control" value="{{ old('pincode', $consultant->pincode) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" id="state" class="form-control" value="{{ old('state', $consultant->state) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $consultant->city) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Office Address</label>
                        <input type="text" name="office_address" class="form-control" value="{{ old('office_address', $consultant->office_address) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="{{ old('website', $consultant->website) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">LinkedIn Profile</label>
                        <input type="url" name="linkedin_profile" class="form-control" value="{{ old('linkedin_profile', $consultant->linkedin_profile) }}">
                    </div>
                </div>

                <!-- 3. Specialization -->
                <div class="section-head">3. Specialization & Expertise</div>
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label">Main Category</label>
                        <select name="category_id" id="main_category" class="form-select select2">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $consultant->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="sub_category_container" style="{{ $consultant->sub_category_id ? '' : 'display:none;' }}">
                        <label class="form-label">Sub Category</label>
                        <select name="sub_category_id" id="sub_category" class="form-select select2">
                            <option value="">Select Sub Category</option>
                            @if($consultant->sub_category)
                                <option value="{{ $consultant->sub_category_id }}" selected>{{ $consultant->sub_category->name }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4" id="sub_sub_category_container" style="{{ $consultant->sub_sub_category_id ? '' : 'display:none;' }}">
                        <label class="form-label">Sub Sub Category</label>
                        <select name="sub_sub_category_id" id="sub_sub_category" class="form-select select2">
                            <option value="">Select Sub-Sub Category</option>
                            @if($consultant->sub_sub_category)
                                <option value="{{ $consultant->sub_sub_category_id }}" selected>{{ $consultant->sub_sub_category->name }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expertise Level</label>
                        <select name="expertise_level" class="form-select">
                            @foreach(['Beginner', 'Intermediate', 'Expert'] as $level)
                                <option value="{{ $level }}" {{ $consultant->expertise_level == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Preferred Universities</label>
                        <select name="preferred_universities[]" class="form-select select2" multiple>
                            @foreach($consultant->preferred_universities ?? [] as $uni)
                                <option value="{{ $uni }}" selected>{{ $uni }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preferred Courses</label>
                        <select name="preferred_courses[]" class="form-select select2" multiple>
                            @foreach($consultant->preferred_courses ?? [] as $course)
                                <option value="{{ $course }}" selected>{{ $course }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modes of Study</label>
                        <select name="preferred_modes_of_study[]" class="form-select select2" multiple>
                            @foreach(['Online', 'Regular', 'Distance', 'Hybrid'] as $mode)
                                <option value="{{ $mode }}" {{ in_array($mode, $consultant->preferred_modes_of_study ?? []) ? 'selected' : '' }}>{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 4. Lead Source -->
                <div class="section-head">4. Lead Source Information</div>
                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="generates_own_leads" id="gen_leads" {{ $consultant->generates_own_leads ? 'checked' : '' }}><label class="form-check-label" for="gen_leads">Generates Own Leads</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="requires_company_leads" id="req_leads" {{ $consultant->requires_company_leads ? 'checked' : '' }}><label class="form-check-label" for="req_leads">Requires Company Leads</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="runs_ads" id="runs_ads" {{ $consultant->runs_ads ? 'checked' : '' }}><label class="form-check-label" for="runs_ads">Runs Ads</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="has_counseling_office" id="has_office" {{ $consultant->has_counseling_office ? 'checked' : '' }}><label class="form-check-label" for="has_office">Has Counseling Office</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="walk_in_students" id="walk_ins" {{ $consultant->walk_in_students ? 'checked' : '' }}><label class="form-check-label" for="walk_ins">Walk-in Students</label></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Approx Leads Per Month</label>
                        <input type="number" name="approx_leads_per_month" class="form-control" value="{{ $consultant->approx_leads_per_month }}">
                    </div>
                </div>

                <!-- 5. Geographic -->
                <div class="section-head">5. Geographic Working Area</div>
                <div class="row g-3 mb-5">
                    <div class="col-md-5">
                        <label class="form-label">Working States</label>
                        <select name="working_states[]" class="form-select select2" multiple>
                            @foreach($consultant->working_states ?? [] as $state) <option value="{{ $state }}" selected>{{ $state }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Working Cities</label>
                        <select name="working_cities[]" class="form-select select2" multiple>
                            @foreach($consultant->working_cities ?? [] as $city) <option value="{{ $city }}" selected>{{ $city }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 pt-4">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="can_handle_pan_india" id="pan_india" {{ $consultant->can_handle_pan_india ? 'checked' : '' }}><label class="form-check-label fw-bold" for="pan_india">Pan India?</label></div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Languages Known</label>
                        <select name="languages_known[]" class="form-select select2" multiple>
                            @foreach(['English', 'Hindi', 'Bengali', 'Marathi', 'Telugu', 'Tamil', 'Gujarati', 'Urdu', 'Kannada', 'Odia', 'Punjabi', 'Malayalam'] as $lang)
                                <option value="{{ $lang }}" {{ in_array($lang, $consultant->languages_known ?? []) ? 'selected' : '' }}>{{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 6. Bank Details -->
                <div class="section-head">6. Bank & Payout Details</div>
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label">Account Holder Name</label>
                        <input type="text" name="account_holder_name" class="form-control" value="{{ $consultant->account_holder_name }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="{{ $consultant->bank_name }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" value="{{ $consultant->account_number }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control" value="{{ $consultant->ifsc_code }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">UPI ID</label>
                        <input type="text" name="upi_id" class="form-control" value="{{ $consultant->upi_id }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">QR Code</label>
                        @if($consultant->qr_code_upload) <a href="{{ asset($consultant->qr_code_upload) }}" target="_blank" class="existing-doc">View Existing QR</a> @endif
                        <input type="file" name="qr_code_upload" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cancelled Cheque</label>
                        @if($consultant->cancelled_cheque_upload) <a href="{{ asset($consultant->cancelled_cheque_upload) }}" target="_blank" class="existing-doc">View Existing</a> @endif
                        <input type="file" name="cancelled_cheque_upload" class="form-control">
                    </div>
                </div>

                <!-- 7. Documents -->
                <div class="section-head">7. Documents & Verification</div>
                <div class="row g-3 mb-5">
                    @foreach([
                        'aadhaar_upload' => 'Aadhaar Card',
                        'pan_upload' => 'PAN Card',
                        'gst_certificate_upload' => 'GST Certificate',
                        'business_registration_upload' => 'Business Registration',
                        'visiting_card_upload' => 'Visiting Card',
                        'msme_upload' => 'MSME Certificate',
                        'mou_upload' => 'MOU Document'
                    ] as $field => $label)
                        <div class="col-md-4">
                            <div class="doc-upload-item">
                                <label class="form-label">{{ $label }}</label>
                                @if($consultant->$field) <a href="{{ asset($consultant->$field) }}" target="_blank" class="existing-doc">View Existing</a> @endif
                                <input type="file" name="{{ $field }}" class="form-control form-control-sm">
                            </div>
                        </div>
                    @endforeach
                    <div class="col-md-4">
                        <div class="doc-upload-item">
                            <label class="form-label">Office Photos</label>
                            @if($consultant->office_photos) 
                                <div class="d-flex gap-1 mb-2">
                                    @foreach($consultant->office_photos as $photo)
                                        <img src="{{ asset($photo) }}" style="width: 30px; height: 30px; border-radius: 4px;">
                                    @endforeach
                                </div>
                            @endif
                            <input type="file" name="office_photos[]" class="form-control form-control-sm" multiple>
                        </div>
                    </div>
                </div>

                <!-- 8. CRM Access -->
                <div class="section-head">8. CRM Access & Permissions</div>
                <div class="row g-3 bg-light p-3 rounded-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Consultant Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $st)
                                <option value="{{ $st->name }}" {{ $consultant->status == $st->name ? 'selected' : '' }}>{{ ucfirst($st->name) }}</option>
                            @endforeach
                            @if($statuses->isEmpty())
                                @foreach(['active', 'pending', 'inactive', 'blocked', 'rejected'] as $st)
                                    <option value="{{ $st }}" {{ $consultant->status == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6" id="status_reason_container" style="{{ in_array(strtolower($consultant->status), ['inactive', 'rejected']) ? '' : 'display:none;' }}">
                        <label class="form-label">Status Reason</label>
                        <textarea name="status_reason" class="form-control" rows="2" placeholder="Enter reason">{{ old('status_reason', $consultant->status_reason) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Access Level</label>
                        <select name="access_level" class="form-select">
                            @foreach($access_levels as $al)
                                <option value="{{ $al->name }}" {{ $consultant->access_level == $al->name ? 'selected' : '' }}>{{ $al->name }}</option>
                            @endforeach
                            @if($access_levels->isEmpty())
                                @foreach(['Standard', 'Premium', 'Admin'] as $al)
                                    <option value="{{ $al }}" {{ $consultant->access_level == $al ? 'selected' : '' }}>{{ $al }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Lead Visibility</label>
                        <select name="lead_visibility" class="form-select">
                            @foreach($lead_visibilities as $lv)
                                <option value="{{ $lv->name }}" {{ $consultant->lead_visibility == $lv->name ? 'selected' : '' }}>{{ $lv->name }}</option>
                            @endforeach
                            @if($lead_visibilities->isEmpty())
                                @foreach(['Own' => 'Own Leads Only', 'Assigned' => 'Assigned Leads Only', 'All' => 'All Organization Leads'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $consultant->lead_visibility == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3 pt-4">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="lead_assignment_allowed" id="assign_leads" {{ $consultant->lead_assignment_allowed ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="assign_leads">Lead Assignment Allowed</label>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer bg-white py-4 text-center border-0">
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill">UPDATE CONSULTANT RECORD</button>
                <a href="{{ route('admin.consultants.index') }}" class="btn btn-link text-muted ms-3">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%', tags: true });

        // Photo Preview
        $('#profile_photo').on('change', function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('#photo_preview').attr('src', event.target.result).show();
                    $('#photo_placeholder').hide();
                }
                reader.readAsDataURL(file);
            }
        });

        // Pincode Lookup
        $('#pincode').on('keyup', function() {
            let pin = $(this).val();
            if (pin.length === 6) {
                $.getJSON(`https://api.postalpincode.in/pincode/${pin}`, function(res) {
                    if (res[0].Status === "Success") {
                        let post = res[0].PostOffice[0];
                        $('#state').val(post.State);
                        $('#city').val(post.District);
                    }
                });
            }
        });

        // Dynamic Sub-Categories
        $('#main_category').on('change', function() {
            let parentId = $(this).val();
            $('#sub_category').html('<option value="">Select Sub Category</option>');
            $('#sub_category_container').hide();
            $('#sub_sub_category_container').hide();
            
            if (parentId) {
                $.get(`{{ route('admin.consultants.sub-categories') }}?parent_id=${parentId}`, function(res) {
                    if (res.data.length > 0) {
                        let html = '<option value="">Select Sub Category</option>';
                        res.data.forEach(cat => { html += `<option value="${cat.id}">${cat.name}</option>`; });
                        $('#sub_category').html(html);
                        $('#sub_category_container').show();
                    }
                });
            }
        });

        $('#sub_category').on('change', function() {
            let parentId = $(this).val();
            $('#sub_sub_category').html('<option value="">Select Sub-Sub Category</option>');
            $('#sub_sub_category_container').hide();
            
            if (parentId) {
                $.get(`{{ route('admin.consultants.sub-categories') }}?parent_id=${parentId}`, function(res) {
                    if (res.data.length > 0) {
                        let html = '<option value="">Select Sub-Sub Category</option>';
                        res.data.forEach(cat => { html += `<option value="${cat.id}">${cat.name}</option>`; });
                        $('#sub_sub_category').html(html);
                        $('#sub_sub_category_container').show();
                    }
                });
            }
        });

        // GST Number Toggle
        $('input[name="is_gst_registered"]').on('change', function() {
            if ($(this).val() == '1') {
                $('#gst_number_container').fadeIn();
            } else {
                $('#gst_number_container').fadeOut();
                $('input[name="gst_number"]').val('');
            }
        });

        // Status Reason Toggle
        $('select[name="status"]').on('change', function() {
            let statusText = $(this).find('option:selected').text().toLowerCase();
            if (statusText.includes('inactive') || statusText.includes('rejected')) {
                $('#status_reason_container').fadeIn();
            } else {
                $('#status_reason_container').fadeOut();
                $('[name="status_reason"]').val('');
            }
        });
    });
</script>
@endpush

