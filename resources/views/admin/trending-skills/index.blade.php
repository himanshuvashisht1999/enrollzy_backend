@extends('admin.layouts.master')

@section('title', 'Manage Trending Skills')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Trending Skills (Homepage Section)</h4>
    <a href="{{ route('admin.trending-skills.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Skill
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Skill Name</th>
                        <th>URL</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($skills as $skill)
                    <tr>
                        <td class="ps-4 text-muted">{{ $skill->id }}</td>
                        <td><span class="fw-bold">{{ $skill->name }}</span></td>
                        <td><small class="text-muted">{{ $skill->url ?? 'N/A' }}</small></td>
                        <td>{{ $skill->sort_order }}</td>
                        <td>
                            <span class="badge bg-{{ $skill->status ? 'success' : 'danger' }}">
                                {{ $skill->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.trending-skills.edit', $skill->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.trending-skills.destroy', $skill->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No trending skills found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
