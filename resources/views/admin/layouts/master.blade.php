<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') | {{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}">
    <style>
        :root {
            --sidebar-width: 270px;
            --admin-theme: #1a222b;
            --admin-dark: #12181f;
            --admin-hover: #252f3a;
            --admin-accent: #f39c12;
            /* Neutral Gold Accent */
            --sidebar-text: rgba(255, 255, 255, 0.7);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: var(--admin-theme);
            color: #fff;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Sidebar Scrollbar */
        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: var(--admin-dark);
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #3e4b5b;
            border-radius: 10px;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            text-align: center;
            background-color: var(--admin-dark);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-heading {
            padding: 12px 20px 4px !important;
            margin-top: 2px !important;
            opacity: 0.4;
            font-size: 9px !important;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #fff;
        }

        #sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 10px 20px;
            border-radius: 0;
            margin-bottom: 0;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
        }

        #sidebar .nav-link:hover {
            color: #fff;
            background-color: var(--admin-hover);
            text-decoration: none;
        }

        #sidebar .nav-link.active {
            color: #fff;
            background-color: var(--admin-dark);
            border-left: 3px solid var(--admin-accent);
        }

        #sidebar .nav-link i:first-child {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            opacity: 0.8;
        }

        #sidebar .menu-arrow {
            margin-left: auto;
            transition: transform 0.3s;
            font-size: 0.75rem;
        }

        #sidebar .nav-link:not(.collapsed) .menu-arrow {
            transform: rotate(180deg);
        }

        #sidebar .sub-link {
            padding: 7px 20px 7px 52px;
            font-size: 0.85rem;
            color: var(--sidebar-text);
            background-color: rgba(0, 0, 0, 0.1);
        }

        #sidebar .sub-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        #sidebar .sub-link.active {
            color: var(--admin-accent);
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.2);
        }

        #content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }

        .admin-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 10px 20px;
            margin-bottom: 20px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
            border-radius: 10px;
        }
    </style>
    @stack('css')
</head>

<body>
    @php
        $user = Auth::guard('web')->user() ?? Auth::guard('expert')->user() ?? Auth::guard('alumni')->user();
        $isExpert = Auth::guard('expert')->check();
        $isAlumni = Auth::guard('alumni')->check();
        $isAdmin = Auth::guard('web')->check() && $user && ($user->is_admin ?? false);
    @endphp

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header py-3 text-center">
            @if($site_settings->logo ?? false)
                <img src="{{ asset($site_settings->logo) }}" alt="Logo" style="max-height: 40px; max-width: 80%;">
            @else
                <h4 class="mb-0 text-white">
                    <span>{{ explode(' ', $site_settings->site_name ?? 'Admin Panel')[0] }}</span>
                    {{ implode(' ', array_slice(explode(' ', $site_settings->site_name ?? 'Admin Panel'), 1)) }}
                </h4>
            @endif
        </div>
        <ul class="nav flex-column mt-2 px-2">
            {{-- Dashboard --}}
            <li class="nav-item">
                @php
                    $dashRoute = $isAdmin ? 'admin.dashboard' : ($isExpert ? 'expert.dashboard' : 'alumni.dashboard');
                @endphp
                <a class="nav-link {{ request()->routeIs($dashRoute) ? 'active' : '' }}" href="{{ route($dashRoute) }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>

            @if($isAdmin)
                <div class="sidebar-heading px-3 text-uppercase fw-bold">Core Management</div>

                {{-- Academics Group --}}
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/organisations*', 'admin/exams*', 'admin/organisation-courses*', 'admin/courses*', 'admin/experts*', 'admin/alumni*', 'admin/noteworthy*', 'admin/program-levels*', 'admin/program-types*', 'admin/stream-offereds*', 'admin/disciplines*', 'admin/specializations*', 'admin/organisation-types*', 'admin/accreditation-approvals*', 'admin/campus-types*', 'admin/sports*', 'admin/exam-stages*') ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#academicsMenu" role="button"
                        aria-expanded="{{ request()->is('admin/organisations*', 'admin/exams*', 'admin/organisation-courses*', 'admin/courses*', 'admin/experts*', 'admin/alumni*', 'admin/noteworthy*', 'admin/program-levels*', 'admin/program-types*', 'admin/stream-offereds*', 'admin/disciplines*', 'admin/specializations*', 'admin/organisation-types*', 'admin/accreditation-approvals*', 'admin/campus-types*', 'admin/sports*', 'admin/exam-stages*') ? 'true' : 'false' }}">
                        <span><i class="fas fa-graduation-cap"></i> Academics</span>
                        <i class="fas fa-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->is('admin/organisations*', 'admin/exams*', 'admin/organisation-courses*', 'admin/courses*', 'admin/noteworthy*', 'admin/program-levels*', 'admin/program-types*', 'admin/stream-offereds*', 'admin/disciplines*', 'admin/specializations*', 'admin/organisation-types*', 'admin/accreditation-approvals*', 'admin/campus-types*', 'admin/sports*', 'admin/exam-stages*') ? 'show' : '' }}"
                        id="academicsMenu">
                        <ul class="nav flex-column ps-3">
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.organisations.*') || request()->routeIs('admin.organisation-courses.*') ? 'active' : '' }}"
                                    href="{{ route('admin.organisations.index') }}">Organisations</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"
                                    href="{{ route('admin.exams.index') }}">Exams List</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}"
                                    href="{{ route('admin.courses.index') }}">Course Master</a></li>

                            <div class="sidebar-heading px-3 pt-3 pb-2 text-uppercase fw-bold text-white-50">Masters</div>

                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.noteworthy-categories.*') ? 'active' : '' }}"
                                    href="{{ route('admin.noteworthy-categories.index') }}">Noteworthy Categories</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.noteworthy-mentions.*') ? 'active' : '' }}"
                                    href="{{ route('admin.noteworthy-mentions.index') }}">Noteworthy Mentions</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.program-levels.*') ? 'active' : '' }}"
                                    href="{{ route('admin.program-levels.index') }}">Program Level</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.program-types.*') ? 'active' : '' }}"
                                    href="{{ route('admin.program-types.index') }}">Program Type</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.stream-offereds.*') ? 'active' : '' }}"
                                    href="{{ route('admin.stream-offereds.index') }}">Stream Offered</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.disciplines.*') ? 'active' : '' }}"
                                    href="{{ route('admin.disciplines.index') }}">Discipline</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.specializations.*') ? 'active' : '' }}"
                                    href="{{ route('admin.specializations.index') }}">Specialization</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.organisation-types.*') ? 'active' : '' }}"
                                    href="{{ route('admin.organisation-types.index') }}">Organisation Type</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.accreditation-approvals.*') ? 'active' : '' }}"
                                    href="{{ route('admin.accreditation-approvals.index') }}">Accreditation</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.campus-types.*') ? 'active' : '' }}"
                                    href="{{ route('admin.campus-types.index') }}">Campus Type</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.sports.*') ? 'active' : '' }}"
                                    href="{{ route('admin.sports.index') }}">Sports</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}"
                                    href="{{ route('admin.languages.index') }}">Languages</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.exam-stages.*') ? 'active' : '' }}"
                                    href="{{ route('admin.exam-stages.index') }}">Exam Stages</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.caste-categories.*') ? 'active' : '' }}"
                                    href="{{ route('admin.caste-categories.index') }}">Caste Categories</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Expert Management --}}
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/experts*') || (request()->routeIs('leads.index') && request()->type == 'Expert') ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#expertMenu" role="button"
                        aria-expanded="{{ request()->is('admin/experts*') || (request()->routeIs('leads.index') && request()->type == 'Expert') ? 'true' : 'false' }}">
                        <span><i class="fas fa-user-tie me-2"></i>Expert Management</span>
                        <i class="fas fa-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->is('admin/experts*') || (request()->routeIs('leads.index') && request()->type == 'Expert') ? 'show' : '' }}"
                        id="expertMenu">
                        <ul class="nav flex-column ps-3">
                            <li><a class="nav-link sub-link {{ request()->routeIs('experts.*') ? 'active' : '' }}"
                                    href="{{ route('experts.index') }}">Experts List</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('expert-categories.*') ? 'active' : '' }}"
                                    href="{{ route('expert-categories.index') }}">Categories</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}"
                                    href="{{ route('admin.bookings.index') }}">Bookings</a></li>

                        </ul>
                    </div>
                </li>

                {{-- Alumni Management --}}
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/alumni*') || (request()->routeIs('leads.index') && request()->type == 'Alumni') ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#alumniMenu" role="button"
                        aria-expanded="{{ request()->is('admin/alumni*') || (request()->routeIs('leads.index') && request()->type == 'Alumni') ? 'true' : 'false' }}">
                        <span><i class="fas fa-user-friends me-2"></i>Alumni Management</span>
                        <i class="fas fa-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->is('admin/alumni*') || (request()->routeIs('leads.index') && request()->type == 'Alumni') ? 'show' : '' }}"
                        id="alumniMenu">
                        <ul class="nav flex-column ps-3">
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.alumni.*') ? 'active' : '' }}"
                                    href="{{ route('admin.alumni.index') }}">Alumni List</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('leads.index') && request()->type == 'Alumni' ? 'active' : '' }}"
                                    href="{{ route('leads.index', ['type' => 'Alumni']) }}">Alumni Leads</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Student Management --}}
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/students*') ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#studentMenu" role="button"
                        aria-expanded="{{ request()->is('admin/students*') ? 'true' : 'false' }}">
                        <span><i class="fas fa-user-graduate me-2"></i>Student Management</span>
                        <i class="fas fa-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->is('admin/students*') ? 'show' : '' }}" id="studentMenu">
                        <ul class="nav flex-column ps-3">
                            <li><a class="nav-link sub-link {{ request()->routeIs('students.index') ? 'active' : '' }}"
                                    href="{{ route('students.index') }}">Students List</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('students.create') ? 'active' : '' }}"
                                    href="{{ route('students.create') }}">Add Student</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Community Group --}}
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/community*') ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#communityMenu" role="button"
                        aria-expanded="{{ request()->is('admin/community*') ? 'true' : 'false' }}">
                        <span><i class="fas fa-users me-2"></i> Community</span>
                        <i class="fas fa-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->is('admin/community*') ? 'show' : '' }}" id="communityMenu">
                        <ul class="nav flex-column ps-3">
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.community-categories.*') ? 'active' : '' }}"
                                    href="{{ route('admin.community-categories.index') }}">Categories</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.community-questions.*') ? 'active' : '' }}"
                                    href="{{ route('admin.community-questions.index') }}">Questions</a></li>
                        </ul>
                    </div>
                </li>

                <div class="sidebar-heading px-3 text-uppercase fw-bold">Marketing & Content</div>

                {{-- Content Group --}}
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/blogs*', 'admin/categories*', 'admin/faqs*', 'admin/testimonials*', 'admin/video-testimonials*') ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#contentMenu" role="button"
                        aria-expanded="{{ request()->is('admin/blogs*', 'admin/categories*', 'admin/faqs*', 'admin/testimonials*', 'admin/video-testimonials*') ? 'true' : 'false' }}">
                        <span><i class="fas fa-copy"></i> Content</span>
                        <i class="fas fa-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->is('admin/blogs*', 'admin/categories*', 'admin/faqs*', 'admin/testimonials*', 'admin/video-testimonials*') ? 'show' : '' }}"
                        id="contentMenu">
                        <ul class="nav flex-column ps-3">
                            <li><a class="nav-link sub-link {{ request()->routeIs('blogs.index') ? 'active' : '' }}"
                                    href="{{ route('blogs.index') }}">Blogs</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('categories.index') ? 'active' : '' }}"
                                    href="{{ route('categories.index') }}">Blog Categories</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('faqs.index') ? 'active' : '' }}"
                                    href="{{ route('faqs.index') }}">FAQs</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('testimonials.index') ? 'active' : '' }}"
                                    href="{{ route('testimonials.index') }}">Testimonials</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.video-testimonials.*') ? 'active' : '' }}"
                                    href="{{ route('admin.video-testimonials.index') }}">Video Stories</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Homepage Setup Group --}}
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/homepage-sections*', 'admin/hero-sliders*', 'admin/home-services*', 'admin/home-benefits*') ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#homepageMenu" role="button"
                        aria-expanded="{{ request()->is('admin/homepage-sections*', 'admin/hero-sliders*', 'admin/home-services*', 'admin/home-benefits*') ? 'true' : 'false' }}">
                        <span><i class="fas fa-home"></i> Homepage Setup</span>
                        <i class="fas fa-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->is('admin/homepage-sections*', 'admin/hero-sliders*', 'admin/home-services*', 'admin/home-benefits*') ? 'show' : '' }}"
                        id="homepageMenu">
                        <ul class="nav flex-column ps-3">
                            <li><a class="nav-link sub-link {{ request()->routeIs('homepage-sections.index') ? 'active' : '' }}"
                                    href="{{ route('homepage-sections.index') }}">Manage Sections</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.hero-sliders.index') ? 'active' : '' }}"
                                    href="{{ route('admin.hero-sliders.index') }}">Hero Sliders</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.home-services.*') ? 'active' : '' }}"
                                    href="{{ route('admin.home-services.index') }}">Specialized Courses</a></li>
                            <li><a class="nav-link sub-link {{ request()->routeIs('admin.home-benefits.*') ? 'active' : '' }}"
                                    href="{{ route('admin.home-benefits.index') }}">Why Choose Us</a></li>
                        </ul>
                    </div>
                </li>

                <div class="sidebar-heading px-3 text-uppercase fw-bold">System</div>

                {{-- System Group --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leads.index') ? 'active' : '' }}"
                        href="{{ route('leads.index') }}">
                        <i class="fas fa-envelope-open-text"></i> Student Leads
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"
                        href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-tools"></i> General Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.commission.index') ? 'active' : '' }}"
                        href="{{ route('admin.commission.index') }}">
                        <i class="fas fa-percent"></i> Commission Rules
                    </a>
                </li>
            @endif

            @if($isExpert || $isAlumni)
                <div class="sidebar-heading px-3 text-uppercase fw-bold">My Guidance Panel</div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('expert.slots.*') ? 'active' : '' }}"
                        href="{{ route('expert.slots.index') }}">
                        <i class="fas fa-clock"></i> My Slots
                    </a>
                </li>
                @if($isExpert)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('expert.leads.*') ? 'active' : '' }}"
                            href="{{ route('expert.leads.index') }}">
                            <i class="fas fa-envelope-open-text"></i> My Leads
                        </a>
                    </li>
                @endif


                @if($isExpert)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('expert.bookings.*') ? 'active' : '' }}"
                            href="{{ route('expert.bookings.index') }}">
                            <i class="fas fa-calendar-check"></i> Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('expert.payouts.*') ? 'active' : '' }}"
                            href="{{ route('expert.payouts.index') }}">
                            <i class="fas fa-wallet"></i> Payouts
                        </a>
                    </li>
                @else
                    {{-- Fallback for Alumni if needed, or hide until Alumni module is updated --}}
                    {{--
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-check"></i> Appointments
                        </a>
                    </li>
                    --}}
                @endif
            @endif

            <li class="nav-item mt-3 mb-4">
                @php
                    $logoutRoute = $isExpert ? route('expert.logout') : ($isAlumni ? route('alumni.logout') : route('logout'));
                @endphp
                <form action="{{ $logoutRoute }}" method="POST" id="logout-form">
                    @csrf
                    <a class="nav-link text-danger" href="javascript:void(0)"
                        onclick="document.getElementById('logout-form').submit();">
                        <i class="fas fa-power-off"></i> Logout
                    </a>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="admin-navbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0">@yield('title')</h5>
            <div class="user-info">
                <span>Welcome, {{ $user->name ?? 'User' }}</span>
                @if($isAdmin) <span class="badge bg-danger ms-2">Admin</span> @endif
                @if($isExpert) <span class="badge bg-primary ms-2">Expert</span> @endif
                @if($isAlumni) <span class="badge bg-success ms-2">Alumni</span> @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @stack('js')
    <script>
        // Smoothly handle collapse arrows and active states
        $(document).ready(function () {
            $('.nav-link[data-bs-toggle="collapse"]').on('click', function () {
                $(this).find('.menu-arrow').toggleClass('rotate-180');
            });
        });
    </script>
</body>

</html>