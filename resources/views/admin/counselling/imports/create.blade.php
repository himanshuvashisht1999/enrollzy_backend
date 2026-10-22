@extends('admin.layouts.master')

@section('title', 'New import')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">New import</h4>
    <a href="{{ route('admin.counselling.imports.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Imports</a>
</div>

<form action="{{ route('admin.counselling.imports.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><div class="row g-4">
        <div class="col-12">
            <label class="form-label fw-bold">What are you importing?</label>
            <div class="row g-2">
                @foreach([
                    'participants' => ['Participating institutes', 'College + course + quota taking part this year'],
                    'seats' => ['Seat matrix', 'Seats per college, course, quota and category. Creates a new version'],
                    'cutoffs' => ['Cutoffs', 'Opening / closing rank or score per round'],
                ] as $k => [$title, $desc])
                <div class="col-md-4"><label class="border rounded-3 p-3 d-block h-100" style="cursor:pointer">
                    <input type="radio" name="type" value="{{ $k }}" class="form-check-input me-2" @checked(old('type', $type) === $k)><strong>{{ $title }}</strong>
                    <div class="small text-muted mt-1">{{ $desc }}</div>
                    <a href="{{ route('admin.counselling.imports.template', $k) }}" class="small">Download template</a>
                </label></div>
                @endforeach
            </div>
        </div>

        <div class="col-md-6"><label class="form-label fw-bold">Counselling cycle *</label>
            <select name="cycle_id" id="cycle_id" class="form-select" required>
                <option value="">Select</option>
                @php
                    // cycles made twice before duplicates were blocked: show which is which
                    $dupes = collect($cycles)->groupBy(fn ($c) => $c->counselling_id . '|' . mb_strtolower($c->title))->filter(fn ($g) => $g->count() > 1)->keys()->all();
                @endphp
                @foreach($cycles as $c)<option value="{{ $c->id }}" @selected(old('cycle_id', $cycle?->id) == $c->id)>{{ $c->counselling->name }} · {{ $c->title }}@if(in_array($c->counselling_id . '|' . mb_strtolower($c->title), $dupes)) (duplicate: #{{ $c->id }}, created {{ $c->created_at?->format('j M Y, H:i') }}{{ $c->is_current ? ', current' : '' }})@endif</option>@endforeach
            </select></div>
        <div class="col-md-6" id="round_box"><label class="form-label fw-bold">Round *</label>
            <select name="round_id" id="round_id" class="form-select"><option value="">Select cycle first</option></select></div>

        <div class="col-md-6"><label class="form-label fw-bold">If rows already exist</label>
            <select name="mode" class="form-select">
                <option value="append" @selected(old('mode') === 'append')>Append: add to what is there (existing rows are flagged as duplicates)</option>
                <option value="replace" @selected(old('mode') === 'replace')>Replace: use only this file</option>
            </select>
            <div class="form-text" id="mode_help"></div></div>
        <div class="col-md-6"><label class="form-label fw-bold">CSV file *</label>
            <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
            <div class="form-text">Up to 50,000 rows / 20 MB. Comma, semicolon or tab separated; UTF-8 or Excel CSV.</div></div>

        <div class="col-12" id="seat_box">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-bold">Version label</label><input type="text" name="label" class="form-control" value="{{ old('label') }}" placeholder="Revised seat matrix 2"></div>
                <div class="col-md-3"><label class="form-label fw-bold">Published on</label><input type="date" name="published_on" class="form-control" value="{{ old('published_on') }}"></div>
                <div class="col-md-5"><label class="form-label fw-bold">Source URL</label><input type="url" name="source_url" class="form-control" value="{{ old('source_url') }}" placeholder="Official notice"></div>
                <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="make_live" value="0"><input class="form-check-input" type="checkbox" name="make_live" id="make_live" value="1" @checked(old('make_live', true))><label class="form-check-label" for="make_live">Make the new version live once confirmed</label></div></div>
            </div>
        </div>
    </div></div></div>
    <div class="alert alert-light border small">
        Colleges and courses are matched against your masters (authority code first, then exact name, then learned aliases). A name that cannot be matched stays an error until you pick the right record or add it to the master:
        <strong>the importer never creates colleges or courses.</strong> Nothing is saved until you confirm the preview.
    </div>
    <button class="btn btn-primary px-4"><i class="fas fa-search me-2"></i> Read file and preview</button>
</form>
@endsection

@push('scripts')
<script>
    const cycles = @json($cycles->mapWithKeys(fn ($c) => [$c->id => $c->rounds->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values()]));
    const preRound = @json(old('round_id', $round?->id));
    const modeHelp = {
        participants: 'Replace removes every participant of the cycle first. Undo brings them back.',
        seats: 'Append = the live version plus these rows. Replace = only these rows. Either way a NEW version is created; old versions are kept.',
        cutoffs: 'Replace removes the round\'s existing cutoffs first. Undo brings them back.',
    };
    function sync() {
        const type = document.querySelector('input[name="type"]:checked')?.value || 'participants';
        document.getElementById('round_box').style.display = type === 'participants' ? 'none' : '';
        document.getElementById('seat_box').style.display = type === 'seats' ? '' : 'none';
        document.getElementById('mode_help').textContent = modeHelp[type];
        const sel = document.getElementById('round_id'), list = cycles[document.getElementById('cycle_id').value] || [];
        const keep = sel.value || preRound;
        sel.innerHTML = '<option value="">' + (list.length ? 'Select round' : 'This cycle has no rounds yet') + '</option>' + list.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
        if (keep) sel.value = keep;
    }
    document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', sync));
    document.getElementById('cycle_id').addEventListener('change', sync);
    sync();
</script>
@endpush
