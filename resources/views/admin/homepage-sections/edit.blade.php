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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <form action="{{ route('homepage-sections.update-details', $homepageSection->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $currentSettings = is_array($homepageSection->settings) ? $homepageSection->settings : json_decode($homepageSection->settings ?? '[]', true);
                        $badgeTextVal = $currentSettings['badge_text'] ?? ($homepageSection->cta_title ?? "India's no.1 Market place");
                    @endphp

                    <div class="mb-3">
                        <label for="badge_text" class="form-label fw-bold">Top Badge Pill Text</label>
                        <input type="text" class="form-control" id="badge_text" name="badge_text" value="{{ old('badge_text', $badgeTextVal) }}" placeholder="e.g. India's no.1 Market place">
                    </div>

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

                    <div class="mb-3">
                        <label for="cta_url" class="form-label fw-bold">Call-to-Action (Button) URL</label>
                        <input type="text" class="form-control @error('cta_url') is-invalid @enderror" id="cta_url" name="cta_url" value="{{ old('cta_url', $homepageSection->cta_url) }}" placeholder="e.g. /courses or https://example.com/courses">
                        @error('cta_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="image" class="form-label fw-bold">Section Image</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($homepageSection->image)
                            <div class="mt-2">
                                <small class="text-muted d-block mb-1">Current Image:</small>
                                <img src="{{ asset($homepageSection->image) }}" alt="Current Image" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        @endif
                    </div>

                    @if($homepageSection->section_key === 'marketplace')
                        <hr class="my-4">
                        <h5 class="fw-bold text-primary mb-2">
                            <i class="fas fa-eye me-2"></i> Marketplace Items Visibility (Show / Hide)
                        </h5>
                        <p class="text-muted small mb-4">Toggle individual marketplace cards ON or OFF to control their visibility on the homepage.</p>
                        
                        @php
                            $currentSettings = is_array($homepageSection->settings) ? $homepageSection->settings : json_decode($homepageSection->settings ?? '[]', true);
                            $itemsVis = $currentSettings['items_visibility'] ?? [];
                            $allMarketplaceKeys = [
                                'schools' => 'Schools',
                                'coaching' => 'Coaching',
                                'universities' => 'Universities',
                                'colleges' => 'Colleges',
                                'mentors' => 'Mentors',
                                'scholarships' => 'Scholarships',
                                'internships' => 'Internships',
                                'top_exams' => 'Top Exams',
                                'exam_bodies' => 'Exam Bodies',
                                'counselling_bodies' => 'Counselling Bodies',
                                'regulatory_bodies' => 'Regulatory Bodies',
                                'govt_agencies' => 'Govt Agencies',
                                'blogs' => 'Blogs & Guidance',
                                'all_institutions' => 'All Institutions',
                            ];
                        @endphp

                        <div class="row g-3 mb-4">
                            @foreach($allMarketplaceKeys as $key => $label)
                                @php $isVis = !isset($itemsVis[$key]) || $itemsVis[$key] == '1' || $itemsVis[$key] === true; @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                        <span class="fw-bold text-dark fs-6">{{ $label }}</span>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="items_visibility[{{ $key }}]" value="0">
                                            <input class="form-check-input" type="checkbox" name="items_visibility[{{ $key }}]" value="1" id="vis_{{ $key }}" {{ $isVis ? 'checked' : '' }}>
                                            <label class="form-check-label ms-1 small text-muted" for="vis_{{ $key }}">{{ $isVis ? 'Visible' : 'Hidden' }}</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

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
