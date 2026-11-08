@extends('admin.layouts.master')

@section('title', 'Billing Details - ' . $organisation->name)

{{-- One form for the organisation itself or (?campus=ID) one of its campuses (BillingOrganisationController::edit).
     A campus can be billed with the organisation's details ("Same as organisation"); its own fields are then hidden
     but kept. For billing only; never shown on the website. --}}
@php
    $isCampus = (bool) $campus;
    $same = (bool) old('same_as_organisation', $profile->same_as_organisation);
    $orgFilled = $orgProfile && $orgProfile->isFilled();
    $chip = function ($row, bool $forCampus) use ($orgFilled) {
        if (!$row) return ['Missing', 'bg-danger-subtle text-danger border-danger-subtle'];
        if ($forCampus && $row->same_as_organisation) return $orgFilled ? ['Same as organisation', 'bg-info-subtle text-info-emphasis border-info-subtle'] : ['Same as organisation (empty)', 'bg-warning-subtle text-warning-emphasis border-warning-subtle'];
        return $row->isFilled() ? ['Set up', 'bg-success-subtle text-success border-success-subtle'] : ['Missing', 'bg-danger-subtle text-danger border-danger-subtle'];
    };
    $field = fn ($name) => old($name, $profile->$name);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-dark fw-bold">Billing Details · {{ $organisation->name }}</h4>
        <p class="text-muted small mb-0">
            @if($campuses->count())
                This organisation has {{ $campuses->count() }} {{ Str::plural('campus', $campuses->count()) }}: invoices are billed per campus.
            @else
                No campuses: invoices are billed to the organisation.
            @endif
            For billing only, never shown on the website.
        </p>
    </div>
    <a href="{{ route('admin.billing.organisations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to list</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</div>
    <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    {{-- the organisation and its campuses --}}
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom"><h6 class="card-title mb-0 fw-bold text-dark"><i class="fas fa-sitemap me-2"></i> Billed to</h6></div>
            <div class="list-group list-group-flush">
                @php [$t, $c] = $chip($saved->get(''), false); @endphp
                <a href="{{ route('admin.billing.organisations.edit', $organisation->id) }}" class="list-group-item list-group-item-action py-3 {{ !$isCampus ? 'active' : '' }}">
                    <div class="fw-semibold"><i class="fas fa-university me-1"></i> Organisation</div>
                    <span class="badge border mt-1 {{ $c }}" style="font-size:11px">{{ $t }}</span>
                </a>
                @foreach($campuses as $cp)
                    @php [$t, $c] = $chip($saved->get($cp->id), true); @endphp
                    <a href="{{ route('admin.billing.organisations.edit', [$organisation->id, 'campus' => $cp->id]) }}" class="list-group-item list-group-item-action py-3 {{ $isCampus && $campus->id === $cp->id ? 'active' : '' }}">
                        <div class="fw-semibold"><i class="fas fa-building me-1"></i> {{ $cp->campus_name }}</div>
                        @if($cp->city)<div class="small {{ $isCampus && $campus->id === $cp->id ? '' : 'text-muted' }}">{{ $cp->city }}</div>@endif
                        <span class="badge border mt-1 {{ $c }}" style="font-size:11px">{{ $t }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <form action="{{ route('admin.billing.organisations.update', $organisation->id) }}" method="POST" id="billingForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="campus_id" value="{{ $campus->id ?? '' }}">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">{{ $isCampus ? 'Campus' : 'Organisation' }}</div>
                        <h5 class="mb-0 fw-bold text-primary">{{ $isCampus ? $campus->campus_name : $organisation->name }}</h5>
                        <div class="small text-muted">
                            @if($isCampus)
                                Used for invoices to this campus.
                            @elseif($campuses->count())
                                Used for campuses set to “Same as organisation”.
                            @else
                                Used for every invoice to this organisation.
                            @endif
                        </div>
                    </div>
                    @if($isCampus)
                        <div class="form-check form-switch fs-6 mb-0">
                            <input type="hidden" name="same_as_organisation" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="same_as_organisation" id="sameAsOrg" value="1" {{ $same ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="sameAsOrg">Same as organisation</label>
                        </div>
                    @endif
                </div>
            </div>

            @if($isCampus)
            {{-- toggle on: the organisation's details, which this campus's invoices will print --}}
            <div class="card border-0 shadow-sm mb-4 {{ $same ? '' : 'd-none' }}" id="orgDetails">
                <div class="card-body">
                    @if($orgFilled)
                        <div class="small text-muted mb-2">This campus is billed with the organisation's details:</div>
                        <div class="lh-sm">
                            @if($orgProfile->legal_name)<div class="fw-bold">{{ $orgProfile->legal_name }}</div>@endif
                            @if($orgProfile->address)<div>{{ $orgProfile->address }}</div>@endif
                            <div>{{ implode(', ', array_filter([$orgProfile->city, $orgProfile->state, $orgProfile->pincode])) }}</div>
                            <div class="mt-1 small">
                                @foreach(['gstin' => 'GSTIN', 'tan_number' => 'TAN', 'pan_number' => 'PAN', 'cin_number' => 'CIN'] as $f => $label)
                                    @if($orgProfile->$f)<span class="me-3"><strong>{{ $label }}:</strong> <span class="font-monospace">{{ $orgProfile->$f }}</span></span>@endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-1"></i> The organisation's billing details are not filled in yet.
                            <a href="{{ route('admin.billing.organisations.edit', $organisation->id) }}">Add them</a>, or turn this off and fill in the campus's own.</div>
                    @endif
                </div>
            </div>
            @endif

            <div id="ownFields" class="{{ $isCampus && $same ? 'd-none' : '' }}">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom"><h6 class="card-title mb-0 fw-bold text-primary"><i class="fas fa-building me-2"></i> Legal name</h6></div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Legal / registered name</label>
                        <input type="text" name="legal_name" class="form-control @error('legal_name') is-invalid @enderror" value="{{ $field('legal_name') }}" maxlength="255" placeholder="As on the GST registration, e.g. XYZ Educational Trust">
                        @error('legal_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text small">Leave empty if it is the same as “{{ $isCampus ? $organisation->name . ' - ' . $campus->campus_name : $organisation->name }}”.</div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom"><h6 class="card-title mb-0 fw-bold text-primary"><i class="fas fa-file-invoice me-2"></i> Tax & Legal Details</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">GSTIN Number</label>
                                <input type="text" name="gstin" class="form-control text-uppercase @error('gstin') is-invalid @enderror" value="{{ $field('gstin') }}" placeholder="e.g. 07AAAAA0000A1Z5" maxlength="15">
                                @error('gstin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text small">15-digit GST ID</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">TAN Number</label>
                                <input type="text" name="tan_number" class="form-control text-uppercase @error('tan_number') is-invalid @enderror" value="{{ $field('tan_number') }}" placeholder="e.g. DELA12345E" maxlength="25">
                                @error('tan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text small">Tax Deduction A/C No</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">PAN Number</label>
                                <input type="text" name="pan_number" class="form-control text-uppercase @error('pan_number') is-invalid @enderror" value="{{ $field('pan_number') }}" placeholder="e.g. ABCDE1234F" maxlength="10">
                                @error('pan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text small">Permanent Account No</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">CIN Number</label>
                                <input type="text" name="cin_number" class="form-control text-uppercase @error('cin_number') is-invalid @enderror" value="{{ $field('cin_number') }}" placeholder="e.g. U72900DL2020PTC123456" maxlength="50">
                                @error('cin_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text small">Corporate Identity No</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom"><h6 class="card-title mb-0 fw-bold text-primary"><i class="fas fa-address-book me-2"></i> Contact Details</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ $field('contact_person') }}" placeholder="e.g. Accounts head">
                                @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $field('email') }}" placeholder="e.g. accounts@college.edu">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $field('phone') }}" placeholder="e.g. +91 9876543210">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom"><h6 class="card-title mb-0 fw-bold text-primary"><i class="fas fa-map-marker-alt me-2"></i> Billing Address</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Full street address, as on the GST registration">{{ $field('address') }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ $field('city') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ $field('state') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Pincode</label>
                                <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ $field('pincode') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Country</label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ $field('country') ?: 'India' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label class="form-label fw-semibold">Internal notes</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional, for the billing team only">{{ $field('notes') }}</textarea>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        @if($profile->exists && $profile->updated_at) Last updated {{ $profile->updated_at->format('d M Y, h:i A') }} @else Not saved yet @endif
                    </div>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="fas fa-save me-1"></i> Save billing details</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($isCampus)
<script>
    // "Same as organisation": show the organisation's details instead of this campus's own (kept, just hidden)
    document.getElementById('sameAsOrg').addEventListener('change', function () {
        document.getElementById('orgDetails').classList.toggle('d-none', !this.checked);
        document.getElementById('ownFields').classList.toggle('d-none', this.checked);
    });
</script>
@endif
@endsection
