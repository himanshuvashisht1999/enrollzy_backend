@extends('admin.layouts.master')

@section('title', 'Edit Stream Tab')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Stream Tab: {{ $homepageStreamTab->name }}</h4>
        <p class="text-muted small mb-0">Update stream details, keywords, exams, states, and courses.</p>
    </div>
    <a href="{{ route('admin.homepage-stream-tabs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.homepage-stream-tabs.update', $homepageStreamTab->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stream Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $homepageStreamTab->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Slug / Key (Unique Identifier)</label>
                    <input type="text" name="key" class="form-control @error('key') is-invalid @enderror" value="{{ old('key', $homepageStreamTab->key) }}">
                    <small class="text-muted">Used for tab switching URL (#tab-key)</small>
                    @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{--
            <div class="mb-4">
                <label class="form-label fw-bold">Search Keywords (Comma Separated)</label>
                <textarea name="keywords" class="form-control" rows="2">{{ old('keywords', is_array($homepageStreamTab->keywords) ? implode(', ', $homepageStreamTab->keywords) : $homepageStreamTab->keywords) }}</textarea>
                <small class="text-muted">Universities in DB matching these keywords will be automatically filtered under this stream tab.</small>
            </div>
            --}}

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Featured Colleges</label>
                    <select name="feature_colleges[]" class="form-select select2" multiple>
                        @php $selectedColleges = old('feature_colleges', $homepageStreamTab->feature_colleges ?? []); @endphp
                        @foreach($organisations as $id => $orgName)
                            <option value="{{ $id }}" {{ is_array($selectedColleges) && in_array($id, $selectedColleges) ? 'selected' : '' }}>{{ $orgName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Important Exams</label>
                    <select name="default_exams[]" class="form-select select2" multiple>
                        @php $selectedExams = old('default_exams', $homepageStreamTab->default_exams ?? []); @endphp
                        @foreach($exams as $id => $examName)
                            <option value="{{ $id }}" {{ is_array($selectedExams) && in_array($id, $selectedExams) ? 'selected' : '' }}>{{ $examName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Related Courses</label>
                    <select name="default_courses[]" class="form-select select2" multiple>
                        @php $selectedCourses = old('default_courses', $homepageStreamTab->default_courses ?? []); @endphp
                        @foreach($courses as $id => $courseName)
                            <option value="{{ $id }}" {{ is_array($selectedCourses) && in_array($id, $selectedCourses) ? 'selected' : '' }}>{{ $courseName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Top States</label>
                    <select name="default_states[]" class="form-select select2" multiple>
                        @php $selectedStates = old('default_states', $homepageStreamTab->default_states ?? []); @endphp
                        @foreach($states as $state)
                            <option value="{{ $state }}" {{ is_array($selectedStates) && in_array($state, $selectedStates) ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sort Order <span class="text-danger">*</span></label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $homepageStreamTab->sort_order) }}" required>
                </div>
                <div class="col-md-6" style="display: none;">
                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <input type="hidden" name="status" value="1">
                    {{-- 
                    <select name="status" class="form-select" required>
                        <option value="1" {{ old('status', $homepageStreamTab->status) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $homepageStreamTab->status) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    --}}
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.homepage-stream-tabs.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Update Stream Tab</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select items',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
