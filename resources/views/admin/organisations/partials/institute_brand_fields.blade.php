{{-- Coaching institute (type 3), brand level: the Institute section's extra tabs on the organisation create and edit forms
     (App\Support\Institutes\InstituteFields). $organisation is null on create. Inputs are named after their columns so the
     edit page's field autosave works; the gallery inputs are "no-autosave" and save with the form. --}}
@php
    use App\Support\Institutes\InstituteFields as IF_;
    $o = $organisation ?? null;
    $opt = IF_::options($o);
    $branches = IF_::branchSummary($o);
    $v = fn ($f) => old($f, $o?->$f);
    $list = fn ($f) => (array) (old($f, $o?->$f) ?? []);
    $csv = fn ($f) => implode(', ', is_array($x = old($f, $o?->$f)) ? $x : array_filter([(string) $x]));
@endphp
<input type="hidden" name="institute_form" value="1">

{{-- Contact & Social --}}
<div class="tab-pane fade" id="inst-contact" role="tabpanel" aria-labelledby="inst-contact-tab">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Official Website</label>
            <input type="url" name="official_website" class="form-control" value="{{ $v('official_website') }}" placeholder="https://www.example.com" maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label">Central Helpline</label>
            <input type="text" name="helpdesk_contact_number" class="form-control" value="{{ $v('helpdesk_contact_number') }}" placeholder="e.g. 1800 123 4567" maxlength="50">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="helpdesk_email" class="form-control" value="{{ $v('helpdesk_email') }}" placeholder="info@example.com" maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label">WhatsApp Number</label>
            <input type="text" name="whatsapp_number" class="form-control" value="{{ $v('whatsapp_number') }}" placeholder="e.g. +91 98765 43210" maxlength="30">
        </div>
        <div class="col-12"><h6 class="text-muted mt-2 mb-0">Social & App Links</h6></div>
        @foreach(['youtube_url' => ['YouTube', 'https://www.youtube.com/@channel'], 'instagram_url' => ['Instagram', 'https://www.instagram.com/handle'],
                  'telegram_url' => ['Telegram', 'https://t.me/channel'], 'facebook_url' => ['Facebook', 'https://www.facebook.com/page'],
                  'play_store_url' => ['App (Play Store)', 'https://play.google.com/store/apps/details?id=…'], 'app_store_url' => ['App (App Store)', 'https://apps.apple.com/…']] as $f => [$label, $ph])
            <div class="col-md-6">
                <label class="form-label">{{ $label }}</label>
                <input type="url" name="{{ $f }}" class="form-control" value="{{ $v($f) }}" placeholder="{{ $ph }}" maxlength="255">
            </div>
        @endforeach
    </div>
</div>

{{-- Courses & Network --}}
<div class="tab-pane fade" id="inst-network" role="tabpanel" aria-labelledby="inst-network-tab">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Coaching Categories</label>
            <select name="coaching_category_ids[]" class="form-select" multiple data-placeholder="e.g. Medical, Engineering">
                @foreach($opt['coachingCategories'] as $cc)
                    <option value="{{ $cc->id }}" {{ in_array($cc->id, array_map('intval', $list('coaching_category_ids')), true) ? 'selected' : '' }}>{{ $cc->title }}</option>
                @endforeach
            </select>
            <div class="form-text">For the whole brand. Branches keep their own categories.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Exams Prepared For</label>
            <select name="exams_prepared_for[]" class="form-select" multiple data-placeholder="e.g. NEET, JEE Main">
                @foreach($opt['exams'] as $exam)
                    <option value="{{ $exam }}" {{ in_array($exam, $list('exams_prepared_for'), true) ? 'selected' : '' }}>{{ $exam }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Teaching Modes</label>
            <select name="teaching_modes[]" class="form-select" multiple data-placeholder="Offline, Online live, Recorded, Hybrid">
                @foreach($opt['teachingModes'] as $m)
                    <option value="{{ $m }}" {{ in_array($m, $list('teaching_modes'), true) ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Founder</label>
            <input type="text" name="founder_name" class="form-control" value="{{ $v('founder_name') }}" maxlength="255">
        </div>
        <div class="col-md-3">
            <label class="form-label">Director</label>
            <input type="text" name="director_name" class="form-control" value="{{ $v('director_name') }}" maxlength="255">
        </div>

        <div class="col-12 d-flex align-items-center justify-content-between mt-3">
            <h6 class="text-muted mb-0">Network</h6>
            @if($branches['count'])
                <button type="button" class="btn btn-sm btn-outline-primary" id="inst-fill-network"
                        data-count="{{ $branches['count'] }}" data-cities="{{ implode(', ', $branches['cities']) }}" data-states="{{ implode(', ', $branches['states']) }}">
                    Fill from branches ({{ $branches['count'] }} active)
                </button>
            @endif
        </div>
        <div class="col-md-3">
            <label class="form-label">Number of Centres</label>
            <input type="number" name="number_of_campuses" id="inst_number_of_campuses" class="form-control" value="{{ $v('number_of_campuses') }}" min="0">
        </div>
        <div class="col-md-5">
            <label class="form-label">Cities</label>
            <input type="text" name="cities_present_in" id="inst_cities_present_in" class="form-control" value="{{ $csv('cities_present_in') }}" placeholder="Kota, Delhi, Patna">
            <div class="form-text">Separate with commas.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">States</label>
            <input type="text" name="states_present_in" id="inst_states_present_in" class="form-control" value="{{ $csv('states_present_in') }}" placeholder="Rajasthan, Delhi, Bihar">
            <div class="form-text">Separate with commas.</div>
        </div>
    </div>
</div>

{{-- Scholarship & Franchise --}}
<div class="tab-pane fade" id="inst-offer" role="tabpanel" aria-labelledby="inst-offer-tab">
    <div class="row g-3">
        <div class="col-12"><h6 class="text-muted mb-0">Scholarship / Admission Test</h6></div>
        <div class="col-md-4">
            <label class="form-label">Test Name</label>
            <input type="text" name="scholarship_test_name" class="form-control" value="{{ $v('scholarship_test_name') }}" placeholder="e.g. TALLENTEX" maxlength="255">
        </div>
        <div class="col-md-4">
            <label class="form-label">Test Dates</label>
            <input type="text" name="scholarship_test_dates" class="form-control" value="{{ $v('scholarship_test_dates') }}" placeholder="e.g. 12 & 19 Oct 2026" maxlength="255">
        </div>
        <div class="col-md-4">
            <label class="form-label">Test Link</label>
            <input type="url" name="scholarship_test_url" class="form-control" value="{{ $v('scholarship_test_url') }}" placeholder="https://…" maxlength="255">
        </div>

        <div class="col-12"><h6 class="text-muted mt-3 mb-0">Franchise</h6></div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" name="franchise_available" id="inst_franchise_available" value="1" {{ $v('franchise_available') ? 'checked' : '' }}>
                <label class="form-check-label" for="inst_franchise_available">Offers franchise</label>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Franchise Contact</label>
            <input type="text" name="franchise_contact_name" class="form-control" value="{{ $v('franchise_contact_name') }}" maxlength="255">
        </div>
        <div class="col-md-3">
            <label class="form-label">Phone</label>
            <input type="text" name="franchise_contact_phone" class="form-control" value="{{ $v('franchise_contact_phone') }}" maxlength="30">
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" name="franchise_contact_email" class="form-control" value="{{ $v('franchise_contact_email') }}" maxlength="255">
        </div>
    </div>
</div>

{{-- Trust & Media --}}
<div class="tab-pane fade" id="inst-trust" role="tabpanel" aria-labelledby="inst-trust-tab">
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Rating (0–5)</label>
            <input type="number" name="average_rating" class="form-control" value="{{ $v('average_rating') }}" min="0" max="5" step="0.1">
        </div>
        <div class="col-md-2">
            <label class="form-label">Review Count</label>
            <input type="number" name="total_reviews" class="form-control" value="{{ $v('total_reviews') }}" min="0">
        </div>
        <div class="col-md-3">
            <label class="form-label">Verification Status</label>
            <select name="verification_status" class="form-select">
                @foreach($opt['verification'] as $s)
                    <option value="{{ $s }}" {{ ($v('verification_status') ?: 'Pending') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Data Source</label>
            <input type="text" name="data_source" class="form-control" value="{{ $v('data_source') }}" placeholder="e.g. Official website, call with centre" maxlength="255">
        </div>
        <div class="col-md-2">
            <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" name="claimed_by_organization" id="inst_claimed" value="1" {{ $v('claimed_by_organization') ? 'checked' : '' }}>
                <label class="form-check-label" for="inst_claimed">Claimed by institute</label>
            </div>
        </div>

        <div class="col-12"><h6 class="text-muted mt-3 mb-0">Photos & Videos</h6></div>
        @include('admin.organisations.partials.media_gallery', ['media' => $o?->id ? $o->media : collect(), 'prefix' => 'inst'])
    </div>
</div>

@once
@push('scripts')
<script>
    // "Fill from branches": the active branches' count, cities and states into the Network fields (they then save as usual)
    document.addEventListener('click', function (e) {
        const b = e.target.closest('#inst-fill-network');
        if (!b) return;
        [['inst_number_of_campuses', b.dataset.count], ['inst_cities_present_in', b.dataset.cities], ['inst_states_present_in', b.dataset.states]].forEach(function ([id, val]) {
            const el = document.getElementById(id);
            if (!el || el.disabled) return;
            el.value = val;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
</script>
@endpush
@endonce
