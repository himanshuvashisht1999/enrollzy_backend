@extends('admin.layouts.master')

@section('title', 'Manage Counselling - ' . $dynamicExam->name)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Counselling: {{ $dynamicExam->name }}</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.dynamic-exams.counsellings.create', $dynamicExam->id) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Counselling
                    </a>
                    <a href="{{ route('admin.dynamic-exams.index') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dynamic Exams
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Authority</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($counsellings as $counselling)
                                <tr>
                                    <td>{{ $counselling->counselling_name }}</td>
                                    <td>{{ $counselling->counselling_type }}</td>
                                    <td>{{ $counselling->conducting_authority_name }}</td>
                                    <td>{{ $counselling->counselling_mode }}</td>
                                    <td>
                                        <span class="badge bg-{{ $counselling->status == 'Active' ? 'success' : 'warning' }}">
                                            {{ $counselling->status ?? 'Draft' }}
                                        </span>
                                    </td>
                                    <td>{{ $counselling->updated_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.dynamic-exams.counsellings.edit', [$dynamicExam->id, $counselling->id]) }}"
                                                class="btn btn-sm btn-info" title="Build Structure">
                                                <i class="fas fa-layer-group"></i>
                                            </a>
                                            <a href="{{ route('admin.dynamic-exams.counsellings.data', [$dynamicExam->id, $counselling->id]) }}"
                                                class="btn btn-sm btn-success" title="Fill Data">
                                                <i class="fas fa-pen-nib"></i>
                                            </a>
                                            <form
                                                action="{{ route('admin.dynamic-exams.counsellings.destroy', [$dynamicExam->id, $counselling->id]) }}"
                                                method="POST" class="d-inline-block"
                                                onsubmit="return confirm('Are you sure you want to delete this counselling?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No counselling records found for this exam.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
