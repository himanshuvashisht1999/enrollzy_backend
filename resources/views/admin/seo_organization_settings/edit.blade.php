@extends('admin.layouts.master')

@section('title', 'Global SEO Organization Settings')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Global SEO Organization Settings</h2>
    </div>


    <form action="{{ route('admin.seo_organization.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="seoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">General Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab">Contact & Address</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="social-tab" data-bs-toggle="tab" href="#social" role="tab">Social Links</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="images-tab" data-bs-toggle="tab" href="#images" role="tab">Logos & Images</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="seo-tab" data-bs-toggle="tab" href="#seo" role="tab">SEO Defaults & Robots</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="analytics-tab" data-bs-toggle="tab" href="#analytics" role="tab">Analytics & Verification</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="schema-tab" data-bs-toggle="tab" href="#schema" role="tab">Schema Toggles</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="seoTabsContent">
                    
                    <!-- 1. General Info -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Name</label>
                                <input type="text" name="organization_name" class="form-control" value="{{ old('organization_name', $setting->organization_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Legal Name</label>
                                <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name', $setting->legal_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alternate Name</label>
                                <input type="text" name="alternate_name" class="form-control" value="{{ old('alternate_name', $setting->alternate_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control" value="{{ old('website', $setting->website) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Founding Date</label>
                                <input type="date" name="founding_date" class="form-control" value="{{ old('founding_date', $setting->founding_date ? $setting->founding_date->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Founder Name</label>
                                <input type="text" name="founder_name" class="form-control" value="{{ old('founder_name', $setting->founder_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Type</label>
                                <select name="organization_type" class="form-select">
                                    @foreach(['Organization', 'EducationalOrganization', 'CollegeOrUniversity', 'LocalBusiness', 'Corporation', 'NGO'] as $type)
                                        <option value="{{ $type }}" {{ old('organization_type', $setting->organization_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Price Range</label>
                                <input type="text" name="price_range" class="form-control" value="{{ old('price_range', $setting->price_range) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Default Currency</label>
                                <input type="text" name="default_currency" class="form-control" value="{{ old('default_currency', $setting->default_currency) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Copyright Text</label>
                                <input type="text" name="copyright_text" class="form-control" value="{{ old('copyright_text', $setting->copyright_text) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Copyright Year</label>
                                <input type="text" name="copyright_year" class="form-control" value="{{ old('copyright_year', $setting->copyright_year) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" class="form-control" rows="3">{{ old('short_description', $setting->short_description) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Long Description</label>
                                <textarea name="long_description" class="form-control" rows="5">{{ old('long_description', $setting->long_description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Contact & Address -->
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <div class="row">
                            <h5 class="mb-3 text-primary">Contact Info</h5>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Support Email</label>
                                <input type="email" name="support_email" class="form-control" value="{{ old('support_email', $setting->support_email) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $setting->whatsapp_number) }}">
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Address & Location</h5>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" name="address_line_1" class="form-control" value="{{ old('address_line_1', $setting->address_line_1) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address_line_2" class="form-control" value="{{ old('address_line_2', $setting->address_line_2) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $setting->city) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="{{ old('state', $setting->state) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $setting->country) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $setting->postal_code) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $setting->latitude) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $setting->longitude) }}">
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Misc</h5>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax Number</label>
                                <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $setting->tax_number) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GST Number</label>
                                <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $setting->gst_number) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Opening Hours</label>
                                <textarea name="opening_hours" class="form-control" rows="3">{{ old('opening_hours', $setting->opening_hours) }}</textarea>
                                <small class="text-muted">E.g., Mo-Fr 09:00-17:00</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Google Map Embed (Iframe)</label>
                                <textarea name="google_map_embed" class="form-control" rows="3">{{ old('google_map_embed', $setting->google_map_embed) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Social Links -->
                    <div class="tab-pane fade" id="social" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $setting->facebook_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instagram URL</label>
                                <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $setting->instagram_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $setting->linkedin_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Twitter URL</label>
                                <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $setting->twitter_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">YouTube URL</label>
                                <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $setting->youtube_url) }}">
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Other Knowledge Graph Links (same_as)</h5>
                            <div class="col-12" id="same_as_container">
                                @if($setting->same_as && is_array($setting->same_as) && count($setting->same_as) > 0)
                                    @foreach($setting->same_as as $link)
                                    <div class="input-group mb-2 same-as-row">
                                        <input type="url" name="same_as[]" class="form-control" value="{{ $link }}" placeholder="https://example.com">
                                        <button type="button" class="btn btn-outline-danger remove-same-as"><i class="fas fa-trash"></i></button>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 same-as-row">
                                        <input type="url" name="same_as[]" class="form-control" placeholder="https://example.com">
                                        <button type="button" class="btn btn-outline-danger remove-same-as"><i class="fas fa-trash"></i></button>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12 mt-2">
                                <button type="button" class="btn btn-sm btn-secondary" id="add-same-as"><i class="fas fa-plus"></i> Add Link</button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Logos & Images -->
                    <div class="tab-pane fade" id="images" role="tabpanel">
                        <div class="row">
                            @php
                                $imageFields = [
                                    'logo' => 'Logo',
                                    'white_logo' => 'White Logo',
                                    'dark_logo' => 'Dark Logo',
                                    'favicon' => 'Favicon',
                                    'apple_touch_icon' => 'Apple Touch Icon',
                                    'og_image' => 'General OG Image',
                                    'default_og_image' => 'Default OG Sharing Image',
                                    'default_twitter_image' => 'Default Twitter Sharing Image',
                                ];
                            @endphp

                            @foreach($imageFields as $field => $label)
                            <div class="col-md-6 mb-4">
                                <div class="card bg-light h-100">
                                    <div class="card-body">
                                        <label class="form-label fw-bold">{{ $label }}</label>
                                        <input type="file" name="{{ $field }}" class="form-control mb-2" accept="image/*">
                                        @if($setting->$field)
                                            <div class="mt-2 text-center bg-white p-2 border rounded">
                                                <img src="{{ asset($setting->$field) }}" alt="{{ $label }}" style="max-height: 100px; max-width: 100%; object-fit: contain;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 5. SEO Defaults & Robots -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <div class="row">
                            <h5 class="mb-3 text-primary">Search Action Settings</h5>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Search URL Template</label>
                                <input type="text" name="search_url" class="form-control" value="{{ old('search_url', $setting->search_url) }}" placeholder="https://yoursite.com/search?q={search_term_string}">
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Open Graph (Facebook/LinkedIn) Defaults</h5>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Default OG Title</label>
                                <input type="text" name="default_og_title" class="form-control" value="{{ old('default_og_title', $setting->default_og_title) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Default OG Description</label>
                                <textarea name="default_og_description" class="form-control" rows="3">{{ old('default_og_description', $setting->default_og_description) }}</textarea>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Twitter Card Defaults</h5>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Default Twitter Title</label>
                                <input type="text" name="default_twitter_title" class="form-control" value="{{ old('default_twitter_title', $setting->default_twitter_title) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Default Twitter Description</label>
                                <textarea name="default_twitter_description" class="form-control" rows="3">{{ old('default_twitter_description', $setting->default_twitter_description) }}</textarea>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Robots & Sitemap Settings</h5>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Default Robots</label>
                                <input type="text" name="default_robots" class="form-control" value="{{ old('default_robots', $setting->default_robots) }}" placeholder="index, follow">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Default Sitemap Priority</label>
                                <input type="text" name="default_sitemap_priority" class="form-control" value="{{ old('default_sitemap_priority', $setting->default_sitemap_priority) }}" placeholder="0.8">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Default Change Frequency</label>
                                <input type="text" name="default_change_frequency" class="form-control" value="{{ old('default_change_frequency', $setting->default_change_frequency) }}" placeholder="weekly">
                            </div>
                        </div>
                    </div>

                    <!-- 6. Analytics & Verification -->
                    <div class="tab-pane fade" id="analytics" role="tabpanel">
                        <div class="row">
                            <h5 class="mb-3 text-primary">Analytics Trackers</h5>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GA4 ID</label>
                                <input type="text" name="ga4_id" class="form-control" value="{{ old('ga4_id', $setting->ga4_id) }}" placeholder="G-XXXXXXXXXX">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GTM ID</label>
                                <input type="text" name="gtm_id" class="form-control" value="{{ old('gtm_id', $setting->gtm_id) }}" placeholder="GTM-XXXXXXX">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Meta Pixel ID</label>
                                <input type="text" name="meta_pixel_id" class="form-control" value="{{ old('meta_pixel_id', $setting->meta_pixel_id) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LinkedIn Insight Tag</label>
                                <input type="text" name="linkedin_insight_tag" class="form-control" value="{{ old('linkedin_insight_tag', $setting->linkedin_insight_tag) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Microsoft Clarity ID</label>
                                <input type="text" name="clarity_id" class="form-control" value="{{ old('clarity_id', $setting->clarity_id) }}">
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Site Verifications</h5>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Google Site Verification</label>
                                <input type="text" name="google_site_verification" class="form-control" value="{{ old('google_site_verification', $setting->google_site_verification) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bing Site Verification</label>
                                <input type="text" name="bing_site_verification" class="form-control" value="{{ old('bing_site_verification', $setting->bing_site_verification) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Yandex Verification</label>
                                <input type="text" name="yandex_verification" class="form-control" value="{{ old('yandex_verification', $setting->yandex_verification) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pinterest Verification</label>
                                <input type="text" name="pinterest_verification" class="form-control" value="{{ old('pinterest_verification', $setting->pinterest_verification) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook Domain Verification</label>
                                <input type="text" name="facebook_domain_verification" class="form-control" value="{{ old('facebook_domain_verification', $setting->facebook_domain_verification) }}">
                            </div>
                        </div>
                    </div>

                    <!-- 7. Schema Toggles -->
                    <div class="tab-pane fade" id="schema" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" name="schema_enabled" id="schema_enabled" value="1" {{ old('schema_enabled', $setting->schema_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="schema_enabled">Enable All Schema Generation</label>
                                </div>
                                <small class="text-muted ms-5">Global switch to turn on/off schema injections.</small>
                            </div>

                            <hr>

                            @php
                                $schemaToggles = [
                                    'organization_schema' => 'Organization Schema',
                                    'search_action_schema' => 'Search Action Schema',
                                    'website_schema' => 'Website Schema',
                                    'breadcrumb_schema' => 'Breadcrumb Schema',
                                    'logo_schema' => 'Logo Schema',
                                    'social_profile_schema' => 'Social Profile Schema',
                                ];
                            @endphp

                            @foreach($schemaToggles as $field => $label)
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}" value="1" {{ old($field, $setting->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Save Settings</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('same_as_container');
        const addButton = document.getElementById('add-same-as');

        addButton.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'input-group mb-2 same-as-row';
            newRow.innerHTML = `
                <input type="url" name="same_as[]" class="form-control" placeholder="https://example.com">
                <button type="button" class="btn btn-outline-danger remove-same-as"><i class="fas fa-trash"></i></button>
            `;
            container.appendChild(newRow);
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-same-as')) {
                const row = e.target.closest('.same-as-row');
                // Don't remove if it's the only one
                if (container.querySelectorAll('.same-as-row').length > 1) {
                    row.remove();
                } else {
                    row.querySelector('input').value = '';
                }
            }
        });

        // Schema Toggles Logic
        const globalSchemaToggle = document.getElementById('schema_enabled');
        const individualSchemaToggles = document.querySelectorAll('#schema input[type="checkbox"]:not(#schema_enabled)');

        if (globalSchemaToggle) {
            // Set initial state
            individualSchemaToggles.forEach(toggle => {
                toggle.disabled = !globalSchemaToggle.checked;
            });

            // Handle change event
            globalSchemaToggle.addEventListener('change', function() {
                const isEnabled = this.checked;
                individualSchemaToggles.forEach(toggle => {
                    toggle.checked = isEnabled;
                    toggle.disabled = !isEnabled;
                });
            });
        }
    });
</script>
@endpush
