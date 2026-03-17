@extends('admin.layouts.master')

@section('title', 'Manage Organisation Sub-Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Organisation Sub-Types</h4>
        <p class="text-muted mb-0">Manage sub-categories for your organisation types.</p>
    </div>
    <div>
        @if(request('organisation_type_id'))
            <a href="{{ route('admin.organisation-types.index') }}" class="btn btn-light border me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Types
            </a>
        @endif
        <a href="{{ route('admin.organisation-sub-types.create', ['organisation_type_id' => request('organisation_type_id')]) }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <form action="{{ route('admin.organisation-sub-types.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-medium">Filter by Organisation Type</label>
                <select name="organisation_type_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach($organisationTypes as $type)
                        <option value="{{ $type->id }}" {{ request('organisation_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('organisation_type_id'))
                <div class="col-md-2">
                    <a href="{{ route('admin.organisation-sub-types.index') }}" class="btn btn-light border w-100">Clear</a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Sub-Type Name</th>
                        <th>Parent Type</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="ps-4">{{ $loop->iteration }}</td>
                            <td class="fw-medium">{{ $item->title }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $item->organisationType->title }}
                                </span>
                            </td>
                            <td>
                                @if($item->status)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $item->sort_order }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.organisation-sub-types.edit', $item->id) }}" class="btn btn-sm btn-light border me-1">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                                <form action="{{ route('admin.organisation-sub-types.destroy', $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                <p>No sub-types found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
