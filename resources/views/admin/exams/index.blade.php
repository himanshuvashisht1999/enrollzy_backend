@extends('admin.layouts.master')

@section('title', 'Exams List')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Exams List</h1>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Exam
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Exam Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Visibility</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>{{ $exam->id }}</td>
                                <td>
                                    <div class="fw-bold">{{ $exam->name }}</div>
                                    @if($exam->short_name)
                                        <small class="text-muted">({{ $exam->short_name }})</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $exam->exam_type ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    {{ is_array($exam->exam_category) ? implode(', ', $exam->exam_category) : ($exam->exam_category ?? '-') }}
                                </td>
                                <td>
                                    @if($exam->status == 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($exam->status == 'Upcoming')
                                        <span class="badge bg-info">Upcoming</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $exam->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($exam->visibility == 'Public')
                                        <span class="badge bg-primary">Public</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $exam->visibility }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.exams.counsellings.index', $exam->id) }}"
                                        class="btn btn-sm btn-info me-1" title="Manage Counselling">
                                        <i class="fas fa-gavel"></i>
                                    </a>
                                    @if($exam->has_stages)
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-dark me-1" type="button"
                                                id="stagesDropdown{{ $exam->id }}" data-bs-toggle="dropdown" aria-expanded="false"
                                                title="Manage Stages">
                                                <i class="fas fa-layer-group"></i>
                                            </button>
                                            <ul class="dropdown-menu shadow border-0 py-2" style="min-width: 280px;"
                                                aria-labelledby="stagesDropdown{{ $exam->id }}">
                                                <li class="dropdown-header text-primary fw-bold border-bottom pb-2 mb-2">Selected
                                                    Exam Stages</li>
                                                @forelse($exam->selectedStages as $selectedStage)
                                                    <li
                                                        class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom-light">
                                                        <div class="d-flex align-items-center">
                                                            <span class="fw-medium">{{ $selectedStage->masterStage->title ?? 'Unknown Stage' }}</span>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('admin.exams.stages.edit', [$exam->id, $selectedStage->exam_stage_id]) }}"
                                                                class="btn btn-xs btn-outline-primary" title="Edit Stage Data">
                                                                <i class="fas fa-cog"></i>
                                                            </a>
                                                            <a href="{{ route('admin.exam-subjects.index', ['exam_id' => $exam->id, 'exam_stage_id' => $selectedStage->exam_stage_id]) }}"
                                                                class="btn btn-xs btn-primary text-white" title="Manage Subjects">
                                                                <i class="fas fa-book"></i>
                                                            </a>
                                                        </div>
                                                    </li>
                                                @empty
                                                    <li><span class="dropdown-item-text text-muted">No stages selected</span></li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    @endif
                                    <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-sm btn-primary me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this exam?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <p class="mb-0">No Exams Found</p>
                                    <a href="{{ route('admin.exams.create') }}"
                                        class="btn btn-sm btn-outline-primary mt-2">Create First Exam</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                @if(is_object($exams) && method_exists($exams, 'links'))
                    {{ $exams->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection