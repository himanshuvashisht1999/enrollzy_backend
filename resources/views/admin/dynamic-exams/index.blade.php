@extends('admin.layouts.master')

@section('title', 'Dynamic Exams')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Dynamic Exams</h4>
        <p class="text-muted mb-0">Manage exam schemas dynamically.</p>
    </div>
    <div class="d-flex align-items-center">
        <form action="{{ route('admin.dynamic-exams.index') }}" method="GET" class="d-flex me-3">
            <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search exams..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.dynamic-exams.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none ms-1">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.dynamic-exams.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Dynamic Exam
        </a>
    </div>
</div>



<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Exam Name</th>
                        <th style="width: 90px;" class="text-center">Sort Order</th>
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
                        <td class="text-center" style="width: 100px;">
                            <input type="number" 
                                   class="form-control form-control-sm text-center dynamic-exam-sort-input" 
                                   data-id="{{ $exam->id }}" 
                                   value="{{ $exam->sort_order ?? 1 }}" 
                                   min="0"
                                   style="width: 70px; margin: 0 auto; font-weight: 600; font-size: 13px; border-radius: 6px;">
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $exam->exam_type ?? 'N/A' }}</span>
                        </td>
                        <td>
                            {{ is_array($exam->exam_category) ? implode(', ', $exam->exam_category) : ($exam->exam_category ?? '-') }}
                        </td>
                        <td>
                            @if($exam->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">Public</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.dynamic-exams.counsellings.index', $exam->id) }}" class="btn btn-sm btn-info me-1" title="Manage Counselling">
                                <i class="fas fa-gavel"></i>
                            </a>
                            <a href="{{ route('admin.dynamic-exams.edit', $exam->id) }}" class="btn btn-sm btn-secondary me-1" title="Manage Structure">
                                <i class="fas fa-layer-group"></i>
                            </a>
                            <a href="{{ route('admin.dynamic-exams.data', $exam->id) }}" class="btn btn-sm btn-primary me-1" title="Fill Exam">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.dynamic-exams.destroy', $exam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this exam?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No dynamic exams found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $exams->links() }}
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    $(document).on('change', '.dynamic-exam-sort-input', function() {
        const input = $(this);
        const id = input.data('id');
        const sortOrder = parseInt(input.val()) || 0;

        input.prop('disabled', true).css('opacity', '0.6');

        $.ajax({
            url: "{{ route('admin.dynamic-exams.update-sort-order') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                sort_order: sortOrder
            },
            success: function(res) {
                input.prop('disabled', false).css('opacity', '1');
                if (res.status === 1) {
                    input.css({
                        'border-color': '#10b981',
                        'box-shadow': '0 0 0 2px rgba(16, 185, 129, 0.25)'
                    });
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Sort order updated successfully');
                    }
                    setTimeout(() => {
                        input.css({
                            'border-color': '',
                            'box-shadow': ''
                        });
                    }, 1200);
                }
            },
            error: function() {
                input.prop('disabled', false).css('opacity', '1');
                input.css({
                    'border-color': '#ef4444',
                    'box-shadow': '0 0 0 2px rgba(239, 68, 68, 0.25)'
                });
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to update sort order');
                }
            }
        });
    });
});
</script>
@endpush
