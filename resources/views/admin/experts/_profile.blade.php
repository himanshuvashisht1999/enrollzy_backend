{{-- "Profile page" tab of the expert form (create and edit): the extra sections of the website's expert profile
     (/expert-detail/{slug}): earlier roles, how sessions work, top achievements of students, track record, talks and
     research, network, and the link students rate the expert on. $expert is null on create. An empty field or list is simply not shown on the website. --}}
@php
    use App\Models\Expert;
    $v = fn ($k, $d = null) => old($k, $expert->{$k} ?? $d);
    $rows = function ($k, array $cols) use ($expert) {
        $r = old($k, $expert->{$k} ?? []);
        if (is_string($r)) $r = json_decode($r, true) ?: [];
        $r = array_values(array_filter((array) $r, 'is_array'));
        return $r ?: [array_fill_keys($cols, '')];
    };
    $lines = fn ($k) => old($k, $expert->{$k} ?? '');
    $tags = fn ($k) => array_values(array_filter((array) old($k, $expert->{$k} ?? []), fn ($t) => is_scalar($t) && trim((string) $t) !== ''));
    $reps = [
        'previous_institutes' => ['Earlier roles', 'Shown on the Experience timeline, below the current institute.', ['role' => ['Role', 'e.g. Physics faculty', 4], 'name' => ['Institute', 'e.g. Super 30 Academy, Patna', 5], 'years' => ['Years', 'e.g. 2013 – 2018', 3]]],
        // keys stay name / rank / exam / year (saved data), the labels say what they hold now
        'top_rank_students' => ['Top achievements of their students', 'Any success a student is proud of: a rank, an admission, a scholarship, a job, a startup. Use a first name or "Student A" if the family has not agreed to the full name.', ['name' => ['Student', 'e.g. Riya S.', 3], 'rank' => ['Achievement', 'e.g. AIR 412 · Admitted · Became CEO', 3], 'exam' => ['Exam / college / company', 'e.g. JEE Advanced · IIT Delhi · Google', 4], 'year' => ['Year', '2024', 2]]],
    ];
    $profileUrl = $expert?->exists ? config('cns.frontend_url') . '/expert-detail/' . ($expert->slug ?: $expert->id) : null;
@endphp
<div class="tab-pane fade" id="ex-profile">
    <h5 class="mb-1 text-primary">Profile page</h5>
    <p class="text-muted small">Extra sections of this expert's profile on the website. The bio, qualifications, teaching details, videos and links stay on their own tabs.</p>

    <h6 class="mt-3 mb-2 fw-bold">How their sessions work</h6>
    <div class="row g-2">
        @foreach(Expert::APPROACH as $k => $l)
            <div class="col-md-4"><div class="form-check form-switch p-2 border rounded"><input class="form-check-input ms-0 me-2" type="checkbox" name="{{ $k }}" id="ap-{{ $k }}" value="1" @checked(old($k, $expert->{$k} ?? false))><label class="form-check-label" for="ap-{{ $k }}">{{ $l }}</label></div></div>
        @endforeach
        <div class="col-md-6">
            <label class="form-label">Counselling focus <small class="text-muted">(tags)</small></label>
            <select class="form-control select2-tags" name="counseling_specialization[]" multiple>@foreach($tags('counseling_specialization') as $t)<option value="{{ $t }}" selected>{{ $t }}</option>@endforeach</select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Usually replies within</label>
            <input type="text" class="form-control" name="average_wait_time" maxlength="60" value="{{ $v('average_wait_time') }}" placeholder="e.g. 2 hours">
        </div>
    </div>

    @foreach($reps as $key => [$title, $help, $cols])
        <h6 class="mt-4 mb-1 fw-bold">{{ $title }}</h6>
        <p class="text-muted small mb-2">{{ $help }}</p>
        <div class="row g-2 mb-1 d-none d-md-flex">@foreach($cols as $c => [$label, $ph, $w])<div class="col-md-{{ $w - ($loop->last ? 1 : 0) }} small fw-semibold text-muted">{{ $label }}</div>@endforeach</div>
        <div data-rep="{{ $key }}">
            @foreach($rows($key, array_keys($cols)) as $i => $row)
                <div class="row g-2 mb-2 rep-row">
                    @foreach($cols as $c => [$label, $ph, $w])<div class="col-md-{{ $w - ($loop->last ? 1 : 0) }}"><input type="text" class="form-control" name="{{ $key }}[{{ $i }}][{{ $c }}]" value="{{ $row[$c] ?? '' }}" placeholder="{{ $ph }}" aria-label="{{ $label }}"></div>@endforeach
                    <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger" data-rep-del title="Remove"><i class="fas fa-trash"></i></button></div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" data-rep-add="{{ $key }}"><i class="fas fa-plus me-1"></i> Add row</button>
    @endforeach

    {{-- testimonials are no longer typed in: students rate the expert on the profile page themselves --}}
    <h6 class="mt-4 mb-1 fw-bold">Ratings from students</h6>
    <p class="text-muted small mb-2">Testimonials are not typed in here. Anyone signed in on the website can rate this expert on their profile page; share the link with their students and parents.</p>
    @if($profileUrl)
        <div class="input-group" style="max-width:640px">
            <input type="text" class="form-control" value="{{ $profileUrl }}" readonly id="ex-profile-link">
            <button type="button" class="btn btn-outline-primary" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('ex-profile-link').value); this.textContent = 'Copied';">Copy link</button>
            <a class="btn btn-outline-secondary" href="{{ $profileUrl }}#reviews" target="_blank" rel="noopener">Open</a>
        </div>
        @if(($expert->profile_visibility ?? null) === 'Private')<div class="form-text text-warning">The profile is Private, so only the expert can open this link until it is made Public (Contact &amp; SEO tab).</div>@endif
    @else
        <div class="form-text">The link appears here once the expert is saved.</div>
    @endif

    <h6 class="mt-4 mb-1 fw-bold">Track record</h6>
    <p class="text-muted small mb-2">Shown as figures in the "Top achievements" section. Write them as you would say them, e.g. "92%" or "40+ students".</p>
    <div class="row g-3">
        @foreach(Expert::TRACK_RECORD as $k => [$l, $ph])
            <div class="col-md-4"><label class="form-label">{{ $l }}</label><input type="text" class="form-control" name="{{ $k }}" maxlength="120" value="{{ $v($k) }}" placeholder="{{ $ph }}"></div>
        @endforeach
    </div>

    <h6 class="mt-4 mb-1 fw-bold">Beyond the classroom</h6>
    <p class="text-muted small mb-2">The "Talks, Research &amp; Network" section. One item per line.</p>
    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Awards &amp; recognition <small class="text-muted">(tags; shown with Certifications)</small></label>
            <select class="form-control select2-tags" name="awards_recognition[]" multiple>@foreach($tags('awards_recognition') as $t)<option value="{{ $t }}" selected>{{ $t }}</option>@endforeach</select>
        </div>
        @foreach(Expert::BEYOND as $k => [$l, $ph])
            <div class="col-md-{{ $loop->last && $loop->count % 2 ? 12 : 6 }}"><label class="form-label">{{ $l }}</label><textarea class="form-control" name="{{ $k }}" rows="3" placeholder="{{ $ph }}">{{ $lines($k) }}</textarea></div>
        @endforeach
        @foreach(Expert::NETWORK as $k => [$l, $ph])
            <div class="col-md-6"><label class="form-label">{{ $l }}</label><input type="text" class="form-control" name="{{ $k }}" maxlength="255" value="{{ $v($k) }}" placeholder="{{ $ph }}"></div>
        @endforeach
    </div>
</div>
<script>
    (function () { // add / remove rows of the repeaters above; new rows get the next free index
        document.addEventListener('click', function (ev) {
            var add = ev.target.closest('[data-rep-add]'), del = ev.target.closest('[data-rep-del]');
            if (add) {
                var box = document.querySelector('[data-rep="' + add.dataset.repAdd + '"]'), last = box.querySelector('.rep-row:last-child'), row = last.cloneNode(true);
                var n = box.dataset.next ? +box.dataset.next : box.querySelectorAll('.rep-row').length;
                box.dataset.next = n + 1;
                row.querySelectorAll('input').forEach(function (i) { i.value = ''; i.name = i.name.replace(/\[\d+\]/, '[' + n + ']'); });
                box.appendChild(row);
            }
            if (del) {
                var r = del.closest('.rep-row'), rows = r.parentNode.querySelectorAll('.rep-row');
                if (rows.length > 1) r.remove(); else r.querySelectorAll('input').forEach(function (i) { i.value = ''; });
            }
        });
    })();
</script>
