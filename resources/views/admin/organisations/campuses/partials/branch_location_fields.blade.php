{{-- Location tab, every organisation type: locality, landmark and map coordinates (App\Support\Institutes\InstituteFields).
     $campus is null on create. "Get from Google Maps link" reads the coordinates from the link above, when it carries them. --}}
@php $c = $campus ?? null; $v = fn ($f) => old($f, $c?->$f); @endphp
<div class="col-md-4">
    <label class="form-label">Locality / Area</label>
    <input type="text" name="locality" class="form-control" value="{{ $v('locality') }}" placeholder="e.g. Talwandi, Mukherjee Nagar" maxlength="255">
</div>
<div class="col-md-4">
    <label class="form-label">Landmark</label>
    <input type="text" name="nearest_landmark" class="form-control" value="{{ $v('nearest_landmark') }}" placeholder="e.g. Opposite City Mall" maxlength="255">
</div>
<div class="col-md-2">
    <label class="form-label">Latitude</label>
    <input type="number" name="latitude" id="branch_latitude" class="form-control" value="{{ $v('latitude') }}" step="0.0000001" min="-90" max="90" placeholder="25.1462">
</div>
<div class="col-md-2">
    <label class="form-label">Longitude</label>
    <input type="number" name="longitude" id="branch_longitude" class="form-control" value="{{ $v('longitude') }}" step="0.0000001" min="-180" max="180" placeholder="75.8344">
</div>
<div class="col-12">
    <button type="button" class="btn btn-sm btn-outline-primary" id="branch-coords-from-map"><i class="fas fa-map-marker-alt me-1"></i> Get from Google Maps link</button>
    <span class="small ms-2" id="branch-coords-msg"></span>
</div>

@once
@push('scripts')
<script>
    // the coordinates in a Google Maps link: …/@25.14,75.83,17z, ?q=25.14,75.83, ll=…, or !3d25.14!4d75.83
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#branch-coords-from-map')) return;
        const link = (document.querySelector('[name="google_map_url"]') || {}).value || '', msg = document.getElementById('branch-coords-msg');
        const m = link.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/) || link.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/) || link.match(/[?&](?:q|ll|query|destination)=(-?\d+\.\d+),\s*(-?\d+\.\d+)/);
        if (!m) {
            msg.className = 'small ms-2 text-danger';
            msg.textContent = link ? 'This link has no coordinates (short links like maps.app.goo.gl don\'t). Open it, then copy the full address-bar link, or right-click the place in Google Maps and click the coordinates to copy them.' : 'Paste the Google Maps link first.';
            return;
        }
        ['branch_latitude', 'branch_longitude'].forEach(function (id, i) {
            const el = document.getElementById(id);
            el.value = parseFloat(m[i + 1]).toFixed(7);
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
        msg.className = 'small ms-2 text-success';
        msg.textContent = 'Filled from the link.';
    });
</script>
@endpush
@endonce
