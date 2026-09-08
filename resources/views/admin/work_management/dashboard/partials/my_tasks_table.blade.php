<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light small">
            <tr>
                <th>Task / Subtask</th>
                <th>Project</th>
                <th>Priority</th>
                <th>Due Date</th>
                <th>Status</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($taskList as $t)
            <tr>
                <td>
                    <div class="d-flex flex-column">
                        <a href="{{ route('admin.work_management.tasks.show', encrypt($t->id)) }}" class="fw-bold text-dark text-decoration-none">
                            @if($t->isSubtask()) <span class="text-muted me-1">↳</span> @endif
                            {{ $t->title }}
                        </a>
                        @if($t->task_code)
                            <small class="text-muted">{{ $t->task_code }}</small>
                        @endif
                    </div>
                </td>
                <td>
                    <small class="fw-semibold">{{ $t->project->title ?? 'No Project' }}</small>
                    @if($t->milestone_assigned)
                        <small class="text-muted d-block">&bull; {{ $t->milestone_assigned->title }}</small>
                    @endif
                </td>
                <td>{!! $t->getPriorityBadge() !!}</td>
                <td>
                    @if($t->due_date)
                        @php $isOverdue = $t->due_date < date('Y-m-d') && !in_array($t->status, ['completed', 'verified', 'closed']); @endphp
                        <small class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ date('M d, Y', strtotime($t->due_date)) }}
                        </small>
                    @else
                        <small class="text-muted">-</small>
                    @endif
                </td>
                <td>{!! $t->getStatusBadge() !!}</td>
                <td class="text-end">
                    <a href="{{ route('admin.work_management.tasks.show', encrypt($t->id)) }}" class="btn btn-sm btn-soft-primary rounded-pill px-3">
                        <i class="fas fa-arrow-right me-1"></i> Open
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-muted small">No tasks in this category.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
