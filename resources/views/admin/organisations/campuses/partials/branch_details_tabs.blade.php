{{-- campus create / edit, coaching institutes (type 3): the Branch Details and Photos & Videos tab panes
     (App\Support\Institutes\InstituteFields). $campus is null on create. Inputs are named after their campuses columns, so
     the edit page's tab autosave keeps them; the photos and videos save with the Update button. --}}
@php use App\Support\Institutes\InstituteFields as IF_; @endphp
@if($organisation->organisation_type_id == IF_::TYPE)
@php
    $c = $campus ?? null;
    $v = fn ($f) => old($f, $c?->$f);
    $time = fn ($f) => ($t = old($f, $c?->$f)) ? substr((string) $t, 0, 5) : '';
    $days = (array) (old('working_days', $c?->working_days) ?? []);
    $stays = array_values((array) (old('nearby_accommodations', $c?->nearby_accommodations) ?? []));
    if (!$stays) $stays = [[]];
@endphp
<div class="tab-pane" id="branch-details" role="tabpanel">
    <input type="hidden" name="branch_form" value="1">
    <div class="row g-3">
        <div class="col-12"><h6 class="text-dark mb-0">Timings</h6></div>
        <div class="col-md-3">
            <label class="form-label">Opens at</label>
            <input type="time" name="opening_time" class="form-control" value="{{ $time('opening_time') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Closes at</label>
            <input type="time" name="closing_time" class="form-control" value="{{ $time('closing_time') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Working Days</label>
            <select name="working_days[]" class="form-select select2" multiple data-placeholder="Mon – Sat">
                @foreach(IF_::DAYS as $d)
                    <option value="{{ $d }}" {{ in_array($d, $days, true) ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12"><h6 class="text-dark mt-3 mb-0">Branch Contact</h6></div>
        <div class="col-md-3">
            <label class="form-label">Branch Head</label>
            <input type="text" name="branch_head_name" class="form-control" value="{{ $v('branch_head_name') }}" maxlength="255">
        </div>
        <div class="col-md-3">
            <label class="form-label">Branch Head Phone</label>
            <input type="text" name="branch_head_phone" class="form-control" value="{{ $v('branch_head_phone') }}" maxlength="30">
        </div>
        <div class="col-md-3">
            <label class="form-label">Branch WhatsApp</label>
            <input type="text" name="whatsapp_number" class="form-control" value="{{ $v('whatsapp_number') }}" maxlength="30">
        </div>
        <div class="col-md-3">
            <label class="form-label">Email for Leads</label>
            <input type="email" name="lead_email" class="form-control" value="{{ $v('lead_email') }}" placeholder="leads go to this address" maxlength="255">
        </div>

        <div class="col-12"><h6 class="text-dark mt-3 mb-0">Facilities</h6></div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input type="hidden" name="reading_room_available" value="0">
                <input class="form-check-input" type="checkbox" name="reading_room_available" value="1" id="br_reading_room" {{ $v('reading_room_available') ? 'checked' : '' }}>
                <label class="form-check-label" for="br_reading_room">Self-study / reading room</label>
            </div>
        </div>
        <div class="col-md-5">
            <label class="form-label">Reading Room Hours</label>
            <input type="text" name="reading_room_hours" class="form-control" value="{{ $v('reading_room_hours') }}" placeholder="e.g. 7 AM – 10 PM, all days" maxlength="100">
        </div>
        <div class="col-md-4">
            <div class="form-check form-switch mt-2">
                <input type="hidden" name="test_centre_available" value="0">
                <input class="form-check-input" type="checkbox" name="test_centre_available" value="1" id="br_test_centre" {{ $v('test_centre_available') ? 'checked' : '' }}>
                <label class="form-check-label" for="br_test_centre">Test centre at this branch</label>
            </div>
        </div>

        <div class="col-12"><h6 class="text-dark mt-3 mb-0">Rating & Page</h6></div>
        <div class="col-md-2">
            <label class="form-label">Rating (0–5)</label>
            <input type="number" name="average_rating" class="form-control" value="{{ $v('average_rating') }}" min="0" max="5" step="0.1">
        </div>
        <div class="col-md-2">
            <label class="form-label">Review Count</label>
            <input type="number" name="total_reviews" class="form-control" value="{{ $v('total_reviews') }}" min="0">
        </div>
        <div class="col-md-8">
            <label class="form-label">Branch Page Link</label>
            <div class="input-group">
                <span class="input-group-text small">{{ IF_::websiteUrl('campus-detail/') }}</span>
                <input type="text" name="slug" class="form-control" value="{{ $v('slug') }}" placeholder="{{ $c ? '' : 'made from the branch name if left empty' }}" maxlength="255" pattern="[a-z0-9]+(-[a-z0-9]+)*">
                @if($c?->slug)
                    <a class="btn btn-outline-secondary" href="{{ IF_::websiteUrl('campus-detail/' . $c->slug) }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt me-1"></i> View page</a>
                @endif
            </div>
            <div class="form-text">Small letters, digits and hyphens. Changing it changes the page's address.</div>
        </div>

        <div class="col-12 d-flex justify-content-between align-items-center mt-3">
            <h6 class="text-dark mb-0">Nearby Hostels & PGs</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="br-add-stay"><i class="fas fa-plus me-1"></i> Add</button>
        </div>
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="br-stays">
                    <thead class="table-light"><tr><th>Name</th><th style="width:100px">Type</th><th style="width:100px">For</th><th style="width:110px">Rent from ₹</th><th style="width:110px">Rent to ₹</th><th style="width:95px">Distance km</th><th style="width:140px">Phone</th><th style="width:40px"></th></tr></thead>
                    <tbody>
                        @foreach($stays as $i => $s)
                            <tr>
                                <td><input type="text" name="nearby_accommodations[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $s['name'] ?? '' }}" maxlength="255" placeholder="e.g. Shree Hostel"></td>
                                <td><select name="nearby_accommodations[{{ $i }}][type]" class="form-select form-select-sm custom-select2"><option value=""></option>@foreach(IF_::STAY_TYPES as $t)<option {{ ($s['type'] ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach</select></td>
                                <td><select name="nearby_accommodations[{{ $i }}][for]" class="form-select form-select-sm custom-select2"><option value=""></option>@foreach(IF_::STAY_FOR as $t)<option {{ ($s['for'] ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach</select></td>
                                <td><input type="number" name="nearby_accommodations[{{ $i }}][rent_min]" class="form-control form-control-sm" value="{{ $s['rent_min'] ?? '' }}" min="0"></td>
                                <td><input type="number" name="nearby_accommodations[{{ $i }}][rent_max]" class="form-control form-control-sm" value="{{ $s['rent_max'] ?? '' }}" min="0"></td>
                                <td><input type="number" name="nearby_accommodations[{{ $i }}][distance_km]" class="form-control form-control-sm" value="{{ $s['distance_km'] ?? '' }}" min="0" step="0.1"></td>
                                <td><input type="text" name="nearby_accommodations[{{ $i }}][phone]" class="form-control form-control-sm" value="{{ $s['phone'] ?? '' }}" maxlength="30"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger br-remove-stay" title="Remove">&times;</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="form-text">Rent per month. Rows without a name are not saved.</div>
        </div>
    </div>
</div>

<div class="tab-pane" id="branch-media" role="tabpanel">
    <div class="row g-3">
        @include('admin.organisations.partials.media_gallery', ['media' => $c?->id ? $c->media : collect(), 'prefix' => 'branch'])
    </div>
</div>

@once
@push('scripts')
<script>
    // Nearby Hostels & PGs: add a row (a copy of the last one, emptied) / remove a row (the last one is emptied instead)
    document.addEventListener('click', function (e) {
        const body = document.querySelector('#br-stays tbody');
        if (!body) return;
        if (e.target.closest('#br-add-stay')) {
            // a new index above every one in use (after a removal, the row count could repeat an existing row's index)
            const rows = body.querySelectorAll('tr'), row = rows[rows.length - 1].cloneNode(true);
            const i = Math.max(-1, ...Array.from(body.querySelectorAll('input[name^="nearby_accommodations["]'), function (el) { return parseInt(el.name.match(/\[(\d+)\]/)[1], 10); })) + 1;
            row.querySelectorAll('input, select').forEach(function (el) {
                el.name = el.name.replace(/\[\d+\]/, '[' + i + ']');
                el.value = '';
            });
            body.appendChild(row);
        }
        const rm = e.target.closest('.br-remove-stay');
        if (rm) {
            const tr = rm.closest('tr');
            if (body.querySelectorAll('tr').length > 1) tr.remove();
            else tr.querySelectorAll('input, select').forEach(function (el) { el.value = ''; });
        }
    });
</script>
@endpush
@endonce
@endif
