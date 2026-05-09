@extends('admin.layouts.master')

@section('title', 'Task Details')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Task Info Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-primary mb-0">Task Overview</h6>
                        {!! GetStatusBadge($task->status) !!}
                    </div>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <label class="small text-muted d-block">Project</label>
                            <span class="fw-bold">{{ $project->title }}</span>
                        </li>
                        <li class="mb-3">
                            <label class="small text-muted d-block">Milestone</label>
                            <span class="fw-bold text-info">{{ $task->milestone_assigned->title ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-3">
                            <label class="small text-muted d-block">Priority</label>
                            <span class="badge bg-soft-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }} text-capitalize px-3">
                                {{ $task->priority }}
                            </span>
                        </li>
                        <li class="mb-3">
                            <label class="small text-muted d-block">Assigned To</label>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @php $assignedIds = explode(',', $task->assigned_to); @endphp
                                @foreach($staff as $s)
                                    @if(in_array($s->id, $assignedIds))
                                        <span class="badge bg-light text-dark border rounded-pill px-3">{{ $s->name }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </li>
                        <li class="mb-3">
                            <label class="small text-muted d-block">Timeline</label>
                            <div class="small fw-bold">
                                <i class="far fa-calendar-alt me-1 text-primary"></i> {{ date('M d, Y', strtotime($task->start_date)) }} 
                                @if($task->due_date)
                                    <span class="mx-1 text-muted">→</span> 
                                    <i class="far fa-calendar-check me-1 text-danger"></i> {{ date('M d, Y', strtotime($task->due_date)) }}
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold">Task Files</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @php
                            $taskDocs = $task->documents ? explode(',', $task->documents) : [];
                        @endphp
                        @forelse($taskDocs as $doc)
                            <a href="{{ asset($doc) }}" target="_blank" class="btn btn-soft-secondary btn-sm text-start rounded-3 d-flex align-items-center">
                                <i class="fas fa-file-alt me-2"></i>
                                <span class="text-truncate">{{ basename($doc) }}</span>
                            </a>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="fas fa-folder-open d-block mb-2 fs-4"></i>
                                No documents attached
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Content & Comments -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="fw-bold mb-0 text-dark">{{ $task->title }}</h4>
                        <a href="{{ route('admin.hr.tasks.edit', encrypt($task->id)) }}" class="btn btn-soft-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-edit me-1"></i> Edit Task
                        </a>
                    </div>
                    <div class="text-muted mb-4 border-start border-4 border-primary ps-3 bg-light py-3 rounded-3">
                        {{ $task->description ?? 'No description provided for this task.' }}
                    </div>

                    <hr class="my-5">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="far fa-comments me-2 text-primary"></i> Discussion</h5>
                        <button class="btn btn-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#commentModal">
                            <i class="fas fa-reply me-1"></i> Post Comment
                        </button>
                    </div>

                    <div class="comment-timeline ps-4 border-start">
                        @forelse($task->comments as $comment)
                            <div class="comment-item position-relative mb-5">
                                <div class="comment-dot position-absolute bg-primary rounded-circle" style="width:12px; height:12px; left:-22px; top:6px;"></div>
                                <div class="card bg-light border-0 rounded-4">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold small mb-0">{{ $comment->user->name ?? 'System User' }}</h6>
                                            <span class="text-muted small">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="small mb-0 text-dark">{{ $comment->comment }}</p>
                                        
                                        @if($comment->documents)
                                            <div class="mt-3">
                                                <a href="{{ asset('assets/task_docs/'.$comment->documents) }}" target="_blank" class="badge bg-white text-primary border rounded-pill text-decoration-none px-3 py-2">
                                                    <i class="fas fa-paperclip me-1"></i> {{ $comment->documents }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="far fa-calendar-minus d-block mb-2 fs-2 opacity-25"></i>
                                Start a discussion about this task
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">New Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.hr.projects.comments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="task_id" value="{{ $task->id }}">
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Your Message</label>
                        <textarea name="comment" class="form-control rounded-3" rows="4" placeholder="What's the update?" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Attach File (Optional)</label>
                        <input type="file" name="documents" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Post Comment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #856404; }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
</style>
@endsection
