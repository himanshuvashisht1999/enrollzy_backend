@extends('admin.layouts.master')

@section('title', 'Exam Audit Log')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Audit Log</h4>
        <p class="text-muted mb-0">Who changed what, and when.</p>
    </div>
    <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Exams</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="type" class="form-select custom-select2">
            <option value="">All records</option>
            @foreach(['Exam', 'Cycle', 'Stage', 'CycleEvent', 'ConductingBody'] as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="action" class="form-select custom-select2">
            <option value="">All actions</option>
            @foreach(['created', 'updated', 'deleted'] as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ ucfirst($a) }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-light border w-100">Filter</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">When</th><th>Who</th><th>Record</th><th>Action</th><th>Old</th><th>New</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr class="small">
                <td class="ps-4 text-nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                <td>{{ $log->user->name ?? 'system' }}</td>
                <td>{{ $log->subject }}</td>
                <td><span class="badge bg-light text-dark border">{{ $log->action }}</span></td>
                <td style="max-width:260px"><code class="text-wrap">{{ $log->action === 'updated' ? json_encode($log->old_values) : '' }}</code></td>
                <td style="max-width:260px"><code class="text-wrap">{{ $log->action === 'updated' ? json_encode($log->new_values) : '' }}</code></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No entries.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
