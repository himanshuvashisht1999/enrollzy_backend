@extends('admin.layouts.master')

@section('title', 'Exam Module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Exams</h4>
        <p class="text-muted mb-0">Permanent exam identities. Dates and details live in yearly cycles.</p>
    </div>
    <div class="d-flex gap-2">
        @can('exm-attention-browse')<a href="{{ route('admin.exam-module.attention') }}" class="btn btn-outline-warning"><i class="fas fa-bell me-1"></i> Needs Attention</a>@endcan
        @can('exm-conducting-bodies-browse')<a href="{{ route('admin.exam-module.conducting-bodies.index') }}" class="btn btn-light border">Conducting Bodies</a>@endcan
        @can('exm-event-types-manage')<a href="{{ route('admin.exam-module.event-types.index') }}" class="btn btn-light border">Event Types</a>@endcan
        @can('exm-audit-browse')<a href="{{ route('admin.exam-module.audit') }}" class="btn btn-light border">Audit Log</a>@endcan
        @can('exm-exams-add')<a href="{{ route('admin.exam-module.exams.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Exam</a>@endcan
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, short name, alias, slug"></div>
    <div class="col-md-3">
        <select name="exam_type" class="form-select custom-select2">
            <option value="">All types</option>
            @foreach(\App\Models\ExamModule\Exam::TYPES as $k => $v)<option value="{{ $k }}" @selected(request('exam_type') === $k)>{{ $v }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="visibility" class="form-select custom-select2">
            <option value="">Draft + Public</option>
            <option value="draft" @selected(request('visibility') === 'draft')>Draft</option>
            <option value="public" @selected(request('visibility') === 'public')>Public</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-light border w-100">Filter</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr><th class="ps-4">Exam</th><th>Type</th><th>Conducted by</th><th>Current cycle</th><th>Visibility</th><th class="text-end pe-4">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($exams as $exam)
                <tr>
                    <td class="ps-4">
                        <a href="{{ route('admin.exam-module.exams.show', $exam) }}" class="fw-semibold text-decoration-none">{{ $exam->name }}</a>
                        <div class="small text-muted">/{{ $exam->slug }}</div>
                    </td>
                    <td>{{ \App\Models\ExamModule\Exam::TYPES[$exam->exam_type] ?? '-' }}</td>
                    <td>{{ $exam->conductingBody->name ?? ($exam->source_type === 'internal' ? 'Owning organisation' : '-') }}</td>
                    <td>{{ $exam->currentCycle?->title ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $exam->visibility === 'public' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($exam->visibility) }}</span>
                        @unless($exam->status)<span class="badge bg-danger-subtle text-danger">Inactive</span>@endunless
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('admin.exam-module.exams.show', $exam) }}" class="btn btn-sm btn-light border"><i class="fas fa-eye text-info"></i></a>
                        @can('exm-exams-edit')<a href="{{ route('admin.exam-module.exams.edit', $exam) }}" class="btn btn-sm btn-light border"><i class="fas fa-edit text-primary"></i></a>@endcan
                        @can('exm-exams-delete')
                        <form action="{{ route('admin.exam-module.exams.destroy', $exam) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No exams yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $exams->links() }}</div>
@endsection
