@extends('admin.layouts.master')

@section('title', 'Calling Dashboard')

@push('css')
<style>
    .metric-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
    }
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; }
    .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); color: white; }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); color: white; }
    .bg-gradient-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); color: white; }
    .icon-bg {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 3rem;
        opacity: 0.2;
    }
    .filter-card {
        border-radius: 12px;
        border: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-gray-800 fw-bold">Calling Module Dashboard</h4>
        <div>
            <a href="{{ route('admin.students-crm.calling-history.index') }}" class="btn btn-outline-secondary rounded-pill px-4 me-2">
                <i class="fas fa-history me-2"></i> Calling History
            </a>
            <a href="{{ route('admin.students-crm.calling-module.index') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-phone-alt me-2"></i> Go to Calling Module
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm filter-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.students-crm.calling-dashboard.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Assigned From Date</label>
                    <input type="date" name="assign_from" class="form-control" value="{{ $assign_from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Assigned To Date</label>
                    <input type="date" name="assign_to" class="form-control" value="{{ $assign_to }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Work Done From Date</label>
                    <input type="date" name="work_from" class="form-control" value="{{ $work_from }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Work Done To Date</label>
                    <input type="date" name="work_to" class="form-control" value="{{ $work_to }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Top Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card metric-card shadow-sm bg-gradient-primary h-100 py-2">
                <div class="card-body position-relative">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1 opacity-75">Total Assigned Leads</div>
                            <div class="h2 mb-0 fw-bold">{{ $totalAssigned }}</div>
                        </div>
                    </div>
                    <i class="fas fa-users icon-bg"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card metric-card shadow-sm bg-gradient-success h-100 py-2">
                <div class="card-body position-relative">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1 opacity-75">Work Done (Updated)</div>
                            <div class="h2 mb-0 fw-bold">{{ $doneCount }}</div>
                        </div>
                    </div>
                    <i class="fas fa-check-circle icon-bg"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card metric-card shadow-sm bg-gradient-warning h-100 py-2">
                <div class="card-body position-relative">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1 opacity-75">Pending Leads</div>
                            <div class="h2 mb-0 fw-bold">{{ $pendingCount }}</div>
                        </div>
                    </div>
                    <i class="fas fa-clock icon-bg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <h5 class="fw-bold text-gray-800 mb-3 mt-5">Lead Status Breakdown</h5>
    <div class="row g-4">
        @foreach($allStatuses as $status)
            @php 
                $count = $statusCounts[$status->id] ?? 0;
            @endphp
            <div class="col-xl-3 col-md-4 col-sm-6">
                <a href="{{ route('admin.students-crm.calling-history.index', ['call_status_id' => $status->id]) }}" class="text-decoration-none">
                    <div class="card metric-card shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark">{{ $status->name }}</span>
                                <span class="badge bg-light text-dark border">{{ $count }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $percent = $doneCount > 0 ? ($count / $doneCount) * 100 : 0; @endphp
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

</div>
@endsection
