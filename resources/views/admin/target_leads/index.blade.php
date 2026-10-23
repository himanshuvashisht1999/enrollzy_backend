@extends('admin.layouts.master')

@section('title', 'Assign Admission Target')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0">Assign Admission Target</h3>
    <a href="{{ route('admin.target-leads.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i> Assign New Target
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Staff Name</th>
                    <th>Year</th>
                    <th>Month</th>
                    <th>Calling Target</th>
                    <th>Admissions Target</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($targets as $target)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-bold">{{ $target->staff->name ?? 'N/A' }}</td>
                    <td>{{ $target->year }}</td>
                    <td>{{ $target->month }}</td>
                    <td>{{ $target->month_target_calling }}</td>
                    <td>{{ $target->month_target_admissions }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.target-leads.edit', $target->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.target-leads.destroy', $target->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this target?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No targets assigned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
