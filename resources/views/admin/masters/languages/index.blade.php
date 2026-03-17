@extends('admin.layouts.master')

@section('title', 'Manage Languages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Languages</h4>
        <p class="text-muted mb-0">Manage course languages (e.g., Hindi, English).</p>
    </div>
    <a href="{{ route('admin.languages.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Language
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Sort Order</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($languages as $language)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border">{{ $language->sort_order }}</span>
                            </td>
                            <td><span class="fw-bold">{{ $language->title }}</span></td>
                            <td>
                                @if($language->status)
                                    <span class="badge bg-success-soft text-success px-3">Active</span>
                                @else
                                    <span class="badge bg-danger-soft text-danger px-3">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.languages.edit', $language->id) }}" class="btn btn-sm btn-light border me-1">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                                <form action="{{ route('admin.languages.destroy', $language->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this language?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No languages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($languages->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $languages->links() }}
        </div>
    @endif
</div>

<style>
    .bg-success-soft { background-color: #e8f5e9; }
    .bg-danger-soft { background-color: #fbe9e7; }
</style>
@endsection
