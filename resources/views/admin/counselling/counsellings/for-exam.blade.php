@extends('admin.layouts.master')

@section('title', 'Counselling for ' . $exam->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Counselling after {{ $exam->name }}</h4>
        <p class="text-muted mb-0">One exam can feed many counsellings (national and every state).</p>
    </div>
    <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Exams</a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Mapped counsellings</h6>
    @forelse($mapped as $m)
        <div class="d-flex justify-content-between align-items-center border-top py-2">
            <div>
                <a href="{{ route('admin.counselling.counsellings.show', $m->counselling) }}" class="fw-semibold text-decoration-none">{{ $m->counselling->name }}</a>
                <span class="badge bg-light text-dark border">{{ ucfirst($m->role) }}</span>
                <div class="small text-muted">{{ $m->counselling->summaryLine() }}
                    · current cycle: {{ $m->counselling->currentCycle?->title ?? 'none' }}</div>
            </div>
            <span class="badge {{ $m->counselling->visibility === 'public' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($m->counselling->visibility) }}</span>
        </div>
    @empty
        <p class="text-muted small mb-0">No counselling is mapped to this exam yet.</p>
    @endforelse
</div></div>

@can('cns-counsellings-edit')
<div class="row g-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Map an existing counselling</h6>
            <form action="{{ route('admin.counselling.exams.map', $exam) }}" method="POST" class="row g-2">
                @csrf
                <div class="col-md-8"><label class="form-label small fw-bold mb-1">Counselling</label><select name="counselling_id" class="form-select @error('counselling_id') is-invalid @enderror" required><option value="">Select counselling</option>
                    @foreach($available as $c)<option value="{{ $c->id }}" @selected(old('counselling_id') == $c->id)>{{ $c->name }} ({{ \App\Models\Counselling\Counselling::LEVELS[$c->level] }})</option>@endforeach</select>
                    @error('counselling_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label small fw-bold mb-1">Role</label><select name="role" class="form-select"><option value="primary" @selected(old('role') !== 'additional')>Primary</option><option value="additional" @selected(old('role') === 'additional')>Additional</option></select></div>
                <div class="col-md-3"><label class="form-label small fw-bold mb-1">Valid from</label><input type="number" name="valid_from_year" value="{{ old('valid_from_year') }}" class="form-control @error('valid_from_year') is-invalid @enderror" placeholder="Year" min="1990" max="2100">
                    @error('valid_from_year')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label small fw-bold mb-1">Valid to</label><input type="number" name="valid_to_year" value="{{ old('valid_to_year') }}" class="form-control @error('valid_to_year') is-invalid @enderror" placeholder="Year" min="1990" max="2100">
                    @error('valid_to_year')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label small fw-bold mb-1">Seat scope note</label><input type="text" name="seat_scope_note" value="{{ old('seat_scope_note') }}" class="form-control" maxlength="255" placeholder="e.g. IIT seats only"></div>
                <div class="col-12"><button class="btn btn-outline-primary">Map</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Create a new counselling</h6>
            @can('cns-counsellings-add')
            <a href="{{ route('admin.counselling.counsellings.create', ['exam_id' => $exam->id]) }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create and map to {{ $exam->name }}</a>
            @else <p class="text-muted small mb-0">You do not have permission to create counsellings.</p> @endcan
        </div></div>
    </div>
</div>
@endcan
@endsection
