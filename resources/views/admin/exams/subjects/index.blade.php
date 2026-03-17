@extends('admin.layouts.master')

@section('title', 'Exam Subjects')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Exam Subjects</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item small"><a href="{{ route('admin.exams.index') }}">Exams List</a></li>
                    <li class="breadcrumb-item small active" aria-current="page">Subjects List</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.exam-subjects.create', request()->only(['exam_id', 'exam_stage_id'])) }}"
            class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Add New Subject
        </a>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.exam-subjects.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Filter by Exam</label>
                    <select name="exam_id" class="form-select select2" onchange="this.form.submit()">
                        <option value="">All Exams</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Filter by Stage</label>
                    <select name="exam_stage_id" class="form-select select2" onchange="this.form.submit()">
                        <option value="">All Stages</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ request('exam_stage_id') == $stage->id ? 'selected' : '' }}>
                                {{ $stage->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.exam-subjects.index') }}" class="btn btn-light border w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Subject</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Exam / Stage</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $subject->subject_name }}</div>
                                    @if($subject->subject_group)
                                        <small class="text-muted">{{ $subject->subject_group }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $subject->subject_code ?? '-' }}</code></td>
                                <td>
                                    <span
                                        class="badge {{ $subject->subject_type == 'Mandatory' ? 'bg-primary' : 'bg-info' }} rounded-pill">
                                        {{ $subject->subject_type }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-medium">{{ $subject->exam->name }}</div>
                                    <div class="small text-muted">{{ $subject->examStage->title }}</div>
                                </td>
                                <td>
                                    @if($subject->status == 'Active')
                                        <span class="badge bg-success-subtle text-success border border-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger">Deprecated</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.exam-subjects.edit', $subject->id) }}"
                                            class="btn btn-sm btn-outline-primary rounded-circle"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.exam-subjects.destroy', $subject->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"
                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                                title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-book fa-3x mb-3 opacity-25"></i>
                                    <p>No subjects found for the selected criteria.</p>
                                    <a href="{{ route('admin.exam-subjects.create', request()->only(['exam_id', 'exam_stage_id'])) }}"
                                        class="btn btn-sm btn-outline-primary">Add Your First Subject</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($subjects->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $subjects->links() }}
            </div>
        @endif
    </div>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
@endpush