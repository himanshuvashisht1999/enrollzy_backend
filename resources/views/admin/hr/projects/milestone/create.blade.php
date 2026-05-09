@extends('admin.layouts.master')

@section('title', 'Add Milestone')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">New Milestone Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.projects.milestones.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Milestone Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select rounded-3" required>
                            <option value="">Select Project</option>
                            @foreach($project as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control rounded-3" value="{{ old('start_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Due Date</label>
                        <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="not_started">Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control rounded-4" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.hr.projects.milestones.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Add Milestone</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
