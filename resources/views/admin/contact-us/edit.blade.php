@extends('admin.layouts.master')

@section('title', 'Manage Contact Us Page')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Manage Premium Contact Us Page</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.contact-us.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Hero Section -->
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-heading me-2"></i>Hero Section</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Hero Badge</label>
                        <input type="text" name="hero_badge" class="form-control" value="{{ old('hero_badge', $contactUs->hero_badge ?? "Let's Talk") }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Hero Title</label>
                        <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $contactUs->hero_title ?? '') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Hero Image</label>
                        <input type="file" name="hero_image" class="form-control" accept="image/*">
                        @if(!empty($contactUs->hero_image))
                            <small class="text-muted d-block mt-1">Current: <a href="{{ asset($contactUs->hero_image) }}" target="_blank">View Image</a></small>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Hero Description</label>
                        <textarea name="hero_description" class="form-control" rows="3">{{ old('hero_description', $contactUs->hero_description ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trust Points (Comma Separated)</label>
                        <textarea name="hero_trust_points" class="form-control" rows="3" placeholder="Free Consultation, Business Process Analysis, Custom Solutions">{{ old('hero_trust_points', is_array($contactUs->hero_trust_points) ? implode(', ', $contactUs->hero_trust_points) : '') }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Primary Button Text</label>
                        <input type="text" name="btn_hero_primary_text" class="form-control" value="{{ old('btn_hero_primary_text', $contactUs->btn_hero_primary_text ?? 'Book Free Consultation') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Primary Button URL</label>
                        <input type="text" name="btn_hero_primary_url" class="form-control" value="{{ old('btn_hero_primary_url', $contactUs->btn_hero_primary_url ?? '#') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Secondary Button Text</label>
                        <input type="text" name="btn_hero_secondary_text" class="form-control" value="{{ old('btn_hero_secondary_text', $contactUs->btn_hero_secondary_text ?? 'Call Now') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Secondary Button URL</label>
                        <input type="text" name="btn_hero_secondary_url" class="form-control" value="{{ old('btn_hero_secondary_url', $contactUs->btn_hero_secondary_url ?? '#') }}">
                    </div>
                </div>
                <hr>

                <!-- Contact Info -->
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-phone me-2"></i>Contact Information Cards</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Main Phone</label>
                        <input type="text" name="phone_general" class="form-control" value="{{ old('phone_general', $contactUs->phone_general ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Support Phone</label>
                        <input type="text" name="phone_toll_free" class="form-control" value="{{ old('phone_toll_free', $contactUs->phone_toll_free ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sales Phone</label>
                        <input type="text" name="phone_sales" class="form-control" value="{{ old('phone_sales', $contactUs->phone_sales ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Working Hours</label>
                        <input type="text" name="office_timings" class="form-control" value="{{ old('office_timings', $contactUs->office_timings ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">General Email</label>
                        <input type="email" name="email_queries" class="form-control" value="{{ old('email_queries', $contactUs->email_queries ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Support Email</label>
                        <input type="email" name="email_support" class="form-control" value="{{ old('email_support', $contactUs->email_support ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sales Email</label>
                        <input type="email" name="email_sales" class="form-control" value="{{ old('email_sales', $contactUs->email_sales ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Head Office Address</label>
                        <textarea name="address_head_office" class="form-control" rows="3">{{ old('address_head_office', $contactUs->address_head_office ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Regional Office Address</label>
                        <textarea name="address_regional_office" class="form-control" rows="3">{{ old('address_regional_office', $contactUs->address_regional_office ?? '') }}</textarea>
                    </div>
                </div>
                <hr>

                <!-- Founder Spotlight -->
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-tie me-2"></i>Founder Spotlight</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Founder Badge</label>
                        <input type="text" name="founder_badge" class="form-control" value="{{ old('founder_badge', $contactUs->founder_badge ?? 'Founder Message') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Founder Heading</label>
                        <input type="text" name="founder_heading" class="form-control" value="{{ old('founder_heading', $contactUs->founder_heading ?? 'A Personal Note From Our Founder') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Founder Image</label>
                        <input type="file" name="co_founder_image" class="form-control" accept="image/*">
                        @if(!empty($contactUs->co_founder_image))
                            <small class="text-muted d-block mt-1">Current: <a href="{{ asset($contactUs->co_founder_image) }}" target="_blank">View Image</a></small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Founder Name</label>
                        <input type="text" name="co_founder_name" class="form-control" value="{{ old('co_founder_name', $contactUs->co_founder_name ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Designation</label>
                        <input type="text" name="co_founder_title" class="form-control" value="{{ old('co_founder_title', $contactUs->co_founder_title ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Founder Email</label>
                        <input type="email" name="co_founder_email" class="form-control" value="{{ old('co_founder_email', $contactUs->co_founder_email ?? '') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Founder Message (Quote)</label>
                        <textarea name="co_founder_message" class="form-control" rows="4">{{ old('co_founder_message', $contactUs->co_founder_message ?? '') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">LinkedIn Link</label>
                        <input type="url" name="co_founder_linkedin" class="form-control" value="{{ old('co_founder_linkedin', $contactUs->co_founder_linkedin ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="btn_founder_book_text" class="form-control" value="{{ old('btn_founder_book_text', $contactUs->btn_founder_book_text ?? 'Book Consultation') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Button URL</label>
                        <input type="text" name="btn_founder_book_url" class="form-control" value="{{ old('btn_founder_book_url', $contactUs->btn_founder_book_url ?? '#') }}">
                    </div>
                </div>
                <hr>

                <!-- Map & Form Trust -->
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-map-marked-alt me-2"></i>Map & Form Setup</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Google Maps Embed URL</label>
                        <textarea name="map_embed_url" class="form-control" rows="3">{{ old('map_embed_url', $contactUs->map_embed_url ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Form Trust Points (Comma Separated)</label>
                        <textarea name="form_trust_points" class="form-control" rows="3" placeholder="100% Confidential, No Spam, Free Initial Consultation">{{ old('form_trust_points', is_array($contactUs->form_trust_points) ? implode(', ', $contactUs->form_trust_points) : '') }}</textarea>
                    </div>
                </div>
                <hr>

                <!-- Why Contact Us Section -->
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-bullseye me-2"></i>Why Contact Us (Dynamic Cards)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Section Heading</label>
                        <input type="text" name="why_contact_heading" class="form-control" value="{{ old('why_contact_heading', $contactUs->why_contact_heading ?? 'Why Businesses Work With Us') }}">
                    </div>
                    
                    @php
                        $cards = $contactUs->why_contact_cards ?? [];
                        if(!is_array($cards)) $cards = [];
                    @endphp

                    @for ($i = 0; $i < 4; $i++)
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6>Card {{ $i + 1 }}</h6>
                                <div class="mb-2">
                                    <label class="form-label small">FontAwesome Icon Class (e.g. fas fa-cogs)</label>
                                    <input type="text" name="why_contact_cards[{{ $i }}][icon]" class="form-control form-control-sm" value="{{ $cards[$i]['icon'] ?? '' }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Title</label>
                                    <input type="text" name="why_contact_cards[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $cards[$i]['title'] ?? '' }}">
                                </div>
                                <div>
                                    <label class="form-label small">Description</label>
                                    <textarea name="why_contact_cards[{{ $i }}][description]" class="form-control form-control-sm" rows="2">{{ $cards[$i]['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
                <hr>

                <!-- Consultation CTA -->
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-handshake me-2"></i>Consultation CTA Banner</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label">CTA Heading</label>
                        <input type="text" name="cta_heading" class="form-control" value="{{ old('cta_heading', $contactUs->cta_heading ?? 'Ready To Improve Your Business Operations?') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Consultant Image</label>
                        <input type="file" name="career_coach_image" class="form-control" accept="image/*">
                        @if(!empty($contactUs->career_coach_image))
                            <small class="text-muted d-block mt-1">Current: <a href="{{ asset($contactUs->career_coach_image) }}" target="_blank">View Image</a></small>
                        @endif
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">CTA Benefits (Comma Separated)</label>
                        <textarea name="career_coach_points" class="form-control" rows="3" placeholder="Reduce dependency on people, Gain visibility into operations, Build scalable systems">{{ old('career_coach_points', is_array($contactUs->career_coach_points) ? implode(', ', $contactUs->career_coach_points) : '') }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Primary Button Text</label>
                        <input type="text" name="btn_book_session_url" class="form-control" value="{{ old('btn_book_session_url', $contactUs->btn_book_session_url ?? '#') }}" placeholder="Primary Button URL">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Secondary Button Text</label>
                        <input type="text" name="btn_cta_secondary_text" class="form-control" value="{{ old('btn_cta_secondary_text', $contactUs->btn_cta_secondary_text ?? 'WhatsApp Us') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Secondary Button URL</label>
                        <input type="text" name="btn_cta_secondary_url" class="form-control" value="{{ old('btn_cta_secondary_url', $contactUs->btn_cta_secondary_url ?? '#') }}">
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-5"><i class="fas fa-save me-2"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
