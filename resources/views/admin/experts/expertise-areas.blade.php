@extends('admin.layouts.master')

@section('title', 'Expertise Areas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Expertise Areas</h4>
        <p class="text-muted mb-0">What experts pick on their profile and application, and what the website's experts page filters by. Renaming an area renames it on every expert. An area in use can be hidden (untick Active), not deleted.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expert-applications.index') }}" class="btn btn-light border">Applications</a>
        <a href="{{ route('experts.index') }}" class="btn btn-light border">Experts</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <form action="{{ route('expertise-areas.store') }}" method="POST" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-6"><label class="form-label fw-bold">New expertise area</label><input type="text" name="name" class="form-control" maxlength="100" placeholder="e.g. MBBS Abroad, Defence Careers" required></div>
        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Add</button></div>
    </form>
</div></div>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Name</th><th style="width:110px">Order</th><th style="width:120px">Used by</th><th style="width:90px">Active</th><th class="text-end pe-4" style="width:140px">Actions</th></tr></thead>
        <tbody>
        @forelse($areas as $a)
            <tr>
                <form action="{{ route('expertise-areas.update', $a) }}" method="POST" id="ea{{ $a->id }}">@csrf @method('PUT')</form>
                <td class="ps-4"><input form="ea{{ $a->id }}" type="text" name="name" value="{{ $a->name }}" maxlength="100" class="form-control form-control-sm" required></td>
                <td><input form="ea{{ $a->id }}" type="number" name="sort_order" value="{{ $a->sort_order }}" min="0" class="form-control form-control-sm"></td>
                <td>{{ $a->used }} {{ Str::plural('expert', $a->used) }}</td>
                <td><div class="form-check form-switch"><input form="ea{{ $a->id }}" class="form-check-input" type="checkbox" name="status" value="1" @checked($a->status)></div></td>
                <td class="text-end pe-4 text-nowrap">
                    <button form="ea{{ $a->id }}" class="btn btn-sm btn-light border" title="Save"><i class="fas fa-save text-primary"></i></button>
                    @if(!$a->used)
                    <form action="{{ route('expertise-areas.destroy', $a) }}" method="POST" class="d-inline" data-confirm="Delete this expertise area?">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-light border text-danger" title="Delete"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No expertise areas yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
