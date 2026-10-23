@extends('admin.layouts.master')

@section('title', 'Edit Homepage Section')

@section('content')
@php
    $currentSettings = (is_array($homepageSection->settings) ? $homepageSection->settings : json_decode($homepageSection->settings ?? '[]', true)) ?: [];
    $badgeTextVal = $currentSettings['badge_text'] ?? ($homepageSection->cta_title ?? "India's no.1 Market place");
    // fields of this section on the current homepage (null = a section of the previous design)
    $core = $schema['core'] ?? ['badge' => 'Top Badge Pill Text', 'title' => 'Title (Heading)', 'subtitle' => 'Subtitle (Description)', 'cta_title' => 'Call-to-Action (Button) Text', 'cta_url' => 'Call-to-Action (Button) URL', 'image' => 'Section Image'];
    if ($schema && $homepageSection->section_key === 'hero_banner') $badgeTextVal = $currentSettings['badge_text'] ?? $homepageSection->cta_title;
    if ($schema && !isset($currentSettings['badge_text'])) $badgeTextVal = $homepageSection->section_key === 'hero_banner' ? $badgeTextVal : '';
@endphp
<div class="row">
    <div class="col-lg-9 mx-auto">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-edit me-2"></i> Edit Section: {{ $homepageSection->name }}
                </h5>
                <a href="{{ route('homepage-sections.index') }}" class="btn btn-sm btn-light border rounded-pill"><i class="fas fa-arrow-left me-1"></i> All sections</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($schema)
                    <div class="alert alert-info border-0 rounded-3 mb-4">
                        <div class="fw-semibold mb-1"><i class="fas fa-info-circle me-1"></i> {{ $schema['about'] }}</div>
                        <div class="small">Empty fields show the design's own text on the website. Changes appear on the homepage as soon as you save.</div>
                        @if(!empty($schema['data']))
                            <div class="small mt-2">The cards in this section come from:
                                @foreach($schema['data'] as [$label, $path])<a class="ms-1 fw-semibold" href="{{ url($path) }}" target="_blank">{{ $label }} <i class="fas fa-external-link-alt" style="font-size:.7em"></i></a>@if(!$loop->last) ·@endif @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning border-0 rounded-3 mb-4">
                        <i class="fas fa-exclamation-triangle me-1"></i> This section belongs to the previous homepage design. The current homepage does not show it, so changes here have no effect on the website.
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                @endif

                <form action="{{ route('homepage-sections.update-details', $homepageSection->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @isset($core['badge'])
                        <div class="mb-3">
                            <label for="badge_text" class="form-label fw-bold">{{ $core['badge'] }}</label>
                            <input type="text" class="form-control" id="badge_text" name="badge_text" value="{{ old('badge_text', $badgeTextVal) }}">
                        </div>
                    @endisset

                    @isset($core['title'])
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">{{ $core['title'] }}</label>
                            @if(!empty($schema['heading_help']) && str_contains($schema['heading_help'], 'line break'))
                                <textarea class="form-control @error('title') is-invalid @enderror" id="title" name="title" rows="2">{{ old('title', $homepageSection->title) }}</textarea>
                            @else
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $homepageSection->title) }}">
                            @endif
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endisset

                    @isset($core['subtitle'])
                        <div class="mb-3">
                            <label for="subtitle" class="form-label fw-bold">{{ $core['subtitle'] }}</label>
                            <textarea class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" name="subtitle" rows="3">{{ old('subtitle', $homepageSection->subtitle) }}</textarea>
                            @if(!empty($schema['heading_help']))<div class="form-text">{{ $schema['heading_help'] }}</div>@endif
                            @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endisset

                    @if(isset($core['cta_title']) || isset($core['cta_url']))
                        <div class="row g-3 mb-3">
                            @isset($core['cta_title'])
                                <div class="col-md-6">
                                    <label for="cta_title" class="form-label fw-bold">{{ $core['cta_title'] }}</label>
                                    <input type="text" class="form-control @error('cta_title') is-invalid @enderror" id="cta_title" name="cta_title" value="{{ old('cta_title', $homepageSection->cta_title) }}">
                                    @error('cta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endisset
                            @isset($core['cta_url'])
                                <div class="col-md-6">
                                    <label for="cta_url" class="form-label fw-bold">{{ $core['cta_url'] }}</label>
                                    <input type="text" class="form-control @error('cta_url') is-invalid @enderror" id="cta_url" name="cta_url" value="{{ old('cta_url', $homepageSection->cta_url) }}" placeholder="/experts or https://...">
                                    @error('cta_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endisset
                        </div>
                    @else
                        {{-- keep the stored values: this section has no button on the homepage --}}
                        <input type="hidden" name="cta_title" value="{{ $homepageSection->cta_title }}">
                        <input type="hidden" name="cta_url" value="{{ $homepageSection->cta_url }}">
                    @endif
                    @unless(isset($core['title']))<input type="hidden" name="title" value="{{ $homepageSection->title }}">@endunless
                    @unless(isset($core['subtitle']))<input type="hidden" name="subtitle" value="{{ $homepageSection->subtitle }}">@endunless

                    @if(!empty($schema['fields']))
                        <hr class="my-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-sliders-h me-1"></i> Section content</h6>
                        @foreach($schema['fields'] as $key => $f)
                            @include('admin.homepage-sections._field', ['f' => $f, 'name' => 'settings[' . $key . ']', 'value' => $currentSettings[$key] ?? null, 'depth' => 0])
                        @endforeach
                    @endif

                    @isset($core['image'])
                        <div class="mb-4">
                            <label for="image" class="form-label fw-bold">{{ $core['image'] }}</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($homepageSection->image)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">Current Image:</small>
                                    <img src="{{ asset($homepageSection->image) }}" alt="Current Image" class="img-thumbnail" style="max-height: 120px;">
                                </div>
                            @endif
                        </div>
                    @endisset

                    @if($homepageSection->section_key === 'marketplace')
                        <hr class="my-4">
                        <h5 class="fw-bold text-primary mb-2">
                            <i class="fas fa-eye me-2"></i> Marketplace Items Visibility (Show / Hide)
                        </h5>
                        <p class="text-muted small mb-4">Toggle individual marketplace cards ON or OFF to control their visibility on the homepage.</p>
                        @php
                            $itemsVis = $currentSettings['items_visibility'] ?? [];
                            $allMarketplaceKeys = [
                                'schools' => 'Schools', 'coaching' => 'Coaching', 'universities' => 'Universities', 'colleges' => 'Colleges',
                                'mentors' => 'Mentors', 'scholarships' => 'Scholarships', 'internships' => 'Internships', 'top_exams' => 'Top Exams',
                                'exam_bodies' => 'Exam Bodies', 'counselling_bodies' => 'Counselling Bodies', 'regulatory_bodies' => 'Regulatory Bodies',
                                'govt_agencies' => 'Govt Agencies', 'blogs' => 'Blogs & Guidance', 'all_institutions' => 'All Institutions',
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

@push('js')
<script>
    // repeaters: add a row from the template, remove, move up / down (rows keep their order when saved)
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.hs-add, .hs-remove, .hs-up, .hs-down');
        if (!b) return;
        e.preventDefault();
        if (b.classList.contains('hs-add')) {
            var rep = b.closest('.hs-repeater'), tpl = rep.querySelector(':scope > .hs-template');
            var id = 'n' + Date.now() + Math.floor(Math.random() * 1000);
            var box = document.createElement('div');
            box.innerHTML = tpl.innerHTML.split('__R' + rep.getAttribute('data-depth') + '__').join(id);
            var row = box.firstElementChild;
            rep.querySelector(':scope > .hs-rows').appendChild(row);
            var first = row.querySelector('input[type=text], select'); if (first) first.focus();
            return;
        }
        var r = b.closest('.hs-row');
        if (b.classList.contains('hs-remove')) { if (confirm('Remove this row?')) r.remove(); }
        else if (b.classList.contains('hs-up') && r.previousElementSibling) r.parentNode.insertBefore(r, r.previousElementSibling);
        else if (b.classList.contains('hs-down') && r.nextElementSibling) r.parentNode.insertBefore(r.nextElementSibling, r);
    });
</script>
@endpush
@endsection
