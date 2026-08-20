@extends('admin.layouts.master')

@section('title', 'Lead Assignment')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Assign Leads to Staff</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <form action="{{ route('admin.students-crm.lead-assign.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category_id" class="form-select rounded-3">
                            <option value="">All Categories</option>
                            @php
                                function renderCategoryOptions($categories, $level = 0) {
                                    foreach ($categories as $cat) {
                                        echo '<option value="'.$cat->id.'">';
                                        echo str_repeat("— ", $level).$cat->name;
                                        echo '</option>';
                                        if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                            renderCategoryOptions($cat->childrenRecursive, $level + 1);
                                        }
                                    }
                                }
                            @endphp
                            @php renderCategoryOptions($categories); @endphp
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Call Status (Optional)</label>
                        <select name="call_status_id" class="form-select rounded-3">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Start Number</label>
                        <input type="number" name="start_number" class="form-control rounded-3" min="1" required placeholder="e.g. 10">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">End Number</label>
                        <input type="number" name="end_number" class="form-control rounded-3" min="1" required placeholder="e.g. 50">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Assign to Staff</label>
                        <select name="staff_id" class="form-select rounded-3" required>
                            <option value="">Select Staff</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">Assign Leads</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Assigned Leads Summary Table -->
    <div class="card shadow mb-4 border-0 rounded-4 mt-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Assigned Leads Overview</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Staff Name</th>
                            <th>Total Leads Assigned</th>
                            <th>Assignment Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignmentsSummary as $summary)
                            <tr>
                                <td>{{ $summary->staff->name ?? 'Unknown Staff' }}</td>
                                <td><span class="badge bg-info text-dark">{{ $summary->total_leads }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($summary->batch_date)->format('d M, Y h:i A') }}</td>
                                <td>
                                    <a href="{{ route('admin.students-crm.lead-assign.show', ['staff' => $summary->staff_id, 'batch' => $summary->batch_date]) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No leads have been assigned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
