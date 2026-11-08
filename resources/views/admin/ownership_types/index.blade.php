@extends('admin.layouts.master')

@section('title', 'Ownership Types')

{{-- Masters → Ownership Types (OwnershipTypeController): the "Ownership Type" choices on the Institute form --}}
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Ownership Types</h4>
        <p class="text-muted mb-0">The choices for "Ownership Type" on the Institute form. Inactive ones are hidden from the form; renaming one also renames it on the organisations that use it.</p>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.ownership-types.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-6">
                <label class="form-label">New Ownership Type</label>
                <input type="text" name="title" class="form-control" maxlength="100" required placeholder="e.g. Private Limited Company">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" min="0" value="{{ ($types->max('sort_order') ?? 0) + 1 }}">
            </div>
            <div class="col-md-2">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="status" value="1" id="ot-new-status" checked>
                    <label class="form-check-label" for="ot-new-status">Active</label>
                </div>
            </div>
            <div class="col-md-2 text-end"><button class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Add</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr><th class="ps-4">Title</th><th style="width:120px">Sort Order</th><th style="width:110px">Active</th><th style="width:140px">Used by</th><th class="text-end pe-4" style="width:180px">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($types as $t)
                        <tr>
                            <form method="POST" action="{{ route('admin.ownership-types.update', $t) }}" id="ot-{{ $t->id }}">@csrf @method('PUT')</form>
                            <td class="ps-4"><input type="text" name="title" form="ot-{{ $t->id }}" class="form-control form-control-sm" value="{{ $t->title }}" maxlength="100" required></td>
                            <td><input type="number" name="sort_order" form="ot-{{ $t->id }}" class="form-control form-control-sm" value="{{ $t->sort_order }}" min="0"></td>
                            <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" value="1" form="ot-{{ $t->id }}" {{ $t->status ? 'checked' : '' }}></div></td>
                            <td class="text-muted small">{{ ($n = $used[$t->title] ?? 0) ? $n . ' ' . Str::plural('organisation', $n) : '—' }}</td>
                            <td class="text-end pe-4">
                                <button type="submit" form="ot-{{ $t->id }}" class="btn btn-sm btn-outline-primary">Save</button>
                                <form method="POST" action="{{ route('admin.ownership-types.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete “{{ addslashes($t->title) }}”?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" {{ ($used[$t->title] ?? 0) ? 'disabled title=In use: turn it off instead' : '' }}>Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No ownership types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
