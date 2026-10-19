@extends('admin.layouts.master')

@section('title', 'Event Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Event Types</h4>
        <p class="text-muted mb-0">A new kind of date needs a new type here, never a new table. <strong>Dates</strong> types calculate status; <strong>Manual</strong> types (results) are set by an admin.</p>
    </div>
    <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border">Exams</a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <form action="{{ route('admin.exam-module.event-types.store') }}" method="POST" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5"><label class="form-label fw-bold">New type</label><input type="text" name="name" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label fw-bold">Status source</label>
            <select name="status_source" class="form-select"><option value="dates">Dates (calculated)</option><option value="manual">Manual</option></select></div>
        <div class="col-md-3"><label class="form-label fw-bold">Used by</label>
            <select name="applies_to" class="form-select"><option value="exam">Exams</option><option value="counselling">Counselling</option><option value="both">Both</option></select></div>
        <div class="col-12"><button class="btn btn-primary">Add</button></div>
    </form>
</div></div>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Name</th><th>Order</th><th>Source</th><th>Used by</th><th>Used</th><th>Active</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @foreach($types as $t)
            <tr>
                <form action="{{ route('admin.exam-module.event-types.update', $t) }}" method="POST" id="et{{ $t->id }}">@csrf @method('PUT')</form>
                <td class="ps-4"><input form="et{{ $t->id }}" type="text" name="name" value="{{ $t->name }}" class="form-control form-control-sm"></td>
                <td style="width:90px"><input form="et{{ $t->id }}" type="number" name="sort_order" value="{{ $t->sort_order }}" class="form-control form-control-sm"></td>
                <td><span class="badge {{ $t->status_source === 'manual' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info' }}">{{ ucfirst($t->status_source) }}</span></td>
                <td><span class="badge bg-light text-dark border">{{ ucfirst($t->applies_to) }}</span></td>
                <td>{{ $t->events_count }}</td>
                <td><div class="form-check form-switch"><input form="et{{ $t->id }}" class="form-check-input" type="checkbox" name="status" value="1" @checked($t->status)></div></td>
                <td class="text-end pe-4">
                    <button form="et{{ $t->id }}" class="btn btn-sm btn-light border"><i class="fas fa-save text-primary"></i></button>
                    <button form="dt{{ $t->id }}" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash-alt"></i></button>
                    <form id="dt{{ $t->id }}" action="{{ route('admin.exam-module.event-types.destroy', $t) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
