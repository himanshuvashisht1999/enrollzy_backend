{{-- College (organisation type 2): identity, the university it belongs to, and where it is (the main campus
     is created from City / State / Address when the college is saved). Used by the create and edit forms. $organisation
     is set on edit. --}}
@php
    $org = $organisation ?? null;
    $val = fn ($k, $d = null) => old($k, $org ? $org->$k : $d);
    $universities = \App\Models\Organisation::where('organisation_type_id', 1)->orderBy('name')->get(['id', 'name', 'head_office_location']);
    $main = $org ? \App\Models\Campus::where('organisation_id', $org->id)->orderByRaw("campus_role = 'main' desc")->orderBy('created_at')->first() : null;
    $states = ['Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala',
        'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Jammu and Kashmir',
        'Ladakh', 'Lakshadweep', 'Puducherry'];
    $campusState = old('campus_state', $main->state ?? '');
@endphp
<div id="college-fields" class="col-12" style="display: none;">
    <hr class="my-4">
    <h5 class="mb-1 text-secondary">College Details</h5>
    <p class="text-muted small mb-3">A college belongs to a university. Its courses are added from the master course list; departments are optional.</p>

    <div class="row g-3">
        <div class="col-12"><h6 class="fw-bold mb-0">Affiliation</h6></div>
        <div class="col-md-7">
            <label class="form-label">Affiliated to (university) <span class="text-danger">*</span></label>
            <select name="affiliating_organisation_id" class="form-select" style="width:100%" data-placeholder="Search a university">
                <option value="">Select the university</option>
                @foreach($universities as $u)
                    <option value="{{ $u->id }}" @selected((string) $val('affiliating_organisation_id') === (string) $u->id)>{{ $u->name }}{{ $u->head_office_location ? ' – ' . $u->head_office_location : '' }}</option>
                @endforeach
            </select>
            <div class="form-text">The university that awards this college's degrees.</div>
        </div>
        <div class="col-md-5">
            <label class="form-label">College status <span class="text-danger">*</span></label>
            <select name="legal_entity_type" class="form-select" style="width:100%">
                <option value="">Select status</option>
                @foreach(['affiliated_college' => 'Affiliated college', 'autonomous_college' => 'Autonomous college (affiliated)', 'constituent_college' => 'Constituent college'] as $k => $l)
                    <option value="{{ $k }}" @selected($val('legal_entity_type') === $k)>{{ $l }}</option>
                @endforeach
            </select>
            <div class="form-text">Constituent = run by the university itself.</div>
        </div>

        <div class="col-12 mt-4"><h6 class="fw-bold mb-0">Identity</h6></div>
        <div class="col-md-4">
            <label class="form-label">Short Name</label>
            <input type="text" name="short_name" class="form-control" value="{{ $val('short_name') }}" placeholder="e.g. GMC">
        </div>
        <div class="col-md-4">
            <label class="form-label">Brand Name</label>
            <input type="text" name="brand_name" class="form-control" value="{{ $val('brand_name') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Established Year</label>
            <input type="number" name="established_year" class="form-control" value="{{ $val('established_year') }}" min="1800" max="{{ date('Y') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Ownership <span class="text-danger">*</span></label>
            <select name="ownership_type" class="form-select" style="width:100%">
                <option value="">Select ownership</option>
                @foreach(['Government', 'Government-aided', 'Private', 'Trust', 'Minority', 'PPP'] as $ow)
                    <option value="{{ $ow }}" @selected($val('ownership_type') === $ow)>{{ $ow }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Official Website</label>
            <input type="url" name="official_website" class="form-control" value="{{ $val('official_website') }}" placeholder="https://">
        </div>
        <div class="col-md-12">
            <label class="form-label">About the College</label>
            <textarea name="about_organisation" class="form-control editor" rows="3">{{ $val('about_organisation') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Logo</label>
            <input type="file" name="logo_url" class="form-control" accept="image/*">
            @if($org && $org->logo_url)<div class="small text-muted mt-1">Current: {{ basename($org->logo_url) }}</div>@endif
        </div>
        <div class="col-md-6">
            <label class="form-label">Cover Image</label>
            <input type="file" name="cover_image_url" class="form-control" accept="image/*">
            @if($org && $org->cover_image_url)<div class="small text-muted mt-1">Current: {{ basename($org->cover_image_url) }}</div>@endif
        </div>

        <div class="col-12 mt-4"><h6 class="fw-bold mb-0">Location</h6><div class="form-text mt-0">Saved as the college's main campus. Add more campuses later only if the college has them.</div></div>
        <div class="col-md-4">
            <label class="form-label">City <span class="text-danger">*</span></label>
            <input type="text" name="campus_city" class="form-control no-autosave" value="{{ old('campus_city', $main->city ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">State <span class="text-danger">*</span></label>
            <select name="campus_state" class="form-select no-autosave" style="width:100%">
                <option value="">Select state</option>
                @foreach($states as $st)<option @selected($campusState === $st)>{{ $st }}</option>@endforeach
                @if($campusState && !in_array($campusState, $states, true))<option selected>{{ $campusState }}</option>@endif
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Pincode</label>
            <input type="text" name="campus_pincode" class="form-control no-autosave" value="{{ old('campus_pincode', $main->pincode ?? '') }}" maxlength="6" inputmode="numeric">
        </div>
        <div class="col-md-12">
            <label class="form-label">Full Address</label>
            <input type="text" name="campus_address" class="form-control no-autosave" value="{{ old('campus_address', $main->full_address ?? '') }}">
        </div>

        <div class="col-12 mt-4">
            <div class="alert alert-info py-2 mb-0 small">Approvals (UGC, AICTE, PCI...), NAAC and NIRF are added on the college's <b>Rankings &amp; Accreditation</b> page after saving.</div>
        </div>
    </div>
</div>
