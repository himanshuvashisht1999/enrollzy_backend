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
                <div class="small text-muted">{{ \App\Models\Counselling\Counselling::LEVELS[$m->counselling->level] }} · {{ $m->counselling->conductingBody->name ?? '-' }}
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
                <div class="col-md-7"><select name="counselling_id" class="form-select" required><option value="">Select counselling</option>
                    @foreach($available as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ \App\Models\Counselling\Counselling::LEVELS[$c->level] }})</option>@endforeach</select></div>
                <div class="col-md-3"><select name="role" class="form-select"><option value="primary">Primary</option><option value="additional">Additional</option></select></div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100">Map</button></div>
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
