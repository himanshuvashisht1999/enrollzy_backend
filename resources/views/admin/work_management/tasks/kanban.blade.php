@extends('admin.layouts.master')

@section('title', 'Work Management - Kanban Board')

@push('css')
<style>
    .kanban-board {
        display: flex;
        overflow-x: auto;
        gap: 1.25rem;
        padding-bottom: 1.5rem;
        min-height: calc(100vh - 220px);
    }
    .kanban-column {
        flex: 0 0 320px;
        background: #f8fafc;
        border-radius: 1rem;
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 220px);
        border: 1px solid #e2e8f0;
    }
    .kanban-column-header {
        padding: 1rem 1.25rem;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #edf2f7;
    }
    .kanban-cards {
        padding: 0.75rem;
        overflow-y: auto;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .kanban-card {
        background: #ffffff;
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #edf2f7;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        cursor: grab;
    }
    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="fas fa-columns text-primary me-2"></i> Work Board (Kanban)</h4>
            <small class="text-muted">Visual workflow tracking across all project development phases</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form method="GET" action="{{ route('admin.work_management.tasks.kanban') }}" class="d-flex align-items-center gap-2">
                <select name="project_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.work_management.tasks.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm">
                <i class="fas fa-list me-1"></i> List View
            </a>
            <a href="{{ route('admin.work_management.tasks.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                <i class="fas fa-plus me-1"></i> New Task
            </a>
        </div>
    </div>

    <!-- Kanban Columns -->
    <div class="kanban-board">
        @foreach($columns as $colKey => $column)
            <div class="kanban-column" data-status="{{ $colKey }}">
                <div class="kanban-column-header">
                    <span class="text-dark">{{ $column['title'] }}</span>
                    <span class="badge {{ $column['badge'] }} rounded-pill px-2">{{ $column['tasks']->count() }}</span>
                </div>
                <div class="kanban-cards" id="column-{{ $colKey }}">
                    @foreach($column['tasks'] as $task)
                        <div class="kanban-card" data-id="{{ $task->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-secondary border small">{{ $task->task_code }}</span>
                                {!! $task->getPriorityBadge() !!}
                            </div>
                            <h6 class="fw-bold text-dark mb-2">
                                <a href="{{ route('admin.work_management.tasks.show', encrypt($task->id)) }}" class="text-dark text-decoration-none">
                                    {{ $task->title }}
                                </a>
                            </h6>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-folder me-1 text-secondary"></i> {{ $task->project->title ?? 'General' }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-light text-dark border small">
                                        <i class="fas fa-user text-primary me-1"></i>
                                        {{ $task->activePrimaryAssignee->display_name ?? 'Unassigned' }}
                                    </span>
                                </div>
                                <div class="small text-muted">
                                    @if($task->due_date)
                                        <span class="{{ ($task->due_date < date('Y-m-d') && !in_array($task->status, ['completed', 'verified', 'closed'])) ? 'text-danger fw-bold' : '' }}">
                                            <i class="fas fa-calendar-alt me-1"></i> {{ date('M d', strtotime($task->due_date)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
