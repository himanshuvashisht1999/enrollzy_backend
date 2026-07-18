@extends('admin.layouts.master')

@section('title', 'Institute Marquee Setup')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Institute Marquee (Logos)</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLogoModal">
        <i class="fas fa-plus"></i> Add Institute Logo
    </button>
</div>

<div class="row mb-4 align-items-center bg-white p-3 rounded shadow-sm mx-0">
    <div class="col-md-6">
        <form action="{{ route('admin.institute-marquees.update-direction') }}" method="POST" class="d-flex align-items-center gap-3">
            @csrf
            <label class="fw-bold mb-0 text-nowrap">Global Scroll Direction:</label>
            @php $currentDir = $marquees->first()->direction ?? 'rtl'; @endphp
            <select name="direction" class="form-select w-auto">
                <option value="rtl" {{ $currentDir == 'rtl' ? 'selected' : '' }}>Right to Left (Default)</option>
                <option value="ltr" {{ $currentDir == 'ltr' ? 'selected' : '' }}>Left to Right</option>
            </select>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<div class="row">
    @forelse($marquees as $item)
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm h-100 position-relative">
            <div class="card-body text-center p-4">
                <img src="{{ asset($item->logo) }}" alt="Logo" class="img-fluid mb-3" style="max-height: 60px; object-fit: contain;">
                <h6 class="mb-1 fw-bold">{{ $item->name ?? 'Untitled' }}</h6>
                @if($item->heading) <div class="small text-primary fw-bold">{{ $item->heading }}</div> @endif
                @if($item->subheading) <div class="x-small text-muted">{{ $item->subheading }}</div> @endif
                
                <div class="mt-3 d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-info edit-btn" 
                        data-id="{{ $item->id }}"
                        data-name="{{ $item->name }}"
                        data-heading="{{ $item->heading }}"
                        data-subheading="{{ $item->subheading }}"
                        data-sort="{{ $item->sort_order }}"
                        data-url="{{ $item->logo_url }}"
                        data-logo="{{ asset($item->logo) }}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.institute-marquees.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this logo?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="position-absolute top-0 end-0 p-2">
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" data-id="{{ $item->id }}" {{ $item->status ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="text-muted">No institute logos found. Start by adding one!</div>
    </div>
    @endforelse
</div>

<!-- Add Modal -->
<div class="modal fade" id="addLogoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add Institute Logo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.institute-marquees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Institute Logo <span class="text-danger">*</span></label>
                        <input type="file" name="logo" class="form-control" accept="image/*" required>
                        <div class="form-text">Recommended size: 200x80px (Transparent PNG)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Institute Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Allen">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Heading (Optional)</label>
                        <input type="text" name="heading" class="form-control" placeholder="Tagline or Heading">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subheading (Optional)</label>
                        <textarea name="subheading" class="form-control" rows="2" placeholder="Brief description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo Target URL (Optional)</label>
                        <input type="url" name="logo_url" class="form-control" placeholder="https://example.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Logo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editLogoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Edit Institute Logo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <img id="editPreview" src="" alt="Preview" class="img-fluid rounded border p-2" style="max-height: 100px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Change Logo (Optional)</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Institute Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Heading</label>
                        <input type="text" name="heading" id="edit_heading" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subheading</label>
                        <textarea name="subheading" id="edit_subheading" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo Target URL (Optional)</label>
                        <input type="url" name="logo_url" id="edit_url" class="form-control" placeholder="https://example.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info text-white">Update Logo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
$(function() {
    $('.edit-btn').click(function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let heading = $(this).data('heading');
        let subheading = $(this).data('subheading');
        let sort = $(this).data('sort');
        let url = $(this).data('url');
        let logo = $(this).data('logo');

        $('#editForm').attr('action', `/admin/institute-marquees/${id}`);
        $('#edit_name').val(name);
        $('#edit_heading').val(heading);
        $('#edit_subheading').val(subheading);
        $('#edit_sort').val(sort);
        $('#edit_url').val(url);
        $('#editPreview').attr('src', logo);

        $('#editLogoModal').modal('show');
    });

    $('.status-toggle').change(function() {
        let id = $(this).data('id');
        $.post(`/admin/institute-marquees/${id}/toggle-status`, { _token: '{{ csrf_token() }}' }, function(res) {
            if(res.success) toastr.success('Status updated');
        });
    });
});
</script>
@endpush
@endsection
