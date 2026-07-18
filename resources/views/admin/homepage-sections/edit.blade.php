@extends('admin.layouts.master')

@section('title', 'Edit Homepage Section')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-edit me-2"></i> Edit Section: {{ $homepageSection->name }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('homepage-sections.update-details', $homepageSection->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Title (Heading)</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $homepageSection->title) }}" placeholder="e.g. Talk To Experts">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subtitle" class="form-label fw-bold">Subtitle (Description)</label>
                        <textarea class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" name="subtitle" rows="3" placeholder="e.g. Connect with our counselors...">{{ old('subtitle', $homepageSection->subtitle) }}</textarea>
                        @error('subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="cta_title" class="form-label fw-bold">Call-to-Action (Button) Text</label>
                        <input type="text" class="form-control @error('cta_title') is-invalid @enderror" id="cta_title" name="cta_title" value="{{ old('cta_title', $homepageSection->cta_title) }}" placeholder="e.g. View More">
                        @error('cta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="cta_url" class="form-label fw-bold">Call-to-Action (Button) URL</label>
                        <input type="text" class="form-control @error('cta_url') is-invalid @enderror" id="cta_url" name="cta_url" value="{{ old('cta_url', $homepageSection->cta_url) }}" placeholder="e.g. /courses or https://example.com/courses">
                        @error('cta_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <a href="{{ route('homepage-sections.index') }}" class="btn btn-secondary rounded-pill px-4 me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
