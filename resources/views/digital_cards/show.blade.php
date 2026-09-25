<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $card->meta_title ?: ($card->name . ' - ' . ($card->designation ?: 'Digital Business Card')) }}</title>
    
    <!-- Meta Tags for SEO & Social Sharing (WhatsApp, LinkedIn, Twitter) -->
    <meta name="description" content="{{ $card->meta_description ?: ($card->bio ?: ($card->name . ' | ' . $card->designation)) }}">
    <meta property="og:title" content="{{ $card->name }} - {{ $card->designation ?: 'Digital Card' }}">
    <meta property="og:description" content="{{ $card->bio ?: 'Tap to connect, call, chat or save contact directly to your phone.' }}">
    <meta property="og:image" content="{{ $card->profile_image_url }}">
    <meta property="og:url" content="{{ $card->public_url }}">
    <meta property="og:type" content="profile">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-color: {{ $card->theme_color ?: '#0f172a' }};
            --accent-color: {{ $card->accent_color ?: '#c59b27' }};
            --accent-gradient: linear-gradient(135deg, #d4af37 0%, #aa7c11 100%);
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: #0b1120;
            background-image: radial-gradient(circle at 50% 0%, #1e293b 0%, #0b1120 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            color: var(--text-main);
        }

        @media (min-width: 576px) {
            body {
                padding: 24px 12px;
            }
        }

        .card-wrapper {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            min-height: 100vh;
            position: relative;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        @media (min-width: 576px) {
            .card-wrapper {
                min-height: auto;
                border-radius: 36px;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }
        }

        /* Top Header & Banner */
        .card-header-area {
            position: relative;
            width: 100%;
            height: 240px;
            background: url("{{ $card->banner_image_url ?: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1000&auto=format&fit=crop' }}") center center/cover no-repeat;
            overflow: visible;
        }

        .header-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.6) 0%, rgba(15, 23, 42, 0.25) 50%, rgba(255, 255, 255, 0) 100%);
        }

        /* Top Bar: Slogan Tags & Mini QR */
        .top-bar-content {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            z-index: 5;
        }

        .slogan-tags {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .slogan-tag-item {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2.2px;
            color: rgba(255, 255, 255, 0.85);
            text-transform: uppercase;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .mini-qr-box {
            background: #ffffff;
            padding: 7px 8px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .mini-qr-box:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
        }

        .mini-qr-box img {
            width: 58px;
            height: 58px;
            display: block;
            margin: 0 auto;
        }

        .mini-qr-label {
            font-size: 8px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 3px;
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Curved Gold & Dark Wave Divider */
        .wave-separator {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            z-index: 3;
        }

        .wave-separator svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 70px;
        }

        /* Avatar */
        .avatar-container {
            position: relative;
            margin-top: -65px;
            display: flex;
            justify-content: center;
            z-index: 10;
        }

        .avatar-wrapper {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, #f6d365 0%, #fda085 50%, #c59b27 100%);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffffff;
            display: block;
            background: #ffffff;
        }

        /* Main Content Container */
        .card-body-content {
            padding: 12px 24px 20px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 5;
        }

        .profile-name {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 6px;
            letter-spacing: -0.5px;
        }

        .designation-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 2px;
            margin-bottom: 16px;
        }

        .designation-badge .line {
            width: 32px;
            height: 1.5px;
            background: var(--accent-color);
            opacity: 0.7;
        }

        .designation-badge .text {
            font-size: 15px;
            font-weight: 700;
            color: var(--accent-color);
            letter-spacing: 0.5px;
        }

        /* Companies Badges Grid */
        .companies-section {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 12px 8px;
            margin-bottom: 16px;
        }

        .company-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            text-decoration: none;
            color: inherit;
        }

        .company-item:not(:last-child)::after {
            content: '';
            position: absolute;
            right: -4px;
            top: 15%;
            height: 70%;
            width: 1px;
            background: #e2e8f0;
        }

        .company-icon-box {
            font-size: 18px;
            margin-bottom: 4px;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 24px;
        }

        .company-icon-box img {
            max-height: 22px;
            max-width: 40px;
            object-fit: contain;
        }

        .company-title {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .company-subtitle {
            font-size: 9px;
            font-weight: 500;
            color: #64748b;
            line-height: 1.2;
        }

        /* Tagline / Bio Quote */
        .bio-tagline {
            font-size: 13px;
            font-style: italic;
            font-weight: 500;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 20px;
            padding: 0 10px;
        }

        /* Save to Contacts Button */
        .btn-save-contact {
            width: 100%;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border: none;
            padding: 14px 20px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 20px;
        }

        .btn-save-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -5px rgba(15, 23, 42, 0.5);
            color: #ffffff;
        }

        .btn-save-contact .icon-name {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
        }

        .btn-save-contact .icon-name i {
            font-size: 18px;
            color: #f6d365;
        }

        .btn-save-contact .chevron {
            font-size: 14px;
            opacity: 0.8;
        }

        /* Quick Action Grid (2x2) */
        .quick-actions-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        @media (max-width: 380px) {
            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .action-card {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 12px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.2s ease;
        }

        .action-card:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
            color: #0f172a;
        }

        .action-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 6px;
            color: #ffffff;
        }

        .icon-call {
            background: #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .icon-whatsapp {
            background: #25d366;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
        }

        .icon-email {
            background: #ef4444;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }

        .icon-direction {
            background: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }

        .action-title {
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        /* Divider */
        .section-divider {
            width: 40px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
            margin: 4px 0 16px 0;
            opacity: 0.6;
        }

        /* Social Icons Row */
        .social-links-row {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }

        .social-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .social-btn:hover {
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25);
            color: #ffffff;
        }

        .social-linkedin { background: #0077b5; }
        .social-instagram { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
        .social-facebook { background: #1877f2; }
        .social-youtube { background: #ff0000; }
        .social-twitter { background: #000000; }
        .social-telegram { background: #0088cc; }
        .social-github { background: #24292e; }
        .social-website { background: #0f172a; }

        /* Custom Links List */
        .custom-links-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .custom-link-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10px 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .custom-link-card:hover {
            border-color: var(--accent-color);
            background: #f8fafc;
            color: var(--text-main);
        }

        /* Bottom Curved Decoration & Action Bar */
        .card-bottom-footer {
            margin-top: auto;
            position: relative;
            width: 100%;
            height: 90px;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 16px 20px;
        }

        .bottom-wave-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .footer-action-btn {
            position: relative;
            z-index: 5;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .footer-action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #ffffff;
            transform: scale(1.05);
        }

        /* QR Modal */
        .modal-backdrop-custom {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(5px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 24px;
            max-width: 360px;
            width: 100%;
            padding: 24px;
            text-align: center;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: modalPop 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes modalPop {
            0% { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .modal-close-btn {
            position: absolute;
            top: 14px;
            right: 14px;
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
        }

        .modal-qr-img {
            width: 200px;
            height: 200px;
            margin: 16px auto;
            display: block;
            border-radius: 12px;
            padding: 8px;
            border: 1px solid #e2e8f0;
        }

        .btn-modal-action {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .btn-primary-action {
            background: #0f172a;
            color: #ffffff;
        }

        .btn-secondary-action {
            background: #f1f5f9;
            color: #1e293b;
        }

        /* Toast notification */
        .toast-msg {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 2000;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .toast-msg.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>

    <div class="card-wrapper">
        
        <!-- Header Banner & Top Bar -->
        <div class="card-header-area">
            <div class="header-overlay"></div>
            
            <div class="top-bar-content">
                <!-- Slogan Tags (Left) -->
                <div class="slogan-tags">
                    @if(!empty($card->slogan_tags) && is_array($card->slogan_tags))
                        @foreach($card->slogan_tags as $tag)
                            <div class="slogan-tag-item">
                                {{ $tag }}
                            </div>
                        @endforeach
                    @else
                        <div class="slogan-tag-item">PEOPLE</div>
                        <div class="slogan-tag-item">TECHNOLOGY</div>
                        <div class="slogan-tag-item">GROWTH</div>
                    @endif
                </div>

                <!-- Mini QR Code Box (Right) -->
                <div class="mini-qr-box" onclick="openQrModal()" title="Tap to scan & save contact">
                    <img src="{{ $card->qr_code_url }}" alt="QR Code">
                    <div class="mini-qr-label">Scan to<br>Save Contact</div>
                </div>
            </div>

            <!-- Curved Dual Wave Separator -->
            <div class="wave-separator">
                <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                    <!-- Subtle Golden Curve -->
                    <path d="M0,40 C150,110 350,-10 500,60 L500,150 L0,150 Z" fill="#d4af37" opacity="0.6"></path>
                    <!-- Pure White Curve -->
                    <path d="M0,60 C150,130 350,10 500,80 L500,150 L0,150 Z" fill="#ffffff"></path>
                </svg>
            </div>
        </div>

        <!-- Center Avatar -->
        <div class="avatar-container">
            <div class="avatar-wrapper">
                <img src="{{ $card->profile_image_url }}" alt="{{ $card->name }}" class="avatar-img">
            </div>
        </div>

        <!-- Card Body -->
        <div class="card-body-content">
            <!-- Full Name -->
            <h1 class="profile-name">{{ $card->name }}</h1>

            <!-- Designation -->
            @if($card->designation)
                <div class="designation-badge">
                    <span class="line"></span>
                    <span class="text">{{ $card->designation }}</span>
                    <span class="line"></span>
                </div>
            @endif

            <!-- Associated Businesses / Companies Badges -->
            @if(!empty($card->companies) && count($card->companies) > 0)
                <div class="companies-section">
                    @foreach($card->companies as $co)
                        <a href="{{ $co['link'] ?: 'javascript:void(0)' }}" class="company-item" {{ !empty($co['link']) ? 'target="_blank"' : '' }}>
                            <div class="company-icon-box" style="color: {{ $co['icon_color'] ?? '#c59b27' }}">
                                @if(!empty($co['logo']))
                                    <img src="{{ asset($co['logo']) }}" alt="{{ $co['name'] }}">
                                @else
                                    <i class="{{ $co['icon'] ?? 'fas fa-building' }}"></i>
                                @endif
                            </div>
                            <div class="company-title">{{ $co['name'] }}</div>
                            @if(!empty($co['subtitle']))
                                <div class="company-subtitle">{{ $co['subtitle'] }}</div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Bio / Vision Statement -->
            @if($card->bio)
                <div class="bio-tagline">
                    "{{ $card->bio }}"
                </div>
            @endif

            <!-- Primary Save to Contacts Action -->
            <a href="{{ $card->vcard_url }}" 
               class="btn-save-contact" 
               id="saveContactBtn"
               data-name="{{ $card->name }}"
               data-phone="{{ $card->phone }}"
               data-email="{{ $card->email }}"
               data-company="{{ $card->company_name ?: (!empty($card->companies[0]['name']) ? $card->companies[0]['name'] : '') }}"
               data-designation="{{ $card->designation }}"
               data-notes="{{ $card->bio }}"
               data-vcard="{{ $card->vcard_url }}">
                <span class="icon-name">
                    <i class="fas fa-user-plus"></i>
                    <span>Save to Contacts</span>
                </span>
                <i class="fas fa-chevron-right chevron"></i>
            </a>

            <!-- Quick Action Grid (4 Buttons) -->
            <div class="quick-actions-grid">
                <!-- 1. Call Now -->
                @if($card->phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $card->phone) }}" class="action-card">
                        <div class="action-icon-circle icon-call">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <span class="action-title">Call Now</span>
                    </a>
                @endif

                <!-- 2. WhatsApp -->
                @if($card->whatsapp_number)
                    @php
                        $cleanWa = preg_replace('/[^0-9]/', '', $card->whatsapp_number);
                        $waUrl = "https://wa.me/{$cleanWa}";
                        if (!empty($card->whatsapp_message)) {
                            $waUrl .= "?text=" . urlencode($card->whatsapp_message);
                        }
                    @endphp
                    <a href="{{ $waUrl }}" target="_blank" class="action-card">
                        <div class="action-icon-circle icon-whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <span class="action-title">WhatsApp</span>
                    </a>
                @endif

                <!-- 3. Email Me -->
                @if($card->email)
                    <a href="mailto:{{ $card->email }}" class="action-card">
                        <div class="action-icon-circle icon-email">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="action-title">Email Me</span>
                    </a>
                @endif

                <!-- 4. Get Directions -->
                @if($card->maps_url || $card->address)
                    @php
                        $mapsTarget = $card->maps_url ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($card->address);
                    @endphp
                    <a href="{{ $mapsTarget }}" target="_blank" class="action-card">
                        <div class="action-icon-circle icon-direction">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <span class="action-title">Get Directions</span>
                    </a>
                @endif
            </div>

            <!-- Custom Links if any -->
            @if(!empty($card->custom_links) && count($card->custom_links) > 0)
                <div class="custom-links-section">
                    @foreach($card->custom_links as $cLink)
                        <a href="{{ $cLink['url'] }}" target="_blank" class="custom-link-card">
                            <span><i class="{{ $cLink['icon'] ?: 'fas fa-link' }} me-2" style="color: var(--accent-color)"></i> {{ $cLink['title'] }}</span>
                            <i class="fas fa-arrow-up-right-from-square text-muted small"></i>
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Section Divider -->
            <div class="section-divider"></div>

            <!-- Social Profiles Row -->
            <div class="social-links-row">
                @if($card->linkedin_url)
                    <a href="{{ $card->linkedin_url }}" target="_blank" class="social-btn social-linkedin" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                @endif

                @if($card->instagram_url)
                    <a href="{{ $card->instagram_url }}" target="_blank" class="social-btn social-instagram" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                @endif

                @if($card->facebook_url)
                    <a href="{{ $card->facebook_url }}" target="_blank" class="social-btn social-facebook" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                @endif

                @if($card->youtube_url)
                    <a href="{{ $card->youtube_url }}" target="_blank" class="social-btn social-youtube" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                @endif

                @if($card->twitter_url)
                    <a href="{{ $card->twitter_url }}" target="_blank" class="social-btn social-twitter" title="X / Twitter">
                        <i class="fab fa-x-twitter"></i>
                    </a>
                @endif

                @if($card->telegram_url)
                    <a href="{{ $card->telegram_url }}" target="_blank" class="social-btn social-telegram" title="Telegram">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                @endif

                @if($card->github_url)
                    <a href="{{ $card->github_url }}" target="_blank" class="social-btn social-github" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                @endif

                @if($card->website_url)
                    <a href="{{ $card->website_url }}" target="_blank" class="social-btn social-website" title="Website">
                        <i class="fas fa-globe"></i>
                    </a>
                @endif
            </div>

        </div>

        <!-- Bottom Curved Decoration -->
        <div class="card-bottom-footer">
            <svg class="bottom-wave-svg" viewBox="0 0 500 150" preserveAspectRatio="none">
                <!-- Gold Bottom Curve -->
                <path d="M0,100 C150,20 350,140 500,60 L500,150 L0,150 Z" fill="#d4af37" opacity="0.7"></path>
                <!-- Navy Bottom Curve -->
                <path d="M0,115 C150,45 350,150 500,85 L500,150 L0,150 Z" fill="#0f172a"></path>
            </svg>

            <!-- Bottom Left Action: Edit if admin -->
            @if(isset($isAdmin) && $isAdmin)
                <a href="{{ route('admin.digital-cards.edit', $card->id) }}" class="footer-action-btn">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @else
                <button type="button" class="footer-action-btn" onclick="copyCardLink()">
                    <i class="fas fa-link"></i> Copy Link
                </button>
            @endif

            <!-- Bottom Right Action: QR / Share -->
            <button type="button" class="footer-action-btn" onclick="openQrModal()">
                <i class="fas fa-qrcode"></i> QR / Share
            </button>
        </div>

    </div>

    <!-- QR Code & Share Modal -->
    <div id="qrModal" class="modal-backdrop-custom" onclick="if(event.target === this) closeQrModal()">
        <div class="modal-card">
            <button class="modal-close-btn" onclick="closeQrModal()">&times;</button>
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a;">{{ $card->name }}</h3>
            <p style="font-size: 12px; color: #64748b; margin-top: 2px;">Scan this QR code with any mobile camera</p>

            <img src="{{ $card->qr_code_url }}" alt="QR Code" class="modal-qr-img" id="modalQrImage">

            <button class="btn-modal-action btn-primary-action" onclick="downloadQrCode()">
                <i class="fas fa-download"></i> Download QR Code
            </button>
            <button class="btn-modal-action btn-secondary-action" onclick="copyCardLink()">
                <i class="fas fa-copy"></i> Copy Card URL
            </button>
            <button class="btn-modal-action btn-secondary-action" onclick="shareOnWhatsApp()">
                <i class="fab fa-whatsapp text-success"></i> Share via WhatsApp
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast-msg">
        <i class="fas fa-check-circle text-warning"></i>
        <span id="toastText">Link copied to clipboard!</span>
    </div>

    <script>
        function openQrModal() {
            document.getElementById('qrModal').style.display = 'flex';
        }

        function closeQrModal() {
            document.getElementById('qrModal').style.display = 'none';
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toastText').innerText = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        function copyCardLink() {
            const url = "{{ $card->public_url }}";
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Card link copied to clipboard!');
                });
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = url;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showToast('Card link copied to clipboard!');
            }
        }

        function shareOnWhatsApp() {
            const text = encodeURIComponent("Check out my digital business card: {{ $card->name }} ({{ $card->designation }})\n{{ $card->public_url }}");
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }

        function downloadQrCode() {
            const qrImg = document.getElementById('modalQrImage');
            const link = document.createElement('a');
            link.href = qrImg.src;
            link.download = '{{ Str::slug($card->name) }}_qr_code.png';
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('Downloading QR Code image...');
        }

        // ── Save to Contacts — Native Mobile Contact Screen ───────────────────────
        // Android: Triggers Android's native system Intent to launch the phone's
        // "Create Contact / Save Contact" screen directly with pre-filled fields.
        // No .vcf file is downloaded!
        // iOS: Navigates to the .vcf URL where Safari natively opens the "Add to Contacts" sheet.
        (function () {
            var btn = document.getElementById('saveContactBtn');
            if (!btn) return;

            btn.addEventListener('click', function (e) {
                var ua = navigator.userAgent || '';
                var isAndroid = /android/i.test(ua);
                var isIOS     = /iPhone|iPad|iPod/i.test(ua);

                if (isAndroid) {
                    e.preventDefault();
                    var name        = btn.getAttribute('data-name') || '';
                    var phone       = btn.getAttribute('data-phone') || '';
                    var email       = btn.getAttribute('data-email') || '';
                    var company     = btn.getAttribute('data-company') || '';
                    var designation = btn.getAttribute('data-designation') || '';
                    var notes       = btn.getAttribute('data-notes') || '';
                    var vcardUrl    = btn.getAttribute('data-vcard') || btn.href;

                    // Clean phone number for Android Intent (strip spaces/dashes)
                    var cleanPhone = phone.replace(/[^0-9+]/g, '');

                    // Android Native Intent URI to open "Create Contact" screen
                    var intentParts = [
                        'intent:#Intent',
                        'action=android.intent.action.INSERT',
                        'type=vnd.android.cursor.dir/contact',
                        name ? 'S.name=' + encodeURIComponent(name) : '',
                        cleanPhone ? 'S.phone=' + encodeURIComponent(cleanPhone) : '',
                        email ? 'S.email=' + encodeURIComponent(email) : '',
                        company ? 'S.company=' + encodeURIComponent(company) : '',
                        designation ? 'S.job_title=' + encodeURIComponent(designation) : '',
                        notes ? 'S.notes=' + encodeURIComponent(notes) : '',
                        'S.browser_fallback_url=' + encodeURIComponent(vcardUrl),
                        'end;'
                    ].filter(Boolean).join(';');

                    showToast('Opening Phone Contacts…');
                    window.location.href = intentParts;
                } else if (isIOS) {
                    // iOS Safari natively intercepts .vcf and presents the "Add Contact" sheet
                    showToast('Opening Add Contact…');
                    // Let href fire naturally
                } else {
                    // Desktop
                    showToast('Downloading contact file…');
                }
            });
        })();
    </script>

</body>
</html>
