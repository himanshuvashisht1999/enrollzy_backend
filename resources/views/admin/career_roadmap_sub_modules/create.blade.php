@extends('admin.layouts.master')

@section('title', 'Add Career Roadmap Sub Module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Career Roadmap Sub Module</h4>
    <a href="{{ route('admin.career-roadmap-sub-modules.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.career-roadmap-sub-modules.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="stage_id" class="form-label fw-bold">Stage <span class="text-danger">*</span></label>
                            <select class="form-select @error('stage_id') is-invalid @enderror" id="stage_id" name="stage_id" required>
                                <option value="" disabled selected>Select Stage</option>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->title }} ({{ $stage->category->name ?? '' }})</option>
                                @endforeach
                            </select>
                            @error('stage_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="parent_id" class="form-label fw-bold">Parent Module (Optional)</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">None (Top Level Module)</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->title }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select if this is a nested sub-module.</small>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label fw-bold">Image / Icon</label>
                            <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="long_description" class="form-label fw-bold">Long Description</label>
                        <textarea class="form-control @error('long_description') is-invalid @enderror" id="long_description" name="long_description" rows="8">{{ old('long_description') }}</textarea>
                        @error('long_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">Custom Fields (Optional)</h5>
                    <p class="text-muted small">Add custom key-value pairs (e.g. Salary: 50k, Duration: 2 Years).</p>
                    
                    <div id="custom-fields-container">
                        <!-- Custom fields will be appended here -->
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-custom-field">
                        <i class="fas fa-plus"></i> Add Custom Field
                    </button>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" checked value="1">
                        <label class="form-check-label" for="status">Active Status</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">Save Sub Module</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    if (typeof initializeTinyMCE === 'function') {
        initializeTinyMCE('#long_description');
    }
    
    $(document).ready(function() {
        const $container = $('#custom-fields-container');
        const $addButton = $('#add-custom-field');

        function createFieldRow(name = '', value = '') {
            const rowHtml = `
                <div class="row mb-2 custom-field-row">
                    <div class="col-md-5">
                        <textarea class="form-control" name="custom_field_names[]" placeholder="Field Name (e.g. Salary)" rows="2">${name}</textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" name="custom_field_values[]" placeholder="Field Value (e.g. 50k - 1L)" rows="2">${value}</textarea>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-danger remove-custom-field"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `;
            $container.append(rowHtml);
        }

        $container.on('click', '.remove-custom-field', function() {
            $(this).closest('.custom-field-row').remove();
        });

        $addButton.on('click', function() {
            createFieldRow();
        });
    });
</script>
@endpush
@endsection
