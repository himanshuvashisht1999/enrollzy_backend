@extends('admin.layouts.master')

@section('title', $counselling->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">{{ $counselling->name }}
            <span class="badge {{ $counselling->visibility === 'public' ? 'bg-success' : 'bg-secondary' }} fs-6 align-middle">{{ ucfirst($counselling->visibility) }}</span>
            @if($counselling->is_archived)<span class="badge bg-warning text-dark fs-6 align-middle">Archived</span>@endif
            @if($counselling->is_featured)<span class="badge bg-primary fs-6 align-middle">Featured</span>@endif
        </h4>
        <div class="text-muted">/{{ $counselling->slug }} · {{ $counselling->summaryLine() }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.counsellings.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
        @can('cns-counsellings-publish')
        <form action="{{ route('admin.counselling.counsellings.visibility', $counselling) }}" method="POST">@csrf @method('PATCH')
            <button class="btn {{ $counselling->visibility === 'public' ? 'btn-outline-secondary' : 'btn-success' }}">{{ $counselling->visibility === 'public' ? 'Move to Draft' : 'Publish' }}</button>
        </form>
        @endcan
        @can('cns-counsellings-edit')<a href="{{ route('admin.counselling.counsellings.edit', $counselling) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Edit</a>@endcan
    </div>
</div>

@if($counselling->visibility === 'draft' && $missing)
    <div class="alert alert-warning">To publish, still missing: <strong>{{ implode(', ', $missing) }}</strong></div>
@endif

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Details</h6>
            <dl class="row mb-0 small">
                <dt class="col-5">Mode</dt><dd class="col-7">{{ \App\Models\Counselling\Counselling::MODES[$counselling->mode] }}</dd>
                @if($counselling->states)<dt class="col-5">States</dt><dd class="col-7">{{ implode(', ', $counselling->states) }}</dd>@endif
                @if($counselling->owningOrganisation)<dt class="col-5">Owner</dt><dd class="col-7">{{ $counselling->owningOrganisation->name }}</dd>@endif
                <dt class="col-5">Program levels</dt><dd class="col-7">{{ collect($counselling->program_levels)->map(fn ($i) => $levelNames[$i] ?? null)->filter()->implode(', ') ?: '-' }}</dd>
                <dt class="col-5">Course categories</dt><dd class="col-7">{{ collect($counselling->course_categories)->map(fn ($i) => $categoryNames[$i] ?? null)->filter()->implode(', ') ?: '-' }}</dd>
                <dt class="col-5">Aliases</dt><dd class="col-7">{{ implode(', ', $counselling->aliases ?? []) ?: '-' }}</dd>
                <dt class="col-5">Helpline</dt><dd class="col-7">{{ $counselling->helpline ?: '-' }}</dd>
                <dt class="col-5">Website</dt><dd class="col-7">@if($counselling->official_website)<a href="{{ $counselling->official_website }}" target="_blank" rel="noopener">Open</a>@else - @endif</dd>
                <dt class="col-5">Portal</dt><dd class="col-7">@if($counselling->registration_portal_url)<a href="{{ $counselling->registration_portal_url }}" target="_blank" rel="noopener">Open</a>@else - @endif</dd>
            </dl>
        </div></div>
    </div>

    <div class="col-lg-8">
        {{-- Exams used --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Exams used</h6>
            @forelse($counselling->mappings as $m)
                <form action="{{ route('admin.counselling.mappings.update', $m) }}" method="POST" class="row g-2 align-items-center border-top py-2">
                    @csrf @method('PUT')
                    <div class="col-md-3 fw-semibold">{{ $m->exam->name }}</div>
                    <div class="col-md-2"><select name="role" class="form-select form-select-sm"><option value="primary" @selected($m->role === 'primary')>Primary</option><option value="additional" @selected($m->role === 'additional')>Additional</option></select></div>
                    <div class="col-md-2"><input type="number" name="valid_from_year" value="{{ $m->valid_from_year }}" class="form-control form-control-sm" placeholder="From year"></div>
                    <div class="col-md-2"><input type="number" name="valid_to_year" value="{{ $m->valid_to_year }}" class="form-control form-control-sm" placeholder="To year"></div>
                    <div class="col-md-3 text-end text-nowrap">
                        @can('cns-counsellings-edit')
                        <button class="btn btn-sm btn-light border"><i class="fas fa-save text-primary"></i></button>
                        <button class="btn btn-sm btn-light border text-danger" form="unmap{{ $m->id }}" data-confirm="{{ ($n = \App\Models\Counselling\Cutoff::where('exam_id', $m->exam_id)->whereIn('round_id', \App\Models\Counselling\Round::whereIn('cycle_id', $counselling->cycles->pluck('id'))->select('id'))->count()) ? "Remove this exam AND delete its $n cutoffs?" : 'Remove this exam from the counselling?' }}"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    </div>
                    <div class="col-12"><input type="text" name="seat_scope_note" value="{{ $m->seat_scope_note }}" class="form-control form-control-sm" placeholder="Seat scope, e.g. JEE Advanced: IIT seats only"></div>
                </form>
                <form id="unmap{{ $m->id }}" action="{{ route('admin.counselling.mappings.destroy', $m) }}" method="POST" class="d-none">@csrf @method('DELETE')<input type="hidden" name="confirm" value="1"></form>
            @empty
                <p class="text-muted small">No exam mapped yet. A counselling needs at least one to be published.</p>
            @endforelse
            @can('cns-counsellings-edit')
            @if($mappable->count())
            <form action="{{ route('admin.counselling.mappings.store', $counselling) }}" method="POST" class="row g-2 border-top pt-3 mt-1">
                @csrf
                <div class="col-md-5"><label class="form-label small fw-bold mb-1">Exam</label><select name="exam_id" class="form-select @error('exam_id') is-invalid @enderror" required><option value="">Map an exam...</option>@foreach($mappable as $e)<option value="{{ $e->id }}" @selected(old('exam_id') == $e->id)>{{ $e->name }}</option>@endforeach</select>
                    @error('exam_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label small fw-bold mb-1">Role</label><select name="role" class="form-select"><option value="primary" @selected(old('role') !== 'additional')>Primary</option><option value="additional" @selected(old('role') === 'additional')>Additional</option></select></div>
                <div class="col-md-2"><label class="form-label small fw-bold mb-1">Valid from</label><input type="number" name="valid_from_year" value="{{ old('valid_from_year') }}" class="form-control @error('valid_from_year') is-invalid @enderror" placeholder="Year" min="1990" max="2100">
                    @error('valid_from_year')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-2"><label class="form-label small fw-bold mb-1">Valid to</label><input type="number" name="valid_to_year" value="{{ old('valid_to_year') }}" class="form-control @error('valid_to_year') is-invalid @enderror" placeholder="Year" min="1990" max="2100">
                    @error('valid_to_year')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-9"><label class="form-label small fw-bold mb-1">Seat scope note</label><input type="text" name="seat_scope_note" value="{{ old('seat_scope_note') }}" class="form-control @error('seat_scope_note') is-invalid @enderror" maxlength="255" placeholder="e.g. JEE Advanced: IIT seats only; JEE Main: NIT, IIIT, GFTI">
                    @error('seat_scope_note')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3 d-flex align-items-end"><button class="btn btn-outline-primary w-100">Add exam</button></div>
                <div class="col-12 form-text mt-0">Leave the years empty when the exam has always been accepted (and still is).</div>
            </form>
            @endif
            @endcan
        </div></div>

        {{-- Cycles --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Cycles</h6>
                @can('cns-counsellings-edit')<a href="{{ route('admin.counselling.cycles.create', $counselling) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Add Cycle</a>@endcan
            </div>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead class="bg-light"><tr><th>Cycle</th><th>Status</th><th>Rounds</th><th>Current</th><th>Public</th></tr></thead>
                <tbody>
                @forelse($counselling->cycles as $cycle)
                    <tr>
                        <td><a href="{{ route('admin.counselling.cycles.show', $cycle) }}" class="fw-semibold text-decoration-none">{{ $cycle->title }}</a>
                            @if($counselling->cycles->filter(fn ($x) => mb_strtolower($x->title) === mb_strtolower($cycle->title))->count() > 1)
                                <div class="small text-danger">Duplicate year: #{{ $cycle->id }}, created {{ $cycle->created_at?->format('j M Y, H:i') }}. Keep one, delete the other.</div>
                            @endif</td>
                        <td><span class="badge {{ in_array($cycle->cycle_status, ['postponed', 'cancelled']) ? 'bg-danger-subtle text-danger' : 'bg-light text-dark border' }}">{{ ucfirst($cycle->cycle_status) }}</span></td>
                        <td>{{ $cycle->rounds_count }}</td>
                        <td>
                            @if($cycle->is_current)<span class="badge bg-primary">Current</span>
                            @else @can('cns-counsellings-edit')
                                <form action="{{ route('admin.counselling.cycles.current', $cycle) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-sm btn-link p-0">Make current</button></form>
                            @endcan @endif
                        </td>
                        <td>
                            @can('cns-counsellings-publish')
                            <form action="{{ route('admin.counselling.cycles.public', $cycle) }}" method="POST">@csrf @method('PATCH')
                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" onchange="this.form.submit()" @checked($cycle->is_public)></div></form>
                            @else {{ $cycle->is_public ? 'Yes' : 'No' }} @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No cycles yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div></div>

        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Recent changes</h6>
            @forelse($logs as $log)
                <div class="small border-bottom py-2"><strong>{{ $log->subject }}</strong> {{ $log->action }} by {{ $log->user->name ?? 'system' }}
                    <span class="text-muted">· {{ $log->created_at->format('d M Y, h:i A') }}</span></div>
            @empty
                <p class="text-muted small mb-0">No changes recorded.</p>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
