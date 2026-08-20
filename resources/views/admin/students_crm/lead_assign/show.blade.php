@extends('admin.layouts.master')

@section('title', 'Assigned Leads Details')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Leads assigned to: <span class="text-primary">{{ $staff->name }}</span></h1>
        <a href="{{ route('admin.students-crm.lead-assign.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Overview
        </a>
    </div>

    <div class="card shadow mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Lead List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Lead Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Assigned On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration + $assignments->firstItem() - 1 }}</td>
                                <td>{{ $assignment->customer->name ?? 'Unknown' }}</td>
                                <td>{{ $assignment->customer->phone ?? 'N/A' }}</td>
                                <td>{{ $assignment->customer->email ?? 'N/A' }}</td>
                                <td>{{ $assignment->created_at->format('d M, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No leads found for this staff member.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $assignments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
