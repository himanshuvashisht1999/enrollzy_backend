@extends('admin.layouts.master')

@section('title', 'Global SEO Organization Settings')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Global SEO Organization Settings</h2>
        <span class="badge bg-primary fs-6"><i class="fas fa-sitemap me-1"></i> Schema.org Compatible</span>
    </div>

    <form action="{{ route('admin.seo_organization.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="seoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab"><i class="fas fa-building me-1"></i> General Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="founders-tab" data-bs-toggle="tab" href="#founders-pane" role="tab">
                            <i class="fas fa-users me-1"></i> Founders (<span id="founders-badge-count">{{ $founders->count() }}</span>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab"><i class="fas fa-map-marker-alt me-1"></i> Contact & Address</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="social-tab" data-bs-toggle="tab" href="#social" role="tab"><i class="fas fa-share-alt me-1"></i> Social Links</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="images-tab" data-bs-toggle="tab" href="#images" role="tab"><i class="fas fa-images me-1"></i> Logos & Images</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="seo-tab" data-bs-toggle="tab" href="#seo" role="tab"><i class="fas fa-robot me-1"></i> SEO Defaults & Robots</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="analytics-tab" data-bs-toggle="tab" href="#analytics" role="tab"><i class="fas fa-chart-line me-1"></i> Analytics & Verification</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="schema-tab" data-bs-toggle="tab" href="#schema" role="tab"><i class="fas fa-code me-1"></i> Schema & JSON-LD Preview</a>
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
                                <input type="date" name="founding_date" class="form-control" value="{{ old('founding_date', $setting->founding_date ? (is_string($setting->founding_date) ? $setting->founding_date : $setting->founding_date->format('Y-m-d')) : '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>Founders</span>
                                    <a href="#founders-pane" class="text-primary small text-decoration-none" onclick="document.getElementById('founders-tab').click();">+ Manage ({{ $founders->count() }})</a>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" readonly value="{{ $founders->pluck('name')->join(', ') ?: ($setting->founder_name ?: 'No founders added yet') }}">
                                    <button class="btn btn-outline-primary" type="button" onclick="document.getElementById('founders-tab').click();"><i class="fas fa-users me-1"></i> Edit Founders</button>
                                </div>
                                <small class="text-muted">Repeatable founders configured in the Founders tab for Schema.org.</small>
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

                    <!-- 2. Founders (Repeatable) -->
                    <div class="tab-pane fade" id="founders-pane" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1 text-primary"><i class="fas fa-users me-2"></i> Organization Founders</h5>
                                <p class="text-muted small mb-0">Add each founder as an individual record. Schema.org will automatically output each founder as a distinct <code>Person</code> structured data entity with their role, profile link, image, and social references.</p>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" id="btn-add-founder">
                                <i class="fas fa-plus me-1"></i> Add Founder
                            </button>
                        </div>

                        <div id="founders-container">
                            @forelse($founders as $index => $founder)
                            <div class="card border mb-3 founder-card" data-index="{{ $index }}">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-grip-vertical text-muted cursor-grab"></i>
                                        <strong class="text-dark founder-title-label">Founder #{{ $index + 1 }}: {{ $founder->name }}</strong>
                                        <span class="badge bg-secondary ms-2 founder-role-badge">{{ $founder->job_title ?: 'Founder' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="founders[{{ $index }}][is_active]" value="1" {{ $founder->is_active ? 'checked' : '' }} id="founder_active_{{ $index }}">
                                            <label class="form-check-label small" for="founder_active_{{ $index }}">Active</label>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-founder-btn" title="Remove Founder">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="founders[{{ $index }}][id]" value="{{ $founder->id }}">
                                    <input type="hidden" name="founders[{{ $index }}][sort_order]" value="{{ $founder->sort_order }}" class="founder-sort-order">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Full Name *</label>
                                            <input type="text" name="founders[{{ $index }}][name]" class="form-control founder-name-input" value="{{ old('founders.'.$index.'.name', $founder->name) }}" placeholder="e.g. Amit Sharma" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Job Title / Role</label>
                                            <input type="text" name="founders[{{ $index }}][job_title]" class="form-control" value="{{ old('founders.'.$index.'.job_title', $founder->job_title) }}" placeholder="e.g. Co-Founder & CEO">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Profile URL (Website / Bio page)</label>
                                            <input type="url" name="founders[{{ $index }}][profile_url]" class="form-control" value="{{ old('founders.'.$index.'.profile_url', $founder->profile_url) }}" placeholder="https://enrollzy.com/team/amit">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">LinkedIn Profile URL</label>
                                            <input type="url" name="founders[{{ $index }}][linkedin_url]" class="form-control" value="{{ old('founders.'.$index.'.linkedin_url', $founder->linkedin_url) }}" placeholder="https://www.linkedin.com/in/amit-founder">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Profile Photo / Image</label>
                                            <div class="d-flex align-items-center gap-3">
                                                @if($founder->image)
                                                <img src="{{ asset($founder->image) }}" alt="{{ $founder->name }}" class="rounded-circle border" style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                                <input type="file" name="founders[{{ $index }}][image_file]" class="form-control" accept="image/*">
                                            </div>
                                            <input type="hidden" name="founders[{{ $index }}][image_url]" value="{{ $founder->image }}">
                                            <small class="text-muted">Upload an image file or leave blank to keep current.</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Additional Profile URLs (SameAs)</label>
                                            <textarea name="founders[{{ $index }}][same_as]" class="form-control" rows="2" placeholder="https://twitter.com/amit, https://wikipedia.org/wiki/Amit">{{ is_array($founder->same_as) ? implode(', ', $founder->same_as) : $founder->same_as }}</textarea>
                                            <small class="text-muted">Separate multiple URLs with commas or new lines.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="card border mb-3 founder-card" data-index="0">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-grip-vertical text-muted cursor-grab"></i>
                                        <strong class="text-dark founder-title-label">Founder #1</strong>
                                        <span class="badge bg-secondary ms-2 founder-role-badge">Founder</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="founders[0][is_active]" value="1" checked id="founder_active_0">
                                            <label class="form-check-label small" for="founder_active_0">Active</label>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-founder-btn" title="Remove Founder">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="founders[0][sort_order]" value="0" class="founder-sort-order">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Full Name *</label>
                                            <input type="text" name="founders[0][name]" class="form-control founder-name-input" value="{{ old('founder_name', $setting->founder_name) }}" placeholder="e.g. Amit Sharma" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Job Title / Role</label>
                                            <input type="text" name="founders[0][job_title]" class="form-control" placeholder="e.g. Co-Founder & CEO">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Profile URL (Website / Bio page)</label>
                                            <input type="url" name="founders[0][profile_url]" class="form-control" placeholder="https://enrollzy.com/team/amit">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">LinkedIn Profile URL</label>
                                            <input type="url" name="founders[0][linkedin_url]" class="form-control" placeholder="https://www.linkedin.com/in/amit-founder">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Profile Photo / Image</label>
                                            <input type="file" name="founders[0][image_file]" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Additional Profile URLs (SameAs)</label>
                                            <textarea name="founders[0][same_as]" class="form-control" rows="2" placeholder="https://twitter.com/amit, https://wikipedia.org/wiki/Amit"></textarea>
                                            <small class="text-muted">Separate multiple URLs with commas or new lines.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforelse
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary" id="btn-add-founder-bottom">
                                <i class="fas fa-plus me-1"></i> Add Another Founder
                            </button>
                        </div>
                    </div>

                    <!-- 3. Contact & Address -->
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
                                <label class="form-label">Google Map Embed Code</label>
                                <textarea name="google_map_embed" class="form-control" rows="3">{{ old('google_map_embed', $setting->google_map_embed) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Social Links -->
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
                                <label class="form-label">Twitter / X URL</label>
                                <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $setting->twitter_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">YouTube URL</label>
                                <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $setting->youtube_url) }}">
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">SameAs Profile URLs</h5>
                            <div id="same_as_container">
                                @if(!empty($setting->same_as) && is_array($setting->same_as))
                                    @foreach($setting->same_as as $index => $url)
                                        <div class="input-group mb-2 same-as-row">
                                            <input type="url" name="same_as[]" class="form-control" value="{{ $url }}" placeholder="https://example.com">
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
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-same-as"><i class="fas fa-plus"></i> Add Profile URL</button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Logos & Images -->
                    <div class="tab-pane fade" id="images" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Default Logo</label>
                                <input type="file" name="logo" class="form-control mb-2">
                                @if($setting->logo)
                                    <img src="{{ asset($setting->logo) }}" alt="Logo" class="img-thumbnail" style="max-height: 80px;">
                                @endif
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">White Logo (For Dark Backgrounds)</label>
                                <input type="file" name="white_logo" class="form-control mb-2">
                                @if($setting->white_logo)
                                    <img src="{{ asset($setting->white_logo) }}" alt="White Logo" class="img-thumbnail bg-dark" style="max-height: 80px;">
                                @endif
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Dark Logo (For Light Backgrounds)</label>
                                <input type="file" name="dark_logo" class="form-control mb-2">
                                @if($setting->dark_logo)
                                    <img src="{{ asset($setting->dark_logo) }}" alt="Dark Logo" class="img-thumbnail" style="max-height: 80px;">
                                @endif
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Favicon</label>
                                <input type="file" name="favicon" class="form-control mb-2">
                                @if($setting->favicon)
                                    <img src="{{ asset($setting->favicon) }}" alt="Favicon" class="img-thumbnail" style="max-height: 32px;">
                                @endif
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Apple Touch Icon</label>
                                <input type="file" name="apple_touch_icon" class="form-control mb-2">
                                @if($setting->apple_touch_icon)
                                    <img src="{{ asset($setting->apple_touch_icon) }}" alt="Apple Touch Icon" class="img-thumbnail" style="max-height: 60px;">
                                @endif
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Open Graph (OG) Image</label>
                                <input type="file" name="og_image" class="form-control mb-2">
                                @if($setting->og_image)
                                    <img src="{{ asset($setting->og_image) }}" alt="OG Image" class="img-thumbnail" style="max-height: 100px;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 6. SEO Defaults & Robots -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <div class="row">
                            <h5 class="mb-3 text-primary">Default Social Share Tags</h5>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default OG Title</label>
                                <input type="text" name="default_og_title" class="form-control" value="{{ old('default_og_title', $setting->default_og_title) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default Twitter Title</label>
                                <input type="text" name="default_twitter_title" class="form-control" value="{{ old('default_twitter_title', $setting->default_twitter_title) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default OG Description</label>
                                <textarea name="default_og_description" class="form-control" rows="3">{{ old('default_og_description', $setting->default_og_description) }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default Twitter Description</label>
                                <textarea name="default_twitter_description" class="form-control" rows="3">{{ old('default_twitter_description', $setting->default_twitter_description) }}</textarea>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3 text-primary">Search Action URL</h5>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Target Search URL (For Google Sitelinks Searchbox)</label>
                                <input type="text" name="search_url" class="form-control" value="{{ old('search_url', $setting->search_url) }}" placeholder="https://example.com/search?q={search_term_string}">
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

                    <!-- 7. Analytics & Verification -->
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

                    <!-- 8. Schema Toggles & Live Preview -->
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
                                    'organization_schema' => 'Organization Schema (with Repeatable Founders)',
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

                            <hr class="my-4">
                            <div class="col-md-12">
                                <h5 class="text-primary mb-2"><i class="fas fa-eye me-1"></i> Live Organization Schema.org Preview (JSON-LD)</h5>
                                <p class="text-muted small">This is the live JSON-LD Structured Data that will be generated for search engines based on your settings and founders above:</p>
                                @php
                                    $schemaPreview = $setting->generateOrganizationSchema() ?? [
                                        '@context' => 'https://schema.org',
                                        '@type' => $setting->organization_type ?: 'Organization',
                                        'name' => $setting->organization_name ?: 'Enrollzy',
                                        'founder' => $founders->map(fn($f) => [
                                            '@type' => 'Person',
                                            'name' => $f->name,
                                            'jobTitle' => $f->job_title,
                                            'url' => $f->profile_url,
                                            'sameAs' => $f->linkedin_url,
                                        ])->values()
                                    ];
                                @endphp
                                <pre class="bg-dark text-light p-3 rounded" style="max-height: 380px; overflow-y: auto; font-size: 13px;"><code>{{ json_encode($schemaPreview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
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

<!-- Template for adding new founder dynamically -->
<template id="founder-template">
    <div class="card border mb-3 founder-card" data-index="__INDEX__">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-grip-vertical text-muted cursor-grab"></i>
                <strong class="text-dark founder-title-label">Founder #__NUM__</strong>
                <span class="badge bg-secondary ms-2 founder-role-badge">Founder</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="founders[__INDEX__][is_active]" value="1" checked id="founder_active___INDEX__">
                    <label class="form-check-label small" for="founder_active___INDEX__">Active</label>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-founder-btn" title="Remove Founder">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" name="founders[__INDEX__][sort_order]" value="__INDEX__" class="founder-sort-order">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Full Name *</label>
                    <input type="text" name="founders[__INDEX__][name]" class="form-control founder-name-input" placeholder="e.g. Rahul Sharma" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Job Title / Role</label>
                    <input type="text" name="founders[__INDEX__][job_title]" class="form-control" placeholder="e.g. Co-Founder & CTO">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Profile URL (Website / Bio page)</label>
                    <input type="url" name="founders[__INDEX__][profile_url]" class="form-control" placeholder="https://enrollzy.com/team/rahul">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">LinkedIn Profile URL</label>
                    <input type="url" name="founders[__INDEX__][linkedin_url]" class="form-control" placeholder="https://www.linkedin.com/in/rahul-founder">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Profile Photo / Image</label>
                    <input type="file" name="founders[__INDEX__][image_file]" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Additional Profile URLs (SameAs)</label>
                    <textarea name="founders[__INDEX__][same_as]" class="form-control" rows="2" placeholder="https://twitter.com/rahul, https://crunchbase.com/person/rahul"></textarea>
                    <small class="text-muted">Separate multiple URLs with commas or new lines.</small>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // SameAs Container Logic
        const sameAsContainer = document.getElementById('same_as_container');
        const addSameAsButton = document.getElementById('add-same-as');

        if (addSameAsButton && sameAsContainer) {
            addSameAsButton.addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.className = 'input-group mb-2 same-as-row';
                newRow.innerHTML = `
                    <input type="url" name="same_as[]" class="form-control" placeholder="https://example.com">
                    <button type="button" class="btn btn-outline-danger remove-same-as"><i class="fas fa-trash"></i></button>
                `;
                sameAsContainer.appendChild(newRow);
            });

            sameAsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-same-as')) {
                    const row = e.target.closest('.same-as-row');
                    if (sameAsContainer.querySelectorAll('.same-as-row').length > 1) {
                        row.remove();
                    } else {
                        row.querySelector('input').value = '';
                    }
                }
            });
        }

        // Founders Repeater Logic
        const foundersContainer = document.getElementById('founders-container');
        const founderTemplate = document.getElementById('founder-template');
        const btnAddFounder = document.getElementById('btn-add-founder');
        const btnAddFounderBottom = document.getElementById('btn-add-founder-bottom');
        const badgeCount = document.getElementById('founders-badge-count');

        function updateFounderIndices() {
            const cards = foundersContainer.querySelectorAll('.founder-card');
            cards.forEach((card, index) => {
                const num = index + 1;
                const nameInput = card.querySelector('.founder-name-input');
                const nameVal = nameInput ? nameInput.value.trim() : '';
                const label = card.querySelector('.founder-title-label');
                if (label) {
                    label.textContent = `Founder #${num}` + (nameVal ? `: ${nameVal}` : '');
                }
                const sortInput = card.querySelector('.founder-sort-order');
                if (sortInput) {
                    sortInput.value = index;
                }
            });
            if (badgeCount) {
                badgeCount.textContent = cards.length;
            }
        }

        function addFounderCard() {
            const currentCount = foundersContainer.querySelectorAll('.founder-card').length;
            let html = founderTemplate.innerHTML
                .replaceAll('__INDEX__', currentCount)
                .replaceAll('__NUM__', currentCount + 1);
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html.trim();
            const newCard = tempDiv.firstElementChild;
            foundersContainer.appendChild(newCard);
            updateFounderIndices();

            // Focus on new name input
            const newNameInput = newCard.querySelector('.founder-name-input');
            if (newNameInput) newNameInput.focus();
        }

        if (btnAddFounder) btnAddFounder.addEventListener('click', addFounderCard);
        if (btnAddFounderBottom) btnAddFounderBottom.addEventListener('click', addFounderCard);

        if (foundersContainer) {
            foundersContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-founder-btn')) {
                    const card = e.target.closest('.founder-card');
                    if (foundersContainer.querySelectorAll('.founder-card').length > 1) {
                        card.remove();
                        updateFounderIndices();
                    } else {
                        // Clear inputs instead of deleting only card
                        card.querySelectorAll('input:not([type="checkbox"]):not([type="hidden"]), textarea').forEach(inp => inp.value = '');
                        updateFounderIndices();
                    }
                }
            });

            foundersContainer.addEventListener('input', function(e) {
                if (e.target.classList.contains('founder-name-input')) {
                    const card = e.target.closest('.founder-card');
                    const index = Array.from(foundersContainer.children).indexOf(card) + 1;
                    const val = e.target.value.trim();
                    const label = card.querySelector('.founder-title-label');
                    if (label) {
                        label.textContent = `Founder #${index}` + (val ? `: ${val}` : '');
                    }
                }
            });
        }

        // Schema Toggles Logic
        const globalSchemaToggle = document.getElementById('schema_enabled');
        const individualSchemaToggles = document.querySelectorAll('#schema input[type="checkbox"]:not(#schema_enabled)');

        if (globalSchemaToggle) {
            individualSchemaToggles.forEach(toggle => {
                toggle.disabled = !globalSchemaToggle.checked;
            });

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