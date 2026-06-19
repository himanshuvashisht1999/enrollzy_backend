@extends('admin.layouts.master')

@section('title', 'Global SEO Defaults')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Global SEO Defaults</h2>
    </div>

    <form action="{{ route('admin.seo_defaults.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white pb-0">
                <h5 class="mb-3 text-primary">Global Fallback Meta Settings</h5>
                <p class="text-muted small">These settings will be used as a fallback when specific pages, courses, or blogs do not have their own SEO settings defined.</p>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Default Meta Title</label>
                        <input type="text" name="default_meta_title" class="form-control" value="{{ old('default_meta_title', $setting->default_meta_title) }}">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Default Meta Description</label>
                        <textarea name="default_meta_description" class="form-control" rows="3">{{ old('default_meta_description', $setting->default_meta_description) }}</textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title Separator</label>
                        <input type="text" name="separator" class="form-control" value="{{ old('separator', $setting->separator) }}" placeholder="e.g. - or |">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title Format</label>
                        <input type="text" name="title_format" class="form-control" value="{{ old('title_format', $setting->title_format) }}" placeholder="e.g. {title} {separator} {organization}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Schema Type</label>
                        <input type="text" name="default_schema_type" class="form-control" value="{{ old('default_schema_type', $setting->default_schema_type) }}" placeholder="e.g. WebPage">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Robots</label>
                        <input type="text" name="default_robots" class="form-control" value="{{ old('default_robots', $setting->default_robots) }}" placeholder="index, follow">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Language</label>
                        <input type="text" name="default_language" class="form-control" value="{{ old('default_language', $setting->default_language) }}" placeholder="en_US">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Country</label>
                        <input type="text" name="default_country" class="form-control" value="{{ old('default_country', $setting->default_country) }}" placeholder="IN">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Author</label>
                        <input type="text" name="default_author" class="form-control" value="{{ old('default_author', $setting->default_author) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Publisher</label>
                        <input type="text" name="default_publisher" class="form-control" value="{{ old('default_publisher', $setting->default_publisher) }}">
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary">Default Sharing Images</h5>

                    <div class="col-md-6 mb-4">
                        <div class="card bg-light h-100">
                            <div class="card-body">
                                <label class="form-label fw-bold">Default OG Image (Facebook/LinkedIn)</label>
                                <input type="file" name="default_og_image" class="form-control mb-2" accept="image/*">
                                @if($setting->default_og_image)
                                    <div class="mt-2 text-center bg-white p-2 border rounded">
                                        <img src="{{ asset($setting->default_og_image) }}" alt="OG Image" style="max-height: 100px; max-width: 100%; object-fit: contain;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card bg-light h-100">
                            <div class="card-body">
                                <label class="form-label fw-bold">Default Twitter Image</label>
                                <input type="file" name="default_twitter_image" class="form-control mb-2" accept="image/*">
                                @if($setting->default_twitter_image)
                                    <div class="mt-2 text-center bg-white p-2 border rounded">
                                        <img src="{{ asset($setting->default_twitter_image) }}" alt="Twitter Image" style="max-height: 100px; max-width: 100%; object-fit: contain;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Save Defaults</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
