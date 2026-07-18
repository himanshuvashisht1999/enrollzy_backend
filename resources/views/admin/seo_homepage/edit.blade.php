@extends('admin.layouts.master')

@section('title', 'Homepage SEO & Content')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Homepage SEO & Content</h2>
    </div>

    <form action="{{ route('admin.seo_homepage.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="homepageTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#meta" role="tab">SEO & Meta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#hero" role="tab">Hero Section</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#sections" role="tab">Dynamic Sections</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#faqs" role="tab">FAQs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#schema" role="tab">Schema Blocks</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- 1. SEO & Meta -->
                    <div class="tab-pane fade show active" id="meta" role="tabpanel">
                        <div class="row">
                            <h5 class="mb-3 text-primary">Standard Meta</h5>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $homepage->meta_title) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Focus Keyword</label>
                                <input type="text" name="focus_keyword" class="form-control" value="{{ old('focus_keyword', $homepage->focus_keyword) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description', $homepage->meta_description) }}</textarea>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Secondary Keywords</label>
                                <div id="secondary_keywords_container">
                                    @php $secKeywords = old('secondary_keywords', $homepage->secondary_keywords) ?: ['']; @endphp
                                    @foreach($secKeywords as $kw)
                                        <div class="input-group mb-2 keyword-row">
                                            <input type="text" name="secondary_keywords[]" class="form-control" value="{{ $kw }}">
                                            <button type="button" class="btn btn-outline-danger remove-keyword"><i class="fas fa-trash"></i></button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary mt-1" id="add-keyword"><i class="fas fa-plus"></i> Add Keyword</button>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Crawling & Indexing</h5>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Canonical URL</label>
                                <input type="url" name="canonical_url" class="form-control" value="{{ old('canonical_url', $homepage->canonical_url) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sitemap Priority</label>
                                <input type="text" name="sitemap_priority" class="form-control" value="{{ old('sitemap_priority', $homepage->sitemap_priority) }}" placeholder="1.0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Change Frequency</label>
                                <input type="text" name="change_frequency" class="form-control" value="{{ old('change_frequency', $homepage->change_frequency) }}" placeholder="daily">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Robots Override</label>
                                <input type="text" name="robots" class="form-control" value="{{ old('robots', $homepage->robots) }}" placeholder="index, follow">
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="allow_index" value="1" {{ old('allow_index', $homepage->allow_index) ? 'checked' : '' }}>
                                    <label class="form-check-label">Allow Index</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="allow_snippet" value="1" {{ old('allow_snippet', $homepage->allow_snippet) ? 'checked' : '' }}>
                                    <label class="form-check-label">Allow Snippet</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="allow_image_preview" value="1" {{ old('allow_image_preview', $homepage->allow_image_preview) ? 'checked' : '' }}>
                                    <label class="form-check-label">Allow Image Preview</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="allow_video_preview" value="1" {{ old('allow_video_preview', $homepage->allow_video_preview) ? 'checked' : '' }}>
                                    <label class="form-check-label">Allow Video Preview</label>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Open Graph & Twitter</h5>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">OG Title</label>
                                <input type="text" name="og_title" class="form-control" value="{{ old('og_title', $homepage->og_title) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Twitter Title</label>
                                <input type="text" name="twitter_title" class="form-control" value="{{ old('twitter_title', $homepage->twitter_title) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">OG Description</label>
                                <textarea name="og_description" class="form-control" rows="3">{{ old('og_description', $homepage->og_description) }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Twitter Description</label>
                                <textarea name="twitter_description" class="form-control" rows="3">{{ old('twitter_description', $homepage->twitter_description) }}</textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">OG Image</label>
                                <input type="file" name="og_image" class="form-control">
                                @if($homepage->og_image)
                                    <img src="{{ asset($homepage->og_image) }}" class="mt-2" style="max-height: 80px;">
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Twitter Image</label>
                                <input type="file" name="twitter_image" class="form-control">
                                @if($homepage->twitter_image)
                                    <img src="{{ asset($homepage->twitter_image) }}" class="mt-2" style="max-height: 80px;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 2. Hero Section -->
                    <div class="tab-pane fade" id="hero" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hero Title</label>
                                <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $homepage->hero_title) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hero Subtitle</label>
                                <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $homepage->hero_subtitle) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Hero Description</label>
                                <textarea name="hero_description" class="form-control" rows="4">{{ old('hero_description', $homepage->hero_description) }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hero CTA Text</label>
                                <input type="text" name="hero_cta_text" class="form-control" value="{{ old('hero_cta_text', $homepage->hero_cta_text) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hero CTA Link</label>
                                <input type="text" name="hero_cta_link" class="form-control" value="{{ old('hero_cta_link', $homepage->hero_cta_link) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Featured Image (Hero Background / Main Graphic)</label>
                                <input type="file" name="featured_image" class="form-control">
                                @if($homepage->featured_image)
                                    <img src="{{ asset($homepage->featured_image) }}" class="mt-2" style="max-height: 150px;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 3. Dynamic Sections -->
                    <div class="tab-pane fade" id="sections" role="tabpanel">
                        <p class="text-muted">Manage the dynamic sections that appear on the homepage.</p>
                        <div id="sections_container">
                            @foreach($sections as $index => $section)
                                <div class="card mb-3 section-row border-info">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Section #<span class="section-num">{{ $index + 1 }}</span></h6>
                                        <button type="button" class="btn btn-sm btn-danger remove-section"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Section Name</label>
                                                <input type="text" name="sections[{{ $index }}][section_name]" class="form-control" value="{{ $section->section_name }}" required>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="sections[{{ $index }}][title]" class="form-control" value="{{ $section->title }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Subtitle</label>
                                                <input type="text" name="sections[{{ $index }}][subtitle]" class="form-control" value="{{ $section->subtitle }}">
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <label class="form-label">Description</label>
                                                <textarea name="sections[{{ $index }}][description]" class="form-control" rows="2">{{ $section->description }}</textarea>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Button Text</label>
                                                <input type="text" name="sections[{{ $index }}][button_text]" class="form-control" value="{{ $section->button_text }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Button Link</label>
                                                <input type="text" name="sections[{{ $index }}][button_link]" class="form-control" value="{{ $section->button_link }}">
                                            </div>
                                            <div class="col-md-4 mb-2 pt-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="sections[{{ $index }}][status]" value="1" {{ $section->status ? 'checked' : '' }}>
                                                    <label class="form-check-label">Enabled</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2" id="add-section"><i class="fas fa-plus"></i> Add New Section</button>
                    </div>

                    <!-- 4. FAQs -->
                    <div class="tab-pane fade" id="faqs" role="tabpanel">
                        <p class="text-muted">These FAQs will automatically be included in the FAQPage Schema.</p>
                        <div id="faqs_container">
                            @foreach($faqs as $index => $faq)
                                <div class="card mb-3 faq-row">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="fw-bold">Question</label>
                                            <button type="button" class="btn btn-sm btn-danger remove-faq"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <input type="text" name="faqs[{{ $index }}][question]" class="form-control mb-2" value="{{ $faq->question }}">
                                        <label class="fw-bold">Answer</label>
                                        <textarea name="faqs[{{ $index }}][answer]" class="form-control" rows="3">{{ $faq->answer }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2" id="add-faq"><i class="fas fa-plus"></i> Add FAQ</button>
                    </div>

                    <!-- 5. Schema Blocks -->
                    <div class="tab-pane fade" id="schema" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Main Schema Type (e.g. WebSite)</label>
                                <input type="text" name="schema_type" class="form-control" value="{{ old('schema_type', $homepage->schema_type) }}">
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Custom Schema JSON (Global Injection)</label>
                                @php
                                    $customJsonStr = $homepage->custom_schema_json ? json_encode($homepage->custom_schema_json, JSON_PRETTY_PRINT) : '';
                                @endphp
                                <textarea name="custom_schema_json" class="form-control text-monospace" rows="6" placeholder="{&quot;@@context&quot;: &quot;https://schema.org&quot;}">{{ old('custom_schema_json', $customJsonStr) }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3 text-primary">Dynamic Schema Blocks</h5>
                        <div id="schema_blocks_container">
                            @foreach($schemaBlocks as $index => $block)
                                <div class="card mb-3 schema-block-row">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <div class="input-group w-50">
                                            <span class="input-group-text">Type</span>
                                            <input type="text" name="schema_blocks[{{ $index }}][schema_type]" class="form-control" value="{{ $block->schema_type }}" placeholder="e.g. VideoObject">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger remove-schema-block"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="card-body">
                                        <textarea name="schema_blocks[{{ $index }}][json_data]" class="form-control text-monospace mb-2" rows="5" placeholder="Valid JSON here">{{ json_encode($block->json_data, JSON_PRETTY_PRINT) }}</textarea>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="schema_blocks[{{ $index }}][status]" value="1" {{ $block->status ? 'checked' : '' }}>
                                            <label class="form-check-label">Inject this block</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2" id="add-schema-block"><i class="fas fa-plus"></i> Add Schema Block</button>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Save Homepage Settings</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Keywords ---
        const kwContainer = document.getElementById('secondary_keywords_container');
        document.getElementById('add-keyword').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'input-group mb-2 keyword-row';
            row.innerHTML = `<input type="text" name="secondary_keywords[]" class="form-control">
                             <button type="button" class="btn btn-outline-danger remove-keyword"><i class="fas fa-trash"></i></button>`;
            kwContainer.appendChild(row);
        });
        kwContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-keyword')) e.target.closest('.keyword-row').remove();
        });

        // --- Sections ---
        let sectionIndex = {{ count($sections) }};
        const secContainer = document.getElementById('sections_container');
        document.getElementById('add-section').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'card mb-3 section-row border-info';
            row.innerHTML = `
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">New Section</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-section"><i class="fas fa-times"></i></button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2"><label class="form-label">Section Name</label><input type="text" name="sections[${sectionIndex}][section_name]" class="form-control" required></div>
                        <div class="col-md-4 mb-2"><label class="form-label">Title</label><input type="text" name="sections[${sectionIndex}][title]" class="form-control"></div>
                        <div class="col-md-4 mb-2"><label class="form-label">Subtitle</label><input type="text" name="sections[${sectionIndex}][subtitle]" class="form-control"></div>
                        <div class="col-md-12 mb-2"><label class="form-label">Description</label><textarea name="sections[${sectionIndex}][description]" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-4 mb-2"><label class="form-label">Button Text</label><input type="text" name="sections[${sectionIndex}][button_text]" class="form-control"></div>
                        <div class="col-md-4 mb-2"><label class="form-label">Button Link</label><input type="text" name="sections[${sectionIndex}][button_link]" class="form-control"></div>
                        <div class="col-md-4 mb-2 pt-4">
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="sections[${sectionIndex}][status]" value="1" checked><label class="form-check-label">Enabled</label></div>
                        </div>
                    </div>
                </div>`;
            secContainer.appendChild(row);
            sectionIndex++;
        });
        secContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-section')) e.target.closest('.section-row').remove();
        });

        // --- FAQs ---
        let faqIndex = {{ count($faqs) }};
        const faqContainer = document.getElementById('faqs_container');
        document.getElementById('add-faq').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'card mb-3 faq-row';
            row.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-bold">Question</label>
                        <button type="button" class="btn btn-sm btn-danger remove-faq"><i class="fas fa-trash"></i></button>
                    </div>
                    <input type="text" name="faqs[${faqIndex}][question]" class="form-control mb-2">
                    <label class="fw-bold">Answer</label>
                    <textarea name="faqs[${faqIndex}][answer]" class="form-control" rows="3"></textarea>
                </div>`;
            faqContainer.appendChild(row);
            faqIndex++;
        });
        faqContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-faq')) e.target.closest('.faq-row').remove();
        });

        // --- Schema Blocks ---
        let schemaIndex = {{ count($schemaBlocks) }};
        const schemaContainer = document.getElementById('schema_blocks_container');
        document.getElementById('add-schema-block').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'card mb-3 schema-block-row';
            row.innerHTML = `
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div class="input-group w-50">
                        <span class="input-group-text">Type</span>
                        <input type="text" name="schema_blocks[${schemaIndex}][schema_type]" class="form-control" placeholder="e.g. VideoObject">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-schema-block"><i class="fas fa-trash"></i></button>
                </div>
                <div class="card-body">
                    <textarea name="schema_blocks[${schemaIndex}][json_data]" class="form-control text-monospace mb-2" rows="5" placeholder="Valid JSON here"></textarea>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="schema_blocks[${schemaIndex}][status]" value="1" checked><label class="form-check-label">Inject this block</label></div>
                </div>`;
            schemaContainer.appendChild(row);
            schemaIndex++;
        });
        schemaContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-schema-block')) e.target.closest('.schema-block-row').remove();
        });
    });
</script>
@endpush
