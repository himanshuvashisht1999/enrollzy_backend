@extends('admin.layouts.master')

@section('title', 'Manage Header Links')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-link me-2"></i> Header Links</h5>
                <a href="{{ route('admin.header-links.create') }}" class="btn btn-primary rounded-pill">
                    <i class="fas fa-plus me-1"></i> Add New Link
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Title</th>
                                <th>URL</th>
                                <th class="text-center" width="150">Publish</th>
                                <th class="text-center" width="100">Order</th>
                                <th class="text-center" width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td class="fw-bold">
                                        @if($item->parent_id)
                                            <span class="text-muted small"><i class="fas fa-level-up-alt fa-rotate-90 me-1"></i> {{ $item->parent->title ?? '' }} / </span>
                                        @endif
                                        {{ $item->title }}
                                    </td>
                                    <td>{{ $item->url ?? '#' }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-flex justify-content-center align-items-center mb-0">
                                            <input class="form-check-input toggle-publish-switch" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   data-url="{{ route('admin.header-links.toggle-status', $item->id) }}"
                                                   {{ $item->status ? 'checked' : '' }}
                                                   style="cursor: pointer; width: 2.4em; height: 1.25em;">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $item->sort_order }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.header-links.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.header-links.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this link?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No header links found. Create one to get started.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $(document).on('change', '.toggle-publish-switch', function() {
            let $switch = $(this);
            let url = $switch.data('url');
            let isChecked = $switch.is(':checked');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PATCH'
                },
                success: function(res) {
                    if (res.success) {
                        $switch.prop('checked', res.status);
                        if (typeof toastr !== 'undefined') {
                            toastr.success(res.message);
                        }
                    } else {
                        $switch.prop('checked', !isChecked);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Failed to update status.');
                        }
                    }
                },
                error: function(xhr) {
                    $switch.prop('checked', !isChecked);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Something went wrong while updating status.');
                    }
                }
            });
        });
    });
</script>
@endpush
