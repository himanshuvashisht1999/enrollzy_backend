<div class="row g-4">
    <!-- Left Column: Form Settings (Tabs) -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <ul class="nav nav-pills card-header-pills" id="cardFormTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" id="tab-basic-btn" data-bs-toggle="pill" data-bs-target="#tab-basic" type="button" role="tab">
                            <i class="fas fa-user me-1"></i> Profile
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="tab-media-btn" data-bs-toggle="pill" data-bs-target="#tab-media" type="button" role="tab">
                            <i class="fas fa-image me-1"></i> Photos & Themes
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="tab-companies-btn" data-bs-toggle="pill" data-bs-target="#tab-companies" type="button" role="tab">
                            <i class="fas fa-briefcase me-1"></i> Sub-Brands
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="tab-contact-btn" data-bs-toggle="pill" data-bs-target="#tab-contact" type="button" role="tab">
                            <i class="fas fa-phone-alt me-1"></i> Contact
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="tab-social-btn" data-bs-toggle="pill" data-bs-target="#tab-social" type="button" role="tab">
                            <i class="fas fa-share-alt me-1"></i> Socials
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="tab-seo-btn" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab">
                            <i class="fas fa-cog me-1"></i> SEO
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="cardFormTabContent">
                    
                    <!-- TAB 1: BASIC PROFILE -->
                    <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-user-circle text-primary me-2"></i>Personal & Professional Info</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Card Title / Reference Name</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Amit Singh - Founder Executive Card" value="{{ old('title', $card->title) }}">
                                <div class="form-text small">Internal reference name for this card.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="input_name" class="form-control" placeholder="e.g. Amit Singh" value="{{ old('name', $card->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Designation / Headline</label>
                                <input type="text" name="designation" id="input_designation" class="form-control" placeholder="e.g. Founder, CEO & Managing Director" value="{{ old('designation', $card->designation) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Primary Company / Organization</label>
                                <input type="text" name="company_name" id="input_company" class="form-control" placeholder="e.g. Enrollzy" value="{{ old('company_name', $card->company_name) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Card URL Slug</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small">/card/</span>
                                    <input type="text" name="slug" id="input_slug" class="form-control" placeholder="amit-singh" value="{{ old('slug', $card->slug) }}">
                                </div>
                                <div class="form-text small">Unique public URL slug (e.g. <code>amit-singh</code>).</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Top Header Slogan Tags (3 Pillars)</label>
                                <div class="row g-2">
                                    @php
                                        $tags = is_array($card->slogan_tags) ? $card->slogan_tags : ['PEOPLE', 'TECHNOLOGY', 'GROWTH'];
                                    @endphp
                                    <div class="col-4">
                                        <input type="text" name="slogan_tags[]" class="form-control slogan-tag-input text-uppercase fw-bold text-center" placeholder="Tag 1" value="{{ old('slogan_tags.0', $tags[0] ?? 'PEOPLE') }}">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" name="slogan_tags[]" class="form-control slogan-tag-input text-uppercase fw-bold text-center" placeholder="Tag 2" value="{{ old('slogan_tags.1', $tags[1] ?? 'TECHNOLOGY') }}">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" name="slogan_tags[]" class="form-control slogan-tag-input text-uppercase fw-bold text-center" placeholder="Tag 3" value="{{ old('slogan_tags.2', $tags[2] ?? 'GROWTH') }}">
                                    </div>
                                </div>
                                <div class="form-text small">Displayed on top-left of the card header (as in the mockup).</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Bio / Vision Statement Quote</label>
                                <textarea name="bio" id="input_bio" class="form-control" rows="3" placeholder="e.g. Empowering Education, Technology and Digital Growth through Innovative Solutions.">{{ old('bio', $card->bio) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: PHOTOS & THEMES -->
                    <div class="tab-pane fade" id="tab-media" role="tabpanel">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-palette text-primary me-2"></i>Profile Photo, Banner & Theme Colors</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Profile Photo / Avatar</label>
                                <input type="file" name="profile_image" id="input_profile_image" class="form-control" accept="image/*">
                                @if($card->profile_image)
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <img src="{{ asset($card->profile_image) }}" alt="Current Photo" class="rounded-circle border" width="50" height="50">
                                        <span class="small text-muted">Current profile photo</span>
                                    </div>
                                @endif
                                <div class="form-text small">Square ratio recommended (e.g. 500x500px).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Header Banner Backdrop</label>
                                <input type="file" name="banner_image" id="input_banner_image" class="form-control" accept="image/*">
                                @if($card->banner_image)
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <img src="{{ asset($card->banner_image) }}" alt="Current Banner" class="rounded border" width="80" height="45" style="object-fit: cover;">
                                        <span class="small text-muted">Current banner</span>
                                    </div>
                                @endif
                                <div class="form-text small">Landscape city / office backdrop (e.g. 1200x600px).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Primary Theme Color</label>
                                <div class="input-group">
                                    <input type="color" name="theme_color" id="input_theme_color" class="form-control form-control-color" value="{{ old('theme_color', $card->theme_color ?: '#0f172a') }}">
                                    <input type="text" class="form-control" value="{{ old('theme_color', $card->theme_color ?: '#0f172a') }}" onchange="document.getElementById('input_theme_color').value = this.value">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Accent Gold / Highlight Color</label>
                                <div class="input-group">
                                    <input type="color" name="accent_color" id="input_accent_color" class="form-control form-control-color" value="{{ old('accent_color', $card->accent_color ?: '#c59b27') }}">
                                    <input type="text" class="form-control" value="{{ old('accent_color', $card->accent_color ?: '#c59b27') }}" onchange="document.getElementById('input_accent_color').value = this.value">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Custom QR Code Image (Optional)</label>
                                <input type="file" name="qr_code_custom_image" class="form-control" accept="image/*">
                                <div class="form-text small">Leave blank to auto-generate crisp dynamic QR code.</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: ASSOCIATED BUSINESSES / SUB-BRANDS -->
                    <div class="tab-pane fade" id="tab-companies" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-sitemap text-primary me-2"></i>Associated Businesses / Brands</h6>
                                <p class="text-muted small mb-0">Add up to 3 or more companies associated with this profile (like Amit Book Depot, TheHashTech, Enrollzy).</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-3" onclick="addCompanyRow()">
                                <i class="fas fa-plus me-1"></i> Add Brand
                            </button>
                        </div>

                        <div id="companies_container" class="d-flex flex-column gap-3">
                            @php
                                $companiesList = old('companies', $card->companies ?? [
                                    ['name' => 'Amit Book Depot', 'subtitle' => 'Books | Stationery | Education', 'icon' => 'fas fa-book-open', 'icon_color' => '#dc2626', 'link' => ''],
                                    ['name' => 'TheHashTech', 'subtitle' => 'IT Solutions | Digital Growth', 'icon' => 'fas fa-hashtag', 'icon_color' => '#2563eb', 'link' => ''],
                                    ['name' => 'Enrollzy', 'subtitle' => 'Education | Admissions | Careers', 'icon' => 'fas fa-graduation-cap', 'icon_color' => '#059669', 'link' => '']
                                ]);
                            @endphp

                            @foreach($companiesList as $idx => $co)
                                <div class="company-row-item card bg-light border p-3 rounded-3 position-relative" data-idx="{{ $idx }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark small"><i class="fas fa-store me-1 text-secondary"></i> Brand #<span class="row-num">{{ $loop->iteration }}</span></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeCompanyRow(this)"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Brand Name</label>
                                            <input type="text" name="companies[{{ $idx }}][name]" class="form-control form-control-sm co-name-input" placeholder="e.g. Amit Book Depot" value="{{ $co['name'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Subtitle / Services</label>
                                            <input type="text" name="companies[{{ $idx }}][subtitle]" class="form-control form-control-sm co-sub-input" placeholder="e.g. Books | Stationery | Education" value="{{ $co['subtitle'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Icon Class</label>
                                            <input type="text" name="companies[{{ $idx }}][icon]" class="form-control form-control-sm co-icon-input" placeholder="e.g. fas fa-book-open" value="{{ $co['icon'] ?? 'fas fa-building' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Icon / Accent Color</label>
                                            <input type="color" name="companies[{{ $idx }}][icon_color]" class="form-control form-control-color form-control-sm w-100" value="{{ $co['icon_color'] ?? '#c59b27' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Website / Link (Optional)</label>
                                            <input type="url" name="companies[{{ $idx }}][link]" class="form-control form-control-sm" placeholder="https://..." value="{{ $co['link'] ?? '' }}">
                                        </div>
                                        @if(!empty($co['logo']))
                                            <input type="hidden" name="companies[{{ $idx }}][existing_logo]" value="{{ $co['logo'] }}">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- TAB 4: CONTACT CHANNELS -->
                    <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-address-card text-primary me-2"></i>Quick Action Buttons (Call, WhatsApp, Email, Maps)</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fas fa-phone-alt text-success me-1"></i> Phone / Mobile Number (Call Now)</label>
                                <input type="text" name="phone" id="input_phone" class="form-control" placeholder="+91 98765 43210" value="{{ old('phone', $card->phone) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Number</label>
                                <input type="text" name="whatsapp_number" id="input_whatsapp" class="form-control" placeholder="+91 98765 43210" value="{{ old('whatsapp_number', $card->whatsapp_number) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">WhatsApp Prefilled Message (Optional)</label>
                                <input type="text" name="whatsapp_message" class="form-control" placeholder="Hi, I saw your digital card and would like to connect!" value="{{ old('whatsapp_message', $card->whatsapp_message) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fas fa-envelope text-danger me-1"></i> Email Address</label>
                                <input type="email" name="email" id="input_email" class="form-control" placeholder="amit@example.com" value="{{ old('email', $card->email) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fas fa-globe text-primary me-1"></i> Primary Website URL</label>
                                <input type="url" name="website_url" class="form-control" placeholder="https://enrollzy.com" value="{{ old('website_url', $card->website_url) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold"><i class="fas fa-map-marker-alt text-info me-1"></i> Office / Business Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="e.g. SCO 210-211, Sector 34A, Chandigarh, India">{{ old('address', $card->address) }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold"><i class="fas fa-location-arrow text-primary me-1"></i> Google Maps Link (Get Directions)</label>
                                <input type="url" name="maps_url" class="form-control" placeholder="https://maps.google.com/?q=..." value="{{ old('maps_url', $card->maps_url) }}">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: SOCIAL MEDIA PROFILES -->
                    <div class="tab-pane fade" id="tab-social" role="tabpanel">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-hashtag text-primary me-2"></i>Social Profiles (Only filled links appear on card)</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-linkedin text-primary me-1"></i> LinkedIn Profile</label>
                                <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/username" value="{{ old('linkedin_url', $card->linkedin_url) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-instagram text-danger me-1"></i> Instagram Profile</label>
                                <input type="url" name="instagram_url" class="form-control" placeholder="https://instagram.com/username" value="{{ old('instagram_url', $card->instagram_url) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-facebook text-primary me-1"></i> Facebook Page / Profile</label>
                                <input type="url" name="facebook_url" class="form-control" placeholder="https://facebook.com/username" value="{{ old('facebook_url', $card->facebook_url) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-youtube text-danger me-1"></i> YouTube Channel</label>
                                <input type="url" name="youtube_url" class="form-control" placeholder="https://youtube.com/@channel" value="{{ old('youtube_url', $card->youtube_url) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-x-twitter text-dark me-1"></i> X / Twitter</label>
                                <input type="url" name="twitter_url" class="form-control" placeholder="https://twitter.com/username" value="{{ old('twitter_url', $card->twitter_url) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-telegram text-info me-1"></i> Telegram Handle</label>
                                <input type="url" name="telegram_url" class="form-control" placeholder="https://t.me/username" value="{{ old('telegram_url', $card->telegram_url) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="fab fa-github text-dark me-1"></i> GitHub Profile</label>
                                <input type="url" name="github_url" class="form-control" placeholder="https://github.com/username" value="{{ old('github_url', $card->github_url) }}">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: SEO & STATUS -->
                    <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-sliders-h text-primary me-2"></i>SEO & Card Configuration</h6>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" placeholder="e.g. Amit Singh - Founder & CEO | Digital Business Card" value="{{ old('meta_title', $card->meta_title) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2" placeholder="Preview description when sharing card link on WhatsApp, LinkedIn...">{{ old('meta_description', $card->meta_description) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">QR Code Scan Action</label>
                                <select name="qr_scan_action" class="form-select">
                                    <option value="card_url" {{ old('qr_scan_action', $card->qr_scan_action) == 'card_url' ? 'selected' : '' }}>Open Digital Card Page (Default)</option>
                                    <option value="vcard" {{ old('qr_scan_action', $card->qr_scan_action) == 'vcard' ? 'selected' : '' }}>Direct Save to Contacts (vCard)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Card Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active_check" value="1" {{ old('is_active', $card->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_active_check">Published & Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.digital-cards.index') }}" class="btn btn-outline-secondary px-4 rounded-3">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 rounded-3 fw-bold shadow-sm">
                    <i class="fas fa-save me-1"></i> Save Digital Card
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Mobile Card Simulator Preview -->
    <div class="col-lg-5">
        <div class="sticky-top" style="top: 20px; z-index: 10;">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="badge bg-dark px-3 py-2 rounded-pill"><i class="fas fa-mobile-alt me-1 text-warning"></i> Real-Time Live Preview</span>
                @if($card->id)
                    <a href="{{ $card->public_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fas fa-external-link-alt me-1"></i> Open Live Card
                    </a>
                @endif
            </div>

            <!-- Phone Container Mockup -->
            <div class="phone-mockup border rounded-5 shadow-lg overflow-hidden bg-white mx-auto" style="max-width: 375px;">
                <!-- Phone Header Area -->
                <div class="preview-header-area position-relative" style="height: 190px; background: url('{{ $card->banner_image_url ?: "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1000&auto=format&fit=crop" }}') center/cover no-repeat;">
                    <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0.6) 0%, rgba(255,255,255,0) 100%);"></div>
                    
                    <div class="p-3 d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                        <div class="d-flex flex-column gap-1" id="preview_slogans">
                            <span class="badge bg-transparent p-0 text-start text-white text-uppercase" style="font-size: 9px; letter-spacing: 1.5px; font-weight: 800;">{{ $tags[0] ?? 'PEOPLE' }}</span>
                            <span class="badge bg-transparent p-0 text-start text-white text-uppercase" style="font-size: 9px; letter-spacing: 1.5px; font-weight: 800;">{{ $tags[1] ?? 'TECHNOLOGY' }}</span>
                            <span class="badge bg-transparent p-0 text-start text-white text-uppercase" style="font-size: 9px; letter-spacing: 1.5px; font-weight: 800;">{{ $tags[2] ?? 'GROWTH' }}</span>
                        </div>
                        <div class="bg-white p-1 rounded-3 shadow-sm text-center" style="width: 50px;">
                            <img src="{{ $card->qr_code_url }}" alt="QR" width="40" height="40">
                            <div style="font-size: 6px; font-weight: bold; color: #0f172a; margin-top: 1px;">SCAN ME</div>
                        </div>
                    </div>

                    <!-- Wave SVG -->
                    <div style="position: absolute; bottom: -1px; left: 0; width: 100%; line-height: 0;">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none" style="width: 100%; height: 50px;">
                            <path d="M0,40 C150,110 350,-10 500,60 L500,150 L0,150 Z" fill="#d4af37" opacity="0.6"></path>
                            <path d="M0,60 C150,130 350,10 500,80 L500,150 L0,150 Z" fill="#ffffff"></path>
                        </svg>
                    </div>
                </div>

                <!-- Preview Avatar -->
                <div class="text-center" style="margin-top: -45px; position: relative; z-index: 5;">
                    <img src="{{ $card->profile_image_url }}" id="preview_avatar" alt="Avatar" class="rounded-circle border border-3 border-white shadow" width="85" height="85" style="object-fit: cover; background: #fff;">
                </div>

                <!-- Preview Content -->
                <div class="p-3 text-center">
                    <h5 class="fw-bold mb-0 text-dark" id="preview_name">{{ $card->name ?: 'Amit Singh' }}</h5>
                    <div class="d-inline-flex align-items-center gap-2 my-1" id="preview_designation_wrap">
                        <span style="width: 20px; height: 1px; background: #c59b27;"></span>
                        <span class="small fw-bold" style="color: #c59b27;" id="preview_designation">{{ $card->designation ?: 'Founder' }}</span>
                        <span style="width: 20px; height: 1px; background: #c59b27;"></span>
                    </div>

                    <!-- Companies Preview Grid -->
                    <div class="d-grid grid-3 gap-1 bg-light p-2 rounded-3 my-2 border text-center" style="grid-template-columns: repeat(3, 1fr);" id="preview_companies_grid">
                        @foreach($companiesList as $co)
                            <div class="small p-1">
                                <i class="{{ $co['icon'] ?? 'fas fa-building' }}" style="color: {{ $co['icon_color'] ?? '#c59b27' }}; font-size: 14px;"></i>
                                <div class="fw-bold text-dark extra-small text-truncate" style="font-size: 10px;">{{ $co['name'] }}</div>
                                <div class="text-muted text-truncate" style="font-size: 7px;">{{ $co['subtitle'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Bio -->
                    <p class="fst-italic small text-secondary my-2 px-2" style="font-size: 11px;" id="preview_bio">
                        "{{ $card->bio ?: 'Empowering Education, Technology and Digital Growth through Innovative Solutions.' }}"
                    </p>

                    <!-- Save Contact CTA -->
                    <div class="btn btn-dark w-100 py-2 rounded-3 my-2 d-flex justify-content-between align-items-center px-3 shadow-sm" style="font-size: 12px; background: #0f172a;">
                        <span><i class="fas fa-user-plus text-warning me-2"></i> Save to Contacts</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </div>

                    <!-- 4 Quick Actions -->
                    <div class="row g-2 text-center my-1">
                        <div class="col-3">
                            <div class="bg-light border rounded-3 p-2">
                                <div class="rounded-circle bg-success text-white mx-auto d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;"><i class="fas fa-phone"></i></div>
                                <div style="font-size: 8px; font-weight: bold; margin-top: 3px;">Call</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light border rounded-3 p-2">
                                <div class="rounded-circle bg-success text-white mx-auto d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;"><i class="fab fa-whatsapp"></i></div>
                                <div style="font-size: 8px; font-weight: bold; margin-top: 3px;">WhatsApp</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light border rounded-3 p-2">
                                <div class="rounded-circle bg-danger text-white mx-auto d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;"><i class="fas fa-envelope"></i></div>
                                <div style="font-size: 8px; font-weight: bold; margin-top: 3px;">Email</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light border rounded-3 p-2">
                                <div class="rounded-circle bg-primary text-white mx-auto d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;"><i class="fas fa-map-marker-alt"></i></div>
                                <div style="font-size: 8px; font-weight: bold; margin-top: 3px;">Direction</div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Icons -->
                    <div class="d-flex justify-content-center gap-2 mt-3 mb-1">
                        <span class="badge rounded-circle p-2 bg-primary"><i class="fab fa-linkedin-in"></i></span>
                        <span class="badge rounded-circle p-2 bg-danger"><i class="fab fa-instagram"></i></span>
                        <span class="badge rounded-circle p-2 bg-primary"><i class="fab fa-facebook-f"></i></span>
                        <span class="badge rounded-circle p-2 bg-danger"><i class="fab fa-youtube"></i></span>
                        <span class="badge rounded-circle p-2 bg-dark"><i class="fas fa-globe"></i></span>
                    </div>
                </div>

                <!-- Phone Bottom Wave -->
                <div class="position-relative" style="height: 45px; background: #0f172a; margin-top: 10px;">
                    <svg viewBox="0 0 500 150" preserveAspectRatio="none" style="position: absolute; top: -25px; left: 0; width: 100%; height: 30px;">
                        <path d="M0,100 C150,20 350,140 500,60 L500,150 L0,150 Z" fill="#d4af37" opacity="0.7"></path>
                        <path d="M0,115 C150,45 350,150 500,85 L500,150 L0,150 Z" fill="#0f172a"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Live update listeners
    document.getElementById('input_name')?.addEventListener('input', function() {
        document.getElementById('preview_name').innerText = this.value || 'Amit Singh';
    });

    document.getElementById('input_designation')?.addEventListener('input', function() {
        document.getElementById('preview_designation').innerText = this.value || 'Founder';
    });

    document.getElementById('input_bio')?.addEventListener('input', function() {
        document.getElementById('preview_bio').innerText = '"' + (this.value || 'Empowering Education, Technology and Digital Growth through Innovative Solutions.') + '"';
    });

    // Profile photo live preview
    document.getElementById('input_profile_image')?.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('preview_avatar').src = ev.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Slogan tags live preview
    document.querySelectorAll('.slogan-tag-input').forEach(input => {
        input.addEventListener('input', updateSloganPreview);
    });

    function updateSloganPreview() {
        const inputs = document.querySelectorAll('.slogan-tag-input');
        const container = document.getElementById('preview_slogans');
        let html = '';
        inputs.forEach(inp => {
            if (inp.value) {
                html += `<span class="badge bg-transparent p-0 text-start text-white text-uppercase" style="font-size: 9px; letter-spacing: 1.5px; font-weight: 800;">${inp.value}</span>`;
            }
        });
        container.innerHTML = html;
    }

    // Dynamic Companies Repeater
    let companyCounter = {{ count($companiesList) }};

    function addCompanyRow() {
        const container = document.getElementById('companies_container');
        const idx = companyCounter++;
        const rowHtml = `
            <div class="company-row-item card bg-light border p-3 rounded-3 position-relative" data-idx="${idx}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark small"><i class="fas fa-store me-1 text-secondary"></i> Brand #<span class="row-num">${container.children.length + 1}</span></span>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeCompanyRow(this)"><i class="fas fa-times"></i></button>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Brand Name</label>
                        <input type="text" name="companies[${idx}][name]" class="form-control form-control-sm co-name-input" placeholder="e.g. TheHashTech">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Subtitle / Services</label>
                        <input type="text" name="companies[${idx}][subtitle]" class="form-control form-control-sm co-sub-input" placeholder="e.g. IT Solutions | Digital Growth">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Icon Class</label>
                        <input type="text" name="companies[${idx}][icon]" class="form-control form-control-sm co-icon-input" placeholder="fas fa-building" value="fas fa-building">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Icon / Accent Color</label>
                        <input type="color" name="companies[${idx}][icon_color]" class="form-control form-control-color form-control-sm w-100" value="#c59b27">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Website / Link (Optional)</label>
                        <input type="url" name="companies[${idx}][link]" class="form-control form-control-sm" placeholder="https://...">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', rowHtml);
    }

    function removeCompanyRow(btn) {
        const item = btn.closest('.company-row-item');
        item.remove();
        // renumber
        document.querySelectorAll('.company-row-item').forEach((row, i) => {
            row.querySelector('.row-num').innerText = i + 1;
        });
    }
</script>
@endpush
