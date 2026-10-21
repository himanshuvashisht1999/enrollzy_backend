@extends('admin.layouts.master')

@section('title', 'Split a brand')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Split a multi-university brand</h4>
        <p class="text-muted mb-0">Amity, SRM and similar brands are several legal universities stored as one. Choose the campuses that belong to a different university and move them (with their departments and programmes). Nothing is copied or deleted, and every move can be undone.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.quality.groups.index') }}" class="btn btn-light border">Groups</a>
        <a href="{{ route('admin.quality.identity.index') }}" class="btn btn-light border">Identity review</a>
    </div>
</div>

@if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-8"><label class="form-label fw-bold">1. Organisation to split</label>
            <select name="from" id="fromOrg" class="form-select" data-ajax="{{ route('admin.quality.moves.index') }}">
                @if($from)<option value="{{ $from->id }}" selected>{{ $from->name }} (#{{ $from->id }})</option>@endif
            </select></div>
        <div class="col-md-4"><button class="btn btn-primary">Load campuses</button></div>
    </form>
</div></div>

@if($from)
    @php $p = $preview; @endphp
    <form method="POST" id="moveForm" action="{{ route('admin.quality.moves.preview') }}">
        @csrf
        <input type="hidden" name="from_organisation_id" value="{{ $from->id }}">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 fw-bold">2. Campuses of {{ $from->name }} ({{ $campuses->count() }})</div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="bg-light"><tr><th class="ps-4" style="width:40px"><input type="checkbox" class="form-check-input" onclick="document.querySelectorAll('.cb').forEach(c => c.checked = this.checked)"></th><th>Campus</th><th>Role</th><th>Departments</th><th>Programmes</th></tr></thead>
                <tbody>
                @foreach($campuses as $c)
                    <tr>
                        <td class="ps-4"><input type="checkbox" name="campus_ids[]" value="{{ $c->id }}" class="form-check-input cb" @checked($p && in_array($c->id, $p['campus_ids']))></td>
                        <td class="fw-bold">{{ $c->campus_name }}</td>
                        <td>{{ \App\Support\Quality\IdentityFields::CAMPUS_ROLES[$c->campus_role] ?? '-' }}</td>
                        <td>{{ number_format($counts[$c->id]['departments']) }}</td>
                        <td>{{ number_format($counts[$c->id]['courses']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <div class="fw-bold mb-2">3. Move them to</div>
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="target_mode" value="existing" id="tmE" @checked(old('target_mode', $p['request']['target_mode'] ?? 'existing') === 'existing')><label class="form-check-label" for="tmE">An existing organisation</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="target_mode" value="new" id="tmN" @checked(old('target_mode', $p['request']['target_mode'] ?? '') === 'new')><label class="form-check-label" for="tmN">A new organisation</label></div>
                </div>
                <div class="col-md-6" id="existingBox"><label class="form-label">Target organisation</label>
                    <select name="to_organisation_id" id="toOrg" class="form-select">
                        @if(!empty($p['request']['to_organisation_id']))<option value="{{ $p['request']['to_organisation_id'] }}" selected>{{ $p['request']['to_name'] }}</option>@endif
                    </select></div>
                <div class="col-md-6 d-none" id="newBox"><label class="form-label">New organisation name</label>
                    <input name="new_name" value="{{ old('new_name', $p['request']['new_name'] ?? '') }}" class="form-control" placeholder="e.g. Amity University Gurugram">
                    <div class="form-text">Starts with the same organisation type and group as {{ $from->name }}. Fill in the rest on its edit page.</div></div>
                <div class="col-12"><label class="form-label">Note (why)</label><input name="note" value="{{ old('note', $p['request']['note'] ?? '') }}" class="form-control" maxlength="1000"></div>
            </div>
            <button class="btn btn-primary mt-3">Preview the move</button>
        </div></div>
    </form>

    @if($p)
        <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 {{ $p['blockers'] ? 'border-danger' : 'border-success' }}"><div class="card-body">
            <div class="fw-bold mb-2">Preview (nothing changed yet)</div>
            <div class="mb-2">
                @foreach($p['counts'] as $t => $n)<span class="badge bg-light text-dark border me-1">{{ number_format($n) }} {{ str_replace('_', ' ', $t) }}</span>@endforeach
                &rarr; <strong>{{ ($p['request']['target_mode'] ?? '') === 'new' ? 'NEW: ' . ($p['request']['new_name'] ?? '') : $p['request']['to_name'] }}</strong>
            </div>
            @foreach($p['blockers'] as $b)<div class="alert alert-danger py-2 mb-2">{{ $b }}</div>@endforeach
            @foreach($p['warnings'] as $w)<div class="alert alert-warning py-2 mb-2">{{ $w }}</div>@endforeach
            @unless($p['blockers'])
                <form method="POST" action="{{ route('admin.quality.moves.apply') }}" data-confirm="Move these campuses now? You can undo it afterwards.">
                    @csrf
                    <input type="hidden" name="from_organisation_id" value="{{ $from->id }}">
                    <input type="hidden" name="target_mode" value="{{ $p['request']['target_mode'] }}">
                    <input type="hidden" name="to_organisation_id" value="{{ $p['request']['to_organisation_id'] ?? '' }}">
                    <input type="hidden" name="new_name" value="{{ $p['request']['new_name'] ?? '' }}">
                    <input type="hidden" name="note" value="{{ $p['request']['note'] ?? '' }}">
                    @foreach($p['campus_ids'] as $id)<input type="hidden" name="campus_ids[]" value="{{ $id }}">@endforeach
                    <button class="btn btn-success"><i class="fas fa-random me-1"></i> Move now</button>
                </form>
            @endunless
        </div></div>
    @endif
@endif

<div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white border-0 fw-bold">Move history</div>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">#</th><th>From</th><th>To</th><th>Campuses</th><th>When</th><th>Status</th><th class="text-end pe-4"></th></tr></thead>
        <tbody>
        @forelse($history as $m)
            @php $mv = json_decode($m->moved, true); @endphp
            <tr>
                <td class="ps-4">{{ $m->id }}</td>
                <td>{{ $orgNames[$m->from_organisation_id] ?? '#' . $m->from_organisation_id }}</td>
                <td>{{ $orgNames[$m->to_organisation_id] ?? '#' . $m->to_organisation_id }}@if($m->created_target) <span class="badge bg-info-subtle text-info">new</span>@endif</td>
                <td>{{ count($mv['campuses'] ?? []) }} <span class="small text-muted">+ {{ number_format(count($mv['departments'] ?? [])) }} dept, {{ number_format(count($mv['organisation_courses'] ?? [])) }} programmes</span></td>
                <td>{{ \Illuminate\Support\Carbon::parse($m->created_at)->format('d M Y H:i') }}</td>
                <td><span class="badge {{ $m->status === 'applied' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($m->status) }}</span></td>
                <td class="text-end pe-4">@if($m->status === 'applied')<form action="{{ route('admin.quality.moves.undo', $m->id) }}" method="POST" data-confirm="Undo this move?">@csrf<button class="btn btn-sm btn-light border">Undo</button></form>@endif</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No moves yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection

@push('scripts')
<script>
    // Organisation pickers search server-side: there are hundreds of organisations.
    const orgSearch = @json(route('admin.quality.moves.search'));
    function orgSelect(sel) {
        $(sel).select2({
            width: '100%', minimumInputLength: 2, placeholder: 'Type at least 2 letters',
            ajax: { url: orgSearch, dataType: 'json', delay: 250, data: p => ({ q: p.term }), processResults: d => ({ results: d }) }
        });
    }
    orgSelect('#fromOrg'); orgSelect('#toOrg');
    function syncMode() {
        const isNew = document.getElementById('tmN')?.checked;
        document.getElementById('newBox')?.classList.toggle('d-none', !isNew);
        document.getElementById('existingBox')?.classList.toggle('d-none', !!isNew);
    }
    document.querySelectorAll('[name=target_mode]').forEach(r => r.addEventListener('change', syncMode));
    syncMode();
</script>
@endpush
