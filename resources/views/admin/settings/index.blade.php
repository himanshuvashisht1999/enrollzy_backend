@extends('admin.layouts.master')

@section('title', 'General Settings')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold">General Website Settings</h3>
    <p class="text-muted">Manage your website's branding, SEO, and contact information.</p>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('POST')
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="settingsTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-3 px-4 border-0 rounded-0" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab">
                                <i class="fas fa-image me-2"></i>Branding
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 rounded-0" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                                <i class="fas fa-search me-2"></i>SEO Meta Tags
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 rounded-0" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                <i class="fas fa-address-book me-2"></i>Contact & Footer
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 rounded-0" id="hero-tab" data-bs-toggle="tab" data-bs-target="#hero" type="button" role="tab">
                                <i class="fas fa-rocket me-2"></i>Hero Section
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- Branding Tab -->
                        <div class="tab-pane fade show active" id="branding" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Site Name</label>
                                    <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $setting->site_name) }}" placeholder="e.g. My Awesome College">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Website Logo</label>
                                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
                                    <div class="form-text">Recommended size: 200x50px. Max 2MB.</div>
                                    @if($setting->logo)
                                        <div class="mt-3 p-2 border rounded bg-light text-center">
                                            <img src="{{ asset($setting->logo) }}" alt="Logo" style="max-height: 50px;">
                                        </div>
                                    @endif
                                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Favicon (Browser Icon)</label>
                                    <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror">
                                    <div class="form-text">Recommended: 32x32px .ico or .png.</div>
                                    @if($setting->favicon)
                                        <div class="mt-3 p-2 border rounded bg-light text-center">
                                            <img src="{{ asset($setting->favicon) }}" alt="Favicon" style="height: 32px;">
                                        </div>
                                    @endif
                                    @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SEO Tab -->
                        <div class="tab-pane fade" id="seo" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Meta Title (Global)</label>
                                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $setting->meta_title) }}" placeholder="e.g. Best Online Degrees & Education">
                                    <div class="form-text">This will be the default page title.</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="3" placeholder="Brief description of your website for search engines...">{{ old('meta_description', $setting->meta_description) }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Meta Keywords</label>
                                    <textarea name="meta_keywords" class="form-control" rows="2" placeholder="e.g. college, education, online degrees, university">{{ old('meta_keywords', $setting->meta_keywords) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Contact Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $setting->contact_email) }}" placeholder="info@college.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Contact Phone</label>
                                    <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $setting->contact_phone) }}" placeholder="+91 98765 43210">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Office Address</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Street name, City, State, ZIP">{{ old('address', $setting->address) }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Footer Copyright Text</label>
                                    <input type="text" name="footer_text" class="form-control" value="{{ old('footer_text', $setting->footer_text) }}" placeholder="© 2025 College Name. All rights reserved.">
                                </div>
                            </div>
                        </div>

                        <!-- Hero Section Tab -->
                        <div class="tab-pane fade" id="hero" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Hero Title</label>
                                    <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $setting->hero_title) }}" placeholder="e.g. Welcome to ABC College">
                                    <div class="form-text">Tip: Use <code>&lt;span class="theme"&gt;Text&lt;/span&gt;</code> for colored text.</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Hero Description</label>
                                    <textarea name="hero_description" class="form-control" rows="3">{{ old('hero_description', $setting->hero_description) }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Hero Features (One per line)</label>
                                    <textarea name="hero_features" class="form-control" rows="4" placeholder="NAAC Accredited Institution&#10;50+ Programs">{{ old('hero_features', $setting->hero_features) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">CTA 1 Text</label>
                                    <input type="text" name="hero_cta_1_text" class="form-control" value="{{ old('hero_cta_1_text', $setting->hero_cta_1_text) }}" placeholder="Apply Now">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">CTA 1 Link</label>
                                    <input type="text" name="hero_cta_1_link" class="form-control" value="{{ old('hero_cta_1_link', $setting->hero_cta_1_link) }}" placeholder="/apply">
                                    <div class="form-check form-switch mt-2">
                                        <input type="hidden" name="hero_cta_1_new_tab" value="0">
                                        <input class="form-check-input" type="checkbox" name="hero_cta_1_new_tab" id="hero_cta_1_new_tab" value="1" {{ $setting->hero_cta_1_new_tab ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="hero_cta_1_new_tab">Open in New Tab</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">CTA 2 Text</label>
                                    <input type="text" name="hero_cta_2_text" class="form-control" value="{{ old('hero_cta_2_text', $setting->hero_cta_2_text) }}" placeholder="Explore Courses">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">CTA 2 Link</label>
                                    <input type="text" name="hero_cta_2_link" class="form-control" value="{{ old('hero_cta_2_link', $setting->hero_cta_2_link) }}" placeholder="/courses">
                                    <div class="form-check form-switch mt-2">
                                        <input type="hidden" name="hero_cta_2_new_tab" value="0">
                                        <input class="form-check-input" type="checkbox" name="hero_cta_2_new_tab" id="hero_cta_2_new_tab" value="1" {{ $setting->hero_cta_2_new_tab ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="hero_cta_2_new_tab">Open in New Tab</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-5 py-2">
                            <i class="fas fa-save me-2"></i> Save All Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('css')
<style>
    .nav-tabs .nav-link { color: #666; font-weight: 500; }
    .nav-tabs .nav-link.active { color: var(--admin-accent); background-color: #fff !important; border-bottom: 3px solid var(--admin-accent) !important; }
    .card-header-tabs { border-bottom: 1px solid #eee; }
</style>
@endpush
@endsection
