@extends('admin.layouts.master')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <!-- Experts Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-primary-subtle text-primary rounded-3 p-3">
                        <i class="fas fa-user-graduate fa-lg"></i>
                    </div>
                    <span class="badge bg-success-subtle text-success small">+12%</span>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['experts'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Total Experts</p>
            </div>
        </div>
    </div>

    <!-- Blogs Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-info-subtle text-info rounded-3 p-3">
                        <i class="fas fa-newspaper fa-lg"></i>
                    </div>
                    <span class="badge bg-info-subtle text-info small">{{ $stats['categories'] }} Cats</span>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['blogs'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Blog Posts</p>
            </div>
        </div>
    </div>

    <!-- Leads Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-warning-subtle text-warning rounded-3 p-3">
                        <i class="fas fa-envelope-open-text fa-lg"></i>
                    </div>
                    @if($stats['new_leads'] > 0)
                        <span class="badge bg-danger text-white pulse">New</span>
                    @endif
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['leads'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Student Leads</p>
            </div>
        </div>
    </div>

    <!-- Testimonials Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-success-subtle text-success rounded-3 p-3">
                        <i class="fas fa-quote-left fa-lg"></i>
                    </div>
                    <div class="text-warning small">
                        <i class="fas fa-star"></i> 4.9
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['testimonials'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Reviews</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Recent Inquiries</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small text-uppercase">
                            <tr>
                                <th class="ps-4">Student</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- This will be populated dynamically if we want --}}
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Go to <a href="{{ route('leads.index') }}">Leads</a> to see all inquiries.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-2">
                    <a href="{{ route('blogs.create') }}" class="btn btn-primary py-2"><i class="fas fa-pen-nib me-2"></i> Write a Blog</a>
                    <a href="{{ route('experts.create') }}" class="btn btn-outline-secondary py-2"><i class="fas fa-plus me-2"></i> Add Expert</a>
                    <a href="{{ route('categories.create') }}" class="btn btn-light py-2 border"><i class="fas fa-tag me-2"></i> New Category</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    .ls-1 { letter-spacing: 0.5px; }
    .pulse { animation: pulse-animation 2s infinite; }
    @keyframes pulse-animation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
</style>
@endsection
