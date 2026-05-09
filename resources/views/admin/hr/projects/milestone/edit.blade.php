@extends('admin.layouts.master')

@section('title', 'Edit Milestone')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="fas fa-flag text-warning"></i>
                        </div>
                        <h5 class="m-0 fw-bold">Edit Milestone</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.hr.projects.milestones.update', encrypt($milestone->id)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Milestone Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-pill px-3" placeholder="Enter title" required value="{{ old('title', $milestone->title) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Select Project <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-select rounded-pill px-3" required>
                                    <option value="">Choose Project</option>
                                    @foreach($project as $p)
                                        <option value="{{ $p->id }}" {{ $milestone->project_id == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control rounded-pill px-3" required value="{{ old('start_date', $milestone->start_date) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" name="due_date" class="form-control rounded-pill px-3" value="{{ old('due_date', $milestone->due_date) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select rounded-pill px-3" required>
                                    <option value="pending" {{ $milestone->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $milestone->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $milestone->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $milestone->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Short Note</label>
                                <textarea name="note" class="form-control rounded-4" rows="3">{{ old('note', $milestone->note) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5 pt-2">
                            <a href="{{ route('admin.hr.projects.milestones.index') }}" class="btn btn-light rounded-pill px-4">
                                <i class="fas fa-arrow-left me-1 small"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                                Update Milestone <i class="fas fa-save ms-1 small"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
