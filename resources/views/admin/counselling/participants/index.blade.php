@extends('admin.layouts.master')

@section('title', 'Participating institutes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Participating institutes <span class="text-muted fs-6">{{ $cycle->counselling->name }} · {{ $cycle->title }}</span></h4>
        <p class="text-muted mb-0">College + course + quota taking part this year. {{ number_format($total) }} in total. Thousands of rows? Use the CSV import.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.cycles.show', $cycle) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Cycle</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.create', ['type' => 'participants', 'cycle' => $cycle->id]) }}" class="btn btn-outline-primary"><i class="fas fa-file-csv me-1"></i> Import CSV</a>@endcan
        @can('cns-counsellings-edit')
            <button class="btn btn-primary" onclick="openBulkAdd()"><i class="fas fa-search-plus me-1"></i> Search &amp; add</button>
            <button class="btn btn-outline-primary" onclick="openParticipant()" title="One college + course + quota"><i class="fas fa-plus me-1"></i> Add one row</button>
        @endcan
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search college, course or code"></div>
    <div class="col-md-3"><select name="quota" class="form-select custom-select2"><option value="">All quotas</option>@foreach($quotas as $q)<option value="{{ $q->id }}" @selected(request('quota') == $q->id)>{{ $q->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="institute_type" class="form-select custom-select2"><option value="">All types</option>@foreach(\App\Support\Counselling\CnsBlocks::INSTITUTE_TYPES as $k => $v)<option value="{{ $k }}" @selected(request('institute_type') === $k)>{{ $v }}</option>@endforeach</select></div>
    <div class="col-md-1"><button class="btn btn-light border w-100">Go</button></div>
</form>

{{-- one row per institute: its name, a link with its number of courses (opens them, with fees and quotas) and its fee range --}}
@php $inr = function ($n) { $n = (string) round((float) $n); $last = substr($n, -3); $rest = substr($n, 0, -3); return '₹' . ($rest !== '' ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' : '') . $last; };
    $fee = fn ($min, $max) => $min === null ? '-' : ($inr($min) . ($max !== null && $max != $min ? ' – ' . $inr($max) : '')); @endphp
<div class="small text-muted mb-2">{{ number_format($colleges->total()) }} {{ \Illuminate\Support\Str::plural('institute', $colleges->total()) }}{{ (request()->filled('search') || request()->filled('quota') || request()->filled('institute_type')) ? ' match' : '' }}. Click the number of courses to see them with their fees and quotas.</div>
<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4" style="width:50px">#</th><th>Institute</th><th>Courses</th><th>Tuition / yr <span class="fw-normal text-muted small">(lowest – highest)</span></th><th>Quotas</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($colleges as $i => $college)
            @php $label = ($college->name ?? '') . (($cities[$college->organisation_id] ?? null) ? ' — ' . $cities[$college->organisation_id] : ''); @endphp
            <tr>
                <td class="ps-4 text-muted">{{ $colleges->firstItem() + $i }}</td>
                <td>
                    <div class="fw-semibold">{{ $college->name ?? '(deleted institute #' . $college->organisation_id . ')' }}</div>
                    @if($cities[$college->organisation_id] ?? null)<div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $cities[$college->organisation_id] }}</div>@endif
                </td>
                <td class="text-nowrap"><a href="#" class="fw-semibold text-decoration-none" data-open-courses="{{ (int) $college->organisation_id }}" onclick="event.preventDefault(); openCourses({{ (int) $college->organisation_id }}, @js($college->name ?? ''))"><i class="fas fa-list-ul me-1"></i>{{ $college->courses }} {{ \Illuminate\Support\Str::plural('course', $college->courses) }}</a></td>
                <td class="text-nowrap">{{ $fee($college->fee_min, $college->fee_max) }}</td>
                <td class="text-muted">{{ $college->quotas }}</td>
                <td class="text-end pe-4 text-nowrap">
                    @can('cns-counsellings-edit')
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openBulkAdd({{ (int) $college->organisation_id }}, @js($label))"><i class="fas fa-plus me-1"></i>Add courses</button>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">{{ (request()->filled('search') || request()->filled('quota') || request()->filled('institute_type')) ? 'No institute matches.' : 'No participating institutes yet.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $colleges->links() }}</div>

{{-- an institute's courses, loaded when its course count is clicked --}}
<div class="modal fade" id="coursesModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered"><div class="modal-content">
    <div class="modal-header py-2"><h5 class="modal-title fs-6 fw-bold" id="coursesTitle">Courses</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body pc-courses" id="coursesBody"></div>
</div></div></div>

@can('cns-counsellings-edit')
<div class="modal fade" id="participantModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="participantForm" method="POST" action="{{ route('admin.counselling.participants.store', $cycle) }}">
        @csrf <span id="pMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Participant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">College *</label><select name="organisation_id" id="p_organisation_id" class="form-select custom-select2" required></select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Course *</label><select name="course_id" id="p_course_id" class="form-select custom-select2" required></select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Quota *</label><select name="quota_id" id="p_quota_id" class="form-select custom-select2" required><option value="">Select</option>@foreach($quotas as $q)<option value="{{ $q->id }}">{{ $q->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Institute type</label><select name="institute_type" id="p_institute_type" class="form-select custom-select2"><option value="">-</option>@foreach(\App\Support\Counselling\CnsBlocks::INSTITUTE_TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Authority code</label><input type="text" name="institute_code" id="p_institute_code" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Annual tuition fee</label><input type="number" step="any" min="0" name="annual_tuition_fee" id="p_annual_tuition_fee" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Other fees</label><input type="number" step="any" min="0" name="other_fees" id="p_other_fees" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Total seats</label><input type="number" min="0" name="total_seats" id="p_total_seats" class="form-control"></div>
            <div class="col-12"><label class="form-label fw-bold">Bond details</label><textarea name="bond_details" id="p_bond_details" rows="2" class="form-control"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Search & add: one college, any of its courses × any quotas, in one go --}}
{{-- the form sits between .modal-content and .modal-body, so it is made the flex column itself (.b-form): without that the
     body cannot scroll and the footer with the Add button falls below the screen --}}
<style>
    #bulkModal .b-form { display: flex; flex-direction: column; min-height: 0; max-height: 100%; overflow: hidden; }
    #bulkModal .modal-body { overflow-y: auto; min-height: 0; padding: .9rem 1rem; }
    #bulkModal .b-step { font-weight: 700; font-size: .9rem; margin-bottom: .35rem; }
    #bulkModal #b_courses { max-height: 34vh; overflow-y: auto; }
    #bulkModal .b-course { font-size: .9rem; cursor: pointer; }
    #bulkModal .b-course:hover { background: #f5f8ff; }
    #bulkModal .btn-check + .btn { padding: .15rem .55rem; font-size: .8rem; }
    #bulkModal details > summary { cursor: pointer; font-weight: 700; font-size: .9rem; list-style: revert; }
    #bulkModal .modal-footer { padding: .6rem 1rem; }
</style>
<div class="modal fade" id="bulkModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"><div class="modal-content">
    <form id="bulkForm" class="b-form" method="POST" action="{{ route('admin.counselling.participants.bulk', $cycle) }}">
        @csrf
        <div class="modal-header py-2">
            <h5 class="modal-title mb-0 fs-6 fw-bold">Search &amp; add participating institutes</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><div class="d-grid gap-3">
            <div>
                <div class="b-step">1. College *</div>
                <select name="organisation_id" id="b_org" class="form-select" required></select>
            </div>

            <div id="b_courses_wrap" hidden>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                    <div class="b-step mb-0">2. Courses * <span class="text-muted fw-normal small" id="b_course_count"></span></div>
                    <div class="d-flex gap-1 align-items-center">
                        <input type="search" id="b_filter" class="form-control form-control-sm" placeholder="Filter" style="width:150px">
                        <button type="button" class="btn btn-sm btn-light border" id="b_all" title="Tick all courses shown">All</button>
                        <button type="button" class="btn btn-sm btn-light border" id="b_none" title="Untick all">None</button>
                    </div>
                </div>
                <div id="b_courses" class="border rounded-3 p-1"></div>
                <div class="mt-2"><select id="b_extra" class="form-select form-select-sm"></select></div>
            </div>

            <div id="b_quota_wrap" hidden>
                <div class="b-step">3. Quotas *</div>
                <div class="d-flex flex-wrap gap-1" id="b_quotas">
                    @foreach($quotas as $q)
                        <input type="checkbox" class="btn-check b-quota" name="quota_ids[]" value="{{ $q->id }}" id="bq_{{ $q->id }}" autocomplete="off">
                        <label class="btn btn-outline-primary" for="bq_{{ $q->id }}">{{ $q->name }}</label>
                    @endforeach
                </div>
            </div>

            {{-- nothing more is asked: type and yearly fee come from the college's own record; the counselling-only details
                 (authority code, bond, seats) come from the CSV or a row's edit button --}}
            <div id="b_details_wrap" class="small text-muted border-top pt-2" hidden>
                Filled in from the college's record: <b>institute type</b> (<span id="b_type_note">not set on the college</span>) and each course's <b>yearly fee</b> (shown beside it).
                Authority code, bond and seats are counselling details: add them with a row's <i class="fas fa-edit"></i> button or the CSV import.
            </div>
        </div></div>
        <div class="modal-footer justify-content-between flex-nowrap">
            <span class="small" id="b_summary">Search a college to start.</span>
            <div class="text-nowrap"><button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button> <button class="btn btn-sm btn-primary" id="b_submit" disabled>Add rows</button></div>
        </div>
    </form>
</div></div></div>
@endcan
@endsection

@push('scripts')
<script>
    const LOOKUP = @json(route('admin.counselling.imports.lookup'));
    const pStore = @json(route('admin.counselling.participants.store', $cycle));
    const pBase = @json(url('admin/counselling/participants'));
    function ajaxSelect(sel, type, parent) {
        $(sel).select2({ width: '100%', dropdownParent: parent, placeholder: 'Search...', minimumInputLength: 0,
            ajax: { url: LOOKUP, dataType: 'json', delay: 250, data: p => ({ q: p.term || '', type }), processResults: d => d } });
    }
    function openParticipant(row) {
        const modal = $('#participantModal'), form = document.getElementById('participantForm');
        form.reset();
        ['organisation_id', 'course_id'].forEach(f => { $('#p_' + f).empty(); });
        ajaxSelect('#p_organisation_id', 'organisation', modal);
        ajaxSelect('#p_course_id', 'course', modal);
        ['quota_id', 'institute_type'].forEach(f => $('#p_' + f).val('').trigger('change'));
        form.action = row ? pBase + '/' + row.id : pStore;
        $('#pMethod').html(row ? '<input type="hidden" name="_method" value="PUT">' : '');
        if (row) {
            $('#p_organisation_id').append(new Option(row.org_name, row.organisation_id, true, true)).trigger('change');
            $('#p_course_id').append(new Option(row.course_name, row.course_id, true, true)).trigger('change');
            ['quota_id', 'institute_type'].forEach(f => $('#p_' + f).val(row[f] ?? '').trigger('change'));
            ['institute_code', 'annual_tuition_fee', 'other_fees', 'total_seats', 'bond_details'].forEach(f => $('#p_' + f).val(row[f] ?? ''));
        }
        new bootstrap.Modal(document.getElementById('participantModal')).show();
    }

    // ---- an institute's courses (from its course count) ----
    const coursesUrl = @json(url('admin/counselling/cycles/' . $cycle->id . '/participants/institutes'));
    function openCourses(orgId, name) {
        document.getElementById('coursesTitle').textContent = name || 'Courses';
        const body = document.getElementById('coursesBody');
        body.innerHTML = '<div class="text-muted small p-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading courses…</div>';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('coursesModal')).show();
        fetch(coursesUrl + '/' + orgId + '/courses', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
            .then(html => { body.innerHTML = html; })
            .catch(() => { body.innerHTML = '<div class="text-danger small p-3">Could not load the courses. Please try again.</div>'; });
    }

    // ---- Search & add ----
    const bColleges = @json(route('admin.counselling.participants.colleges', $cycle));
    const bCoursesUrl = @json(url('admin/counselling/cycles/' . $cycle->id . '/participants/colleges')) ;
    const bQuotaNames = @json($quotas->pluck('name', 'id'));
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let bReady = false;
    function bRow(c, checked) {
        const had = (c.quotas || []).map(id => '<span class="badge bg-success-subtle text-success border border-success-subtle ms-1">' + esc(bQuotaNames[id] || id) + '</span>').join('');
        return '<label class="d-flex align-items-center gap-2 px-2 py-1 rounded b-course" data-t="' + esc((c.name + ' ' + (c.level || '')).toLowerCase()) + '" data-quotas="' + (c.quotas || []).join(',') + '">'
            + '<input type="checkbox" class="form-check-input mt-0 b-cid" name="course_ids[]" value="' + c.id + '"' + (checked ? ' checked' : '') + '>'
            + '<span class="flex-grow-1">' + esc(c.name) + (c.level ? ' <span class="badge bg-light text-dark border">' + esc(c.level) + '</span>' : '') + had + '</span>'
            + (c.fee ? '<span class="small text-muted text-nowrap">' + (c.fee_varies ? 'from ' : '') + '₹' + Number(c.fee).toLocaleString('en-IN') + '/yr</span>' : '<span class="small text-muted">no fee on record</span>') + '</label>';
    }
    function bSummary() {
        const courses = [...document.querySelectorAll('.b-cid:checked')], quotas = [...document.querySelectorAll('.b-quota:checked')].map(q => q.value);
        let total = courses.length * quotas.length, dup = 0;
        courses.forEach(c => { const had = (c.closest('.b-course').dataset.quotas || '').split(','); quotas.forEach(q => { if (had.includes(q)) dup++; }); });
        const s = document.getElementById('b_summary');
        if (!$('#b_org').val()) s.textContent = 'Search a college to start.';
        else if (!courses.length || !quotas.length) s.textContent = 'Tick at least one course and one quota.';
        else s.innerHTML = '<b>' + courses.length + '</b> course' + (courses.length > 1 ? 's' : '') + ' × <b>' + quotas.length + '</b> quota' + (quotas.length > 1 ? 's' : '') + ' = <b>' + (total - dup) + '</b> new row' + (total - dup === 1 ? '' : 's') + (dup ? ' <span class="text-muted">(' + dup + ' already added, skipped)</span>' : '');
        document.getElementById('b_submit').disabled = !(courses.length && quotas.length && total - dup > 0);
    }
    function bLoadCourses(orgId) {
        const box = document.getElementById('b_courses');
        box.innerHTML = '<div class="text-muted small p-2">Loading courses…</div>';
        ['b_courses_wrap', 'b_quota_wrap', 'b_details_wrap'].forEach(id => document.getElementById(id).hidden = false);
        fetch(bCoursesUrl + '/' + orgId + '/courses', { headers: { 'Accept': 'application/json' } }).then(r => r.json()).then(d => {
            box.innerHTML = d.courses.length ? d.courses.map(c => bRow(c, false)).join('') : '<div class="text-muted small p-2">This college has no courses in the CRM yet. Add them from the master list below.</div>';
            document.getElementById('b_course_count').textContent = d.courses.length ? '(' + d.courses.length + ' listed)' : '';
            document.getElementById('b_type_note').textContent = d.type || 'not set on the college';
            document.getElementById('b_filter').value = '';
            bSummary();
        }).catch(() => { box.innerHTML = '<div class="text-danger small p-2">Could not load the courses. Try again.</div>'; });
    }
    function openBulkAdd(orgId, orgName) {
        const modal = $('#bulkModal');
        if (!bReady) {
            $('#b_org').select2({ width: '100%', dropdownParent: modal, placeholder: 'Search a college…', minimumInputLength: 2,
                ajax: { url: bColleges, dataType: 'json', delay: 250, data: p => ({ q: p.term || '' }), processResults: d => d },
                templateResult: r => r.loading ? r.text : $('<span>' + esc(r.text) + ' <small class="text-muted">· ' + (r.courses || 0) + ' courses' + (r.added ? ', ' + r.added + ' rows already in this cycle' : '') + '</small></span>') })
                .on('change', function () { if (this.value) bLoadCourses(this.value); });
            $('#b_extra').select2({ width: '100%', dropdownParent: modal, placeholder: '+ Add a course the college does not list (master list)…', minimumInputLength: 2,
                ajax: { url: LOOKUP, dataType: 'json', delay: 250, data: p => ({ q: p.term || '', type: 'course' }), processResults: d => d } })
                .on('select2:select', function (e) {
                    const c = e.params.data, box = document.getElementById('b_courses'), have = box.querySelector('.b-cid[value="' + c.id + '"]');
                    if (have) have.checked = true;
                    else { if (!box.querySelector('.b-course')) box.innerHTML = ''; box.insertAdjacentHTML('afterbegin', bRow({ id: c.id, name: c.text, quotas: [] }, true)); }
                    $(this).val(null).trigger('change'); bSummary();
                });
            document.getElementById('bulkForm').addEventListener('change', bSummary);
            document.getElementById('b_filter').addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                document.querySelectorAll('.b-course').forEach(l => l.classList.toggle('d-none', q !== '' && !l.dataset.t.includes(q)));
            });
            document.getElementById('b_all').onclick = () => { document.querySelectorAll('.b-course:not(.d-none) .b-cid').forEach(c => c.checked = true); bSummary(); };
            document.getElementById('b_none').onclick = () => { document.querySelectorAll('.b-cid').forEach(c => c.checked = false); bSummary(); };
            document.getElementById('bulkForm').addEventListener('submit', () => { const b = document.getElementById('b_submit'); b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding… please wait'; document.getElementById('b_summary').textContent = 'A big college can take up to a minute. Do not reload the page.'; });
            bReady = true;
        }
        new bootstrap.Modal(document.getElementById('bulkModal')).show();
        // "Add courses" on a college line: open with that college already picked
        if (orgId) $('#b_org').empty().append(new Option(orgName || ('College #' + orgId), orgId, true, true)).trigger('change');
    }
    @if(request('open'))
        // after adding: show that institute's courses again
        $(() => document.querySelector('[data-open-courses="{{ (int) request('open') }}"]')?.click());
    @endif
    @if(request('add'))
        $(() => openBulkAdd());
    @endif
</script>
@endpush
