@extends('admin.layouts.master')

@section('title', 'Conducting Bodies')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Conducting Bodies</h4>
        <p class="text-muted mb-0">NTA, UPSC, CBSE... one body can run many exams.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border">Exams</a>
        @can('exm-conducting-bodies-add')<a href="{{ route('admin.exam-module.conducting-bodies.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add New</a>@endcan
    </div>
</div>
<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Name</th><th>Short</th><th>Type</th><th>Website</th><th>Exams</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($bodies as $b)
            <tr>
                <td class="ps-4 fw-medium">{{ $b->name }}</td>
                <td>{{ $b->short_name ?: '-' }}</td>
                <td>{{ \App\Models\ExamModule\ConductingBody::TYPES[$b->body_type] ?? '-' }}</td>
                <td>@if($b->website)<a href="{{ $b->website }}" target="_blank" rel="noopener">Open</a>@else - @endif</td>
                <td>{{ $b->exams_count }}</td>
                <td><span class="badge {{ $b->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $b->status ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-end pe-4">
                    @can('exm-conducting-bodies-edit')<a href="{{ route('admin.exam-module.conducting-bodies.edit', $b) }}" class="btn btn-sm btn-light border"><i class="fas fa-edit text-primary"></i></a>@endcan
                    @can('exm-conducting-bodies-delete')
                    <form action="{{ route('admin.exam-module.conducting-bodies.destroy', $b) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-5 text-muted">No conducting bodies yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
