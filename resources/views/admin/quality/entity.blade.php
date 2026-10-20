@extends('admin.layouts.master')

@section('title', 'Rankings & Accreditation: ' . $name)

@php
    $canEdit = auth('admin')->user()?->can('quality-edit');
    $canVerify = auth('admin')->user()?->can('quality-verify');
    $badge = fn ($s) => ['verified' => 'bg-success-subtle text-success', 'rejected' => 'bg-danger-subtle text-danger', 'unverified' => 'bg-warning-subtle text-warning'][$s] ?? 'bg-light text-dark';
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <div class="text-muted small">{{ $context }}</div>
        <h4 class="mb-0 fw-bold">{{ $name }}</h4>
        <p class="text-muted mb-0">Rankings &amp; accreditation recorded at <strong>{{ strtolower(\App\Support\Quality\Levels::LABELS[$type]) }}</strong> level.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $editUrl }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back to {{ strtolower(\App\Support\Quality\Levels::LABELS[$type]) }}</a>
        <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

@foreach($inherited as $inh)
    @if($inh['accreditations']->isNotEmpty() || $inh['rankings']->isNotEmpty())
        <div class="alert alert-light border small">
            <strong>From the {{ strtolower($inh['label']) }}</strong> (<a href="{{ $inh['url'] }}">{{ $inh['name'] }}</a>) - shown on this page for context, recorded there, not copied here:
            @foreach($inh['accreditations'] as $a)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $inh['label'] }} {{ $a->body->short_name }} {{ $a->grade }}</span>@endforeach
            @foreach($inh['rankings'] as $r)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $inh['label'] }} {{ $r->body->short_name ?: $r->body->name }} {{ $r->category->name }} {{ $r->rank_label }} @if($r->ranking_year)({{ $r->ranking_year }})@endif</span>@endforeach
        </div>
    @endif
@endforeach

{{-- Accreditations --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Accreditations, approvals &amp; recognitions</span>
        @if($canEdit)<button class="btn btn-sm btn-primary" onclick="openAcc()"><i class="fas fa-plus me-1"></i> Add</button>@endif
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Body</th><th>Grade</th><th>Validity</th><th>Status</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($accreditations as $a)
            @php $p = $a->only(['id', 'accreditation_body_id', 'grade', 'score', 'cycle', 'status', 'certificate_number', 'source_url', 'notes']) + ['valid_from' => $a->valid_from?->toDateString(), 'valid_to' => $a->valid_to?->toDateString()]; @endphp
            <tr>
                <td class="ps-4 fw-bold">{{ $a->body->short_name }}<div class="small text-muted fw-normal">{{ \App\Models\Quality\AccreditationBody::KINDS[$a->body->kind] }}</div></td>
                <td>{{ $a->grade ?: '-' }}@if($a->score) <span class="small text-muted">({{ rtrim(rtrim(number_format($a->score, 2), '0'), '.') }})</span>@endif</td>
                <td>@if($a->valid_from || $a->valid_to){{ $a->valid_from?->format('d M Y') ?? '?' }} &rarr; {{ $a->valid_to?->format('d M Y') ?? 'open' }}@else<span class="text-muted">not recorded</span>@endif</td>
                <td><span class="badge {{ $a->effective_status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ \App\Models\Quality\EntityAccreditation::STATUSES[$a->effective_status] }}</span></td>
                <td><span class="badge {{ $badge($a->verification_status) }}">{{ ucfirst($a->verification_status) }}</span>@if($a->legacy_source)<span class="badge bg-light text-muted border ms-1" title="Migrated from {{ $a->legacy_source }}">migrated</span>@endif</td>
                <td>@if($a->source_url)<a href="{{ $a->source_url }}" target="_blank" rel="noopener">link</a>@endif @if($a->evidence_file)<a href="{{ route('admin.quality.evidence', ['accreditation', $a->id]) }}" target="_blank">file</a>@endif @if(!$a->source_url && !$a->evidence_file)<span class="text-muted">none</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $a->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['accreditation', $a->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $a->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['accreditation', $a->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openAcc(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.accreditations.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this accreditation?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No accreditation recorded at this level.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

{{-- Rankings --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Rankings</span>
        @if($canEdit)<button class="btn btn-sm btn-primary" onclick="openRank()"><i class="fas fa-plus me-1"></i> Add</button>@endif
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Ranking</th><th>Year</th><th>Rank</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($rankings as $r)
            @php $p = $r->only(['id', 'ranking_body_id', 'ranking_category_id', 'ranking_year', 'rank_exact', 'rank_band_from', 'rank_band_to', 'score', 'total_ranked', 'external_institute_id', 'source_url', 'notes']) + ['published_on' => $r->published_on?->toDateString()]; @endphp
            <tr>
                <td class="ps-4 fw-bold">{{ $r->body->short_name ?: $r->body->name }}<div class="small text-muted fw-normal">{{ $r->category->name }}@if($r->body->trust_level === 'low') · low-trust source @endif</div></td>
                <td>@if($r->ranking_year){{ $r->ranking_year }}@else<span class="badge bg-warning-subtle text-warning">unknown</span>@endif</td>
                <td class="fw-bold">{{ $r->rank_label }}@if($r->total_ranked) <span class="small text-muted fw-normal">of {{ $r->total_ranked }}</span>@endif @if($r->isBand())<span class="badge bg-light text-muted border">band</span>@endif</td>
                <td><span class="badge {{ $badge($r->verification_status) }}">{{ ucfirst($r->verification_status) }}</span>@if($r->legacy_source)<span class="badge bg-light text-muted border ms-1" title="Migrated from {{ $r->legacy_source }}">migrated</span>@endif</td>
                <td>@if($r->source_url)<a href="{{ $r->source_url }}" target="_blank" rel="noopener">link</a>@endif @if($r->evidence_file)<a href="{{ route('admin.quality.evidence', ['ranking', $r->id]) }}" target="_blank">file</a>@endif @if(!$r->source_url && !$r->evidence_file)<span class="text-muted">none</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $r->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['ranking', $r->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $r->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['ranking', $r->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openRank(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.rankings.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this ranking?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No ranking recorded at this level.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

@if($canEdit)
{{-- Accreditation modal --}}
<div class="modal fade" id="accModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="accForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.quality.accreditations.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="accMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Accreditation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">Body <span class="text-danger">*</span></label>
                <select name="accreditation_body_id" id="accBody" class="form-select" required><option value="">Select</option>
                    @foreach($accBodies as $b)<option value="{{ $b->id }}" data-grade="{{ (int) $b->has_grade }}" data-options="{{ json_encode($b->grade_options ?? []) }}" data-validity="{{ (int) $b->has_validity }}">{{ $b->short_name }} - {{ $b->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-bold">Grade</label>
                <input name="grade" id="accGrade" list="gradeList" class="form-control" maxlength="30"><datalist id="gradeList"></datalist></div>
            <div class="col-md-3"><label class="form-label fw-bold">Score / CGPA</label><input type="number" step="0.01" name="score" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Cycle</label><input type="number" name="cycle" min="1" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Valid from</label><input type="date" name="valid_from" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Valid to</label><input type="date" name="valid_to" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">@foreach(\App\Models\Quality\EntityAccreditation::STATUSES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Certificate / approval number</label><input name="certificate_number" class="form-control" maxlength="100"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Source link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Evidence (pdf / image)</label><input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">Changing a verified record sends it back to unverified.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Ranking modal --}}
<div class="modal fade" id="rankModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="rankForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.quality.rankings.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="rankMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Ranking</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-bold">Ranking body <span class="text-danger">*</span></label>
                <select name="ranking_body_id" id="rankBody" class="form-select" required><option value="">Select</option>
                    @foreach($rankingBodies as $b)<option value="{{ $b->id }}">{{ $b->short_name ?: $b->name }}</option>@endforeach</select></div>
            <div class="col-md-5"><label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                <select name="ranking_category_id" id="rankCat" class="form-select" required><option value="">Select body first</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" data-body="{{ $c->ranking_body_id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-bold">Year <span class="text-danger">*</span></label><input type="number" name="ranking_year" min="1990" max="{{ now()->year + 1 }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-bold">Exact rank</label><input type="number" name="rank_exact" min="1" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">...or band from</label><input type="number" name="rank_band_from" min="1" class="form-control" placeholder="101"></div>
            <div class="col-md-3"><label class="form-label fw-bold">band to</label><input type="number" name="rank_band_to" min="1" class="form-control" placeholder="150"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Score</label><input type="number" step="0.001" name="score" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Total ranked</label><input type="number" name="total_ranked" min="1" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Body's institute ID</label><input name="external_institute_id" class="form-control" maxlength="40" placeholder="e.g. IR-E-U-0001"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Published on</label><input type="date" name="published_on" class="form-control"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Source link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Evidence (pdf / image)</label><input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">Give an exact rank <em>or</em> a band (many results are published as 101-150). Never enter a made-up number for a band.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endif
@endsection

@push('scripts')
<script>
    const accStore = @json(route('admin.quality.accreditations.store', [$type, $entity->getKey()]));
    const accBase = @json(url('admin/quality/accreditations'));
    const rankStore = @json(route('admin.quality.rankings.store', [$type, $entity->getKey()]));
    const rankBase = @json(url('admin/quality/rankings'));

    function fillForm(form, row) {
        Object.entries(row).forEach(([name, val]) => {
            if (name === 'id') return;
            const el = form.querySelector(`[name="${name}"]`);
            if (el) el.value = val ?? '';
        });
    }
    function syncAccBody() {
        const opt = document.querySelector('#accBody option:checked');
        const list = document.getElementById('gradeList');
        list.innerHTML = '';
        if (!opt || !opt.value) return;
        document.getElementById('accGrade').disabled = opt.dataset.grade !== '1';
        JSON.parse(opt.dataset.options || '[]').forEach(g => list.insertAdjacentHTML('beforeend', `<option value="${g}">`));
    }
    function syncRankCats() {
        const body = document.getElementById('rankBody').value;
        const sel = document.getElementById('rankCat');
        Array.from(sel.options).forEach(o => { if (o.value) { o.hidden = o.disabled = o.dataset.body !== body; } });
        if (sel.selectedOptions[0] && sel.selectedOptions[0].disabled) sel.value = '';
    }
    function openAcc(row) {
        const form = document.getElementById('accForm');
        form.reset();
        form.action = row ? accBase + '/' + row.id : accStore;
        document.getElementById('accMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) fillForm(form, row);
        syncAccBody();
        new bootstrap.Modal(document.getElementById('accModal')).show();
    }
    function openRank(row) {
        const form = document.getElementById('rankForm');
        form.reset();
        form.action = row ? rankBase + '/' + row.id : rankStore;
        document.getElementById('rankMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) fillForm(form, row);
        syncRankCats();
        if (row) document.getElementById('rankCat').value = row.ranking_category_id;
        new bootstrap.Modal(document.getElementById('rankModal')).show();
    }
    document.getElementById('accBody')?.addEventListener('change', syncAccBody);
    document.getElementById('rankBody')?.addEventListener('change', syncRankCats);
</script>
@endpush
