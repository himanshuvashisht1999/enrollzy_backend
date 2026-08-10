@extends('admin.layouts.master')

@section('title', 'Edit Career Roadmap Sub Module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Career Roadmap Sub Module</h4>
    <a href="{{ route('admin.career-roadmap-sub-modules.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.career-roadmap-sub-modules.update', $careerRoadmapSubModule->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="stage_id" class="form-label fw-bold">Stage <span class="text-danger">*</span></label>
                            <select class="form-select @error('stage_id') is-invalid @enderror" id="stage_id" name="stage_id" required>
                                <option value="" disabled>Select Stage</option>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" {{ old('stage_id', $careerRoadmapSubModule->stage_id) == $stage->id ? 'selected' : '' }}>{{ $stage->title }} ({{ $stage->category->name ?? '' }})</option>
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
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $careerRoadmapSubModule->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->title }}</option>
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
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $careerRoadmapSubModule->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label fw-bold">Image / Icon</label>
                            @if($careerRoadmapSubModule->image)
                                <div class="mb-2">
                                    <img src="{{ asset($careerRoadmapSubModule->image) }}" alt="Current Image" class="rounded border" style="max-height: 80px;">
                                </div>
                            @endif
                            <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep the current image.</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $careerRoadmapSubModule->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="long_description" class="form-label fw-bold">Long Description</label>
                        <textarea class="form-control @error('long_description') is-invalid @enderror" id="long_description" name="long_description" rows="8">{{ old('long_description', $careerRoadmapSubModule->long_description) }}</textarea>
                        @error('long_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $cFields = !empty($careerRoadmapSubModule->custom_fields) 
                            ? (is_array($careerRoadmapSubModule->custom_fields) ? $careerRoadmapSubModule->custom_fields : json_decode($careerRoadmapSubModule->custom_fields, true)) 
                            : [];
                    @endphp

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary"><i class="fas fa-link me-2"></i>Action Buttons Configuration</h5>
                    <p class="text-muted small">Configure left and right buttons for this card. Note: A button will only be displayed on the website if its <strong>Label</strong> is provided.</p>
                    
                    <div class="row bg-light p-3 rounded mb-4 border">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold mb-3 text-dark">Left Button Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Button Label (Text)</label>
                                <input type="text" class="form-control" name="btn1_label" value="{{ old('btn1_label', $cFields['btn1_label'] ?? 'Get Guidance') }}" placeholder="e.g. Get Guidance (Leave empty to hide button)">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Button URL</label>
                                <input type="text" class="form-control" name="btn1_url" value="{{ old('btn1_url', $cFields['btn1_url'] ?? '/contact-us') }}" placeholder="e.g. /contact-us or https://...">
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="fw-bold mb-3 text-dark">Right Button Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Button Label (Text)</label>
                                <input type="text" class="form-control" name="btn2_label" value="{{ old('btn2_label', $cFields['btn2_label'] ?? 'Talk to Counselor') }}" placeholder="e.g. Talk to Counselor (Leave empty to hide button)">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Button URL</label>
                                <input type="text" class="form-control" name="btn2_url" value="{{ old('btn2_url', $cFields['btn2_url'] ?? '/contact-us') }}" placeholder="e.g. /contact-us or https://...">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">Custom Fields (Optional)</h5>
                    <p class="text-muted small">Add custom key-value pairs (e.g. Salary: 50k, Duration: 2 Years).</p>
                    
                    <div id="custom-fields-container">
                        @if($careerRoadmapSubModule->custom_fields && is_array($careerRoadmapSubModule->custom_fields))
                            @foreach($careerRoadmapSubModule->custom_fields as $name => $value)
                                <div class="row mb-2 custom-field-row">
                                    <div class="col-md-5">
                                        <textarea class="form-control" name="custom_field_names[]" placeholder="Field Name (e.g. Salary)" rows="2">{{ $name }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <textarea class="form-control" name="custom_field_values[]" placeholder="Field Value (e.g. 50k - 1L)" rows="2">{{ $value }}</textarea>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-outline-danger remove-custom-field"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-custom-field">
                        <i class="fas fa-plus"></i> Add Custom Field
                    </button>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" {{ old('status', $careerRoadmapSubModule->status) ? 'checked' : '' }} value="1">
                        <label class="form-check-label" for="status">Active Status</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">Update Sub Module</button>
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
