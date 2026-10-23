@extends('admin.layouts.master')

@section('title', 'My Work & Tasks')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-user-check text-primary me-2"></i> My Work Dashboard</h4>
            <p class="text-muted small mb-0">Personal tasks, assigned milestones, active custody subtasks & deadlines</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.work_management.tasks.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-plus me-1"></i> Create Task
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 bg-white p-3">
                <span class="text-muted small fw-semibold">Due Today</span>
                <h3 class="fw-bold text-primary mb-0">{{ $todayTasks->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 bg-white p-3">
                <span class="text-muted small fw-semibold">Overdue</span>
                <h3 class="fw-bold text-danger mb-0">{{ $overdueTasks->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 bg-white p-3">
                <span class="text-muted small fw-semibold">Upcoming</span>
                <h3 class="fw-bold text-info mb-0">{{ $upcomingTasks->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 bg-white p-3">
                <span class="text-muted small fw-semibold">Completed</span>
                <h3 class="fw-bold text-success mb-0">{{ $completedTasks->count() }}</h3>
            </div>
        </div>
    </div>

    <!-- Tasks Accordion / List -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-pills card-header-pills" id="myWorkTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4" id="all-tab" data-bs-toggle="tab" data-bs-target="#tab-all" type="button">
                        All Assigned ({{ $tasks->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 text-danger" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#tab-overdue" type="button">
                        Overdue ({{ $overdueTasks->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 text-primary" id="today-tab" data-bs-toggle="tab" data-bs-target="#tab-today" type="button">
                        Today ({{ $todayTasks->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="myWorkTabContent">
                <div class="tab-pane fade show active p-3" id="tab-all">
                    @include('admin.work_management.dashboard.partials.my_tasks_table', ['taskList' => $tasks])
                </div>
                <div class="tab-pane fade p-3" id="tab-overdue">
                    @include('admin.work_management.dashboard.partials.my_tasks_table', ['taskList' => $overdueTasks])
                </div>
                <div class="tab-pane fade p-3" id="tab-today">
                    @include('admin.work_management.dashboard.partials.my_tasks_table', ['taskList' => $todayTasks])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
