@extends('admin.layouts.master')

@section('title', $exam->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">{{ $exam->name }}
            <span class="badge {{ $exam->visibility === 'public' ? 'bg-success' : 'bg-secondary' }} fs-6 align-middle">{{ ucfirst($exam->visibility) }}</span>
        </h4>
        <div class="text-muted">/{{ $exam->slug }} · {{ \App\Models\ExamModule\Exam::TYPES[$exam->exam_type] ?? 'Type not set' }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
        @can('exm-exams-publish')
        <form action="{{ route('admin.exam-module.exams.visibility', $exam) }}" method="POST">@csrf @method('PATCH')
            <button class="btn {{ $exam->visibility === 'public' ? 'btn-outline-secondary' : 'btn-success' }}">{{ $exam->visibility === 'public' ? 'Move to Draft' : 'Publish' }}</button>
        </form>
        @endcan
        @can('exm-exams-edit')<a href="{{ route('admin.exam-module.exams.edit', $exam) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Edit</a>@endcan
    </div>
</div>

@if($exam->visibility === 'draft' && $missing)
    <div class="alert alert-warning">To publish, still missing: <strong>{{ implode(', ', $missing) }}</strong></div>
@endif

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Details</h6>
            <dl class="row mb-0 small">
                <dt class="col-5">Source</dt><dd class="col-7">{{ ucfirst($exam->source_type) }}</dd>
                <dt class="col-5">{{ $exam->source_type === 'internal' ? 'Owner' : 'Conducted by' }}</dt>
                <dd class="col-7">{{ $exam->source_type === 'internal' ? ($exam->owningOrganisation->name ?? '-') : ($exam->conductingBody->name ?? '-') }}</dd>
                <dt class="col-5">Frequency</dt><dd class="col-7">{{ \App\Models\ExamModule\Exam::FREQUENCIES[$exam->frequency] ?? '-' }}</dd>
                <dt class="col-5">Categories</dt><dd class="col-7">{{ collect($exam->exam_categories)->map(fn ($i) => $categories[$i] ?? null)->filter()->implode(', ') ?: '-' }}</dd>
                <dt class="col-5">Levels</dt><dd class="col-7">{{ collect($exam->program_levels)->map(fn ($i) => $levels[$i] ?? null)->filter()->implode(', ') ?: '-' }}</dd>
                @if($exam->states)<dt class="col-5">States</dt><dd class="col-7">{{ implode(', ', $exam->states) }}</dd>@endif
                <dt class="col-5">Feeder exam</dt><dd class="col-7">{{ $exam->feederExam->name ?? '-' }}</dd>
                <dt class="col-5">Languages</dt><dd class="col-7">{{ implode(', ', $exam->languages ?? []) ?: '-' }}</dd>
                <dt class="col-5">Aliases</dt><dd class="col-7">{{ implode(', ', $exam->aliases ?? []) ?: '-' }}</dd>
                <dt class="col-5">Website</dt><dd class="col-7">@if($exam->official_website)<a href="{{ $exam->official_website }}" target="_blank" rel="noopener">Open</a>@else - @endif</dd>
            </dl>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Cycles</h6>
                @can('exm-cycles-manage')<a href="{{ route('admin.exam-module.cycles.create', $exam) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Add Cycle</a>@endcan
            </div>
            <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light"><tr><th>Cycle</th><th>Status</th><th>Stages / Events</th><th>Current</th><th>Public</th></tr></thead>
                <tbody>
                @forelse($exam->cycles as $cycle)
                    <tr>
                        <td><a href="{{ route('admin.exam-module.cycles.show', $cycle) }}" class="fw-semibold text-decoration-none">{{ $cycle->title }}</a></td>
                        <td><span class="badge {{ in_array($cycle->cycle_status, ['postponed', 'cancelled']) ? 'bg-danger-subtle text-danger' : 'bg-light text-dark border' }}">{{ ucfirst($cycle->cycle_status) }}</span></td>
                        <td>{{ $cycle->stages_count }} / {{ $cycle->events_count }}</td>
                        <td>
                            @if($cycle->is_current)<span class="badge bg-primary">Current</span>
                            @else
                                @can('exm-cycles-manage')
                                <form action="{{ route('admin.exam-module.cycles.current', $cycle) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-sm btn-link p-0">Make current</button></form>
                                @endcan
                            @endif
                        </td>
                        <td>
                            @can('exm-exams-publish')
                            <form action="{{ route('admin.exam-module.cycles.public', $cycle) }}" method="POST">@csrf @method('PATCH')
                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" onchange="this.form.submit()" @checked($cycle->is_public)></div>
                            </form>
                            @else {{ $cycle->is_public ? 'Yes' : 'No' }} @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No cycles yet. Add the first one to enter dates.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div></div>

        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Recent changes</h6>
            @forelse($logs as $log)
                <div class="small border-bottom py-2">
                    <strong>{{ $log->subject }}</strong> {{ $log->action }} by {{ $log->user->name ?? 'system' }}
                    <span class="text-muted">· {{ $log->created_at->format('d M Y, h:i A') }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0">No changes recorded.</p>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
