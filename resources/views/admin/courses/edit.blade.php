@extends('admin.layouts.master')

@section('title', 'Edit Master Course')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.courses.index') }}" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
    <h3 class="fw-bold mt-2">Edit Master Course</h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Course Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Course Name</label>
                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $course->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Slug (Manual Update)</label>
                            <input type="text"
                                   name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $course->slug) }}"
                                   required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Slug will not automatically change if you update the name.</small>
                        </div>

                        {{-- Sort Order --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number"
                                   name="sort_order"
                                   class="form-control"
                                   value="{{ old('sort_order', $course->sort_order) }}">
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status', $course->status) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $course->status) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        {{-- Program Level --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Program Level</label>
                            <select name="program_level_id" class="form-select select2">
                                <option value="">-- Select Level --</option>
                                @foreach($programLevels as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('program_level_id', $course->program_level_id) == $level->id ? 'selected' : '' }}>
                                        {{ $level->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Stream Offered --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stream Offered</label>
                            <select name="stream_offered_id" class="form-select select2">
                                <option value="">-- Select Stream --</option>
                                @foreach($streamOffereds as $stream)
                                    <option value="{{ $stream->id }}"
                                        {{ old('stream_offered_id', $course->stream_offered_id) == $stream->id ? 'selected' : '' }}>
                                        {{ $stream->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Discipline --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Discipline</label>
                            <select name="discipline_id" class="form-select select2">
                                <option value="">-- Select Discipline --</option>
                                @foreach($disciplines as $disc)
                                    <option value="{{ $disc->id }}"
                                        {{ old('discipline_id', $course->discipline_id) == $disc->id ? 'selected' : '' }}>
                                        {{ $disc->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Duration --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Duration</label>
                            <input type="text"
                                   name="duration"
                                   class="form-control"
                                   value="{{ old('duration', $course->duration) }}"
                                   placeholder="e.g. 2 Years">
                        </div>

                        {{-- Submit --}}
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Update Course
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
