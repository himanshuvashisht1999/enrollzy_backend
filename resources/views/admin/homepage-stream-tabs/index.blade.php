@extends('admin.layouts.master')

@section('title', 'Manage Stream Tabs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Homepage Stream Tabs (Leading Universities)</h4>
        <p class="text-muted small mb-0">Manage streams, keywords, default exams, top states, and related courses displayed on the homepage.</p>
    </div>
    <a href="{{ route('admin.homepage-stream-tabs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Stream Tab
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Order</th>
                        <th>Stream Name</th>
                        <th>Key / Identifier</th>
                        <th>Keywords</th>
                        <th>Exams Count</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tabs as $tab)
                    <tr>
                        <td class="ps-4 text-muted fw-bold">{{ $tab->sort_order }}</td>
                        <td>
                            <span class="fw-bold text-dark fs-6">{{ $tab->name }}</span>
                        </td>
                        <td><code>#tab-{{ $tab->key }}</code></td>
                        <td>
                            <small class="text-muted">
                                {{ is_array($tab->keywords) ? implode(', ', array_slice($tab->keywords, 0, 4)) : $tab->keywords }}
                                @if(is_array($tab->keywords) && count($tab->keywords) > 4)
                                    <span class="badge bg-light text-dark">+{{ count($tab->keywords) - 4 }} more</span>
                                @endif
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ is_array($tab->default_exams) ? count($tab->default_exams) : 0 }} Exams
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $tab->status ? 'success' : 'danger' }}">
                                {{ $tab->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.homepage-stream-tabs.edit', $tab->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Stream Tab">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.homepage-stream-tabs.destroy', $tab->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this stream tab?')" title="Delete Stream Tab">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No stream tabs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
