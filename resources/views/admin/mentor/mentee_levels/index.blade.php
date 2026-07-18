@extends('admin.layouts.master')

@section('title', 'Mentor Mentee Levels')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Mentor Mentee Levels</h4>
        <a href="{{ route('admin.mentor.mentee_levels.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Mentee Level
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">ID</th>
                            <th>Name</th>
                            <th width="100">Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menteeLevels as $menteeLevel)
                            <tr>
                                <td>{{ $menteeLevel->id }}</td>
                                <td>{{ $menteeLevel->name }}</td>
                                <td>
                                    @if($menteeLevel->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.mentor.mentee_levels.edit', $menteeLevel->id) }}" class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.mentor.mentee_levels.destroy', $menteeLevel->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Mentee Level?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No Mentor Mentee Levels found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection



