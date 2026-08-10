<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Expert Portal') - Enrollzy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            width: 250px;
            background: #0f172a;
            color: #fff;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 1px solid #1e293b;
            color: #6366f1;
        }
        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
            margin: 0;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: #1e293b;
            color: #ffffff;
            border-left: 4px solid #6366f1;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .navbar-custom {
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 15px 30px;
            margin-bottom: 30px;
            border-radius: 12px;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            background: #ffffff;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-person-workspace me-2"></i>Enrollzy Expert
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="{{ route('expert.dashboard') }}" class="{{ request()->routeIs('expert.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-3"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('expert.slots.index') }}" class="{{ request()->routeIs('expert.slots.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event me-3"></i>Slot Management
            </a>
        </li>
        <li>
            <a href="{{ route('expert.bookings.index') }}" class="{{ request()->routeIs('expert.bookings.*') ? 'active' : '' }}">
                <i class="bi bi-journal-check me-3"></i>Booked Sessions
            </a>
        </li>
        <li class="mt-4 pt-3 border-top border-secondary">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right me-3 text-danger"></i>Logout
            </a>
            <form id="logout-form" action="{{ route('expert.logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">@yield('page-title', 'Dashboard')</h5>
        <div class="d-flex align-items-center">
            <img src="{{ asset(Auth::guard('expert')->user()->img ?? 'uploads/experts/default.png') }}" class="rounded-circle me-2" width="38" height="38" style="object-fit: cover;">
            <div>
                <div class="fw-semibold text-dark">{{ Auth::guard('expert')->user()->name }}</div>
                <small class="text-muted fs-7">{{ Auth::guard('expert')->user()->role ?? 'Mentor' }}</small>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
