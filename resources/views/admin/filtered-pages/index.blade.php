@extends('admin.layouts.master')
@section('title', 'Filtered Pages')

@push('css')
<style>
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .badge-category-School {
        background-color: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }
    .badge-category-University {
        background-color: #f3e8ff;
        color: #7e22ce;
        border: 1px solid #e9d5ff;
    }
    .badge-category-Coaching {
        background-color: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .badge-category-Exam {
        background-color: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .badge-category-Scholarship {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .badge-category-Carrier-Road-Map,
    .badge-category-Career-Roadmap {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .page-thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    .page-thumb-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 1.2rem;
    }
    .criteria-tag {
        font-size: 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        padding: 2px 7px;
        border-radius: 6px;
        display: inline-block;
        margin: 2px 2px 2px 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 text-dark fw-bold">
                <i class="fas fa-filter text-primary me-2"></i>Filtered Pages Management
            </h4>
            <p class="text-muted mb-0 small">Create and manage dynamic filter landing pages with custom SEO slugs and targeting criteria.</p>
        </div>
        <a href="{{ route('admin.filtered-pages.create') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Add New Page
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="filter-card p-3 p-md-4 mb-4">
        <form action="{{ route('admin.filtered-pages.index') }}" method="GET" id="filterForm">
            <div class="row g-3">
                <!-- Search Input -->
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Search Keywords</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search title, slug, city..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- State Filter -->
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">State / Location</label>
                    <select name="state" class="form-select form-select-sm">
                        <option value="">All States</option>
                        @foreach($states as $st)
                            <option value="{{ $st }}" {{ request('state') == $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Course Filter -->
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Course</label>
                    <select name="course_id" class="form-select form-select-sm">
                        <option value="">All Courses</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- School Type Filter -->
                @if(isset($schoolTypes) && $schoolTypes->count() > 0)
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">School Type</label>
                    <select name="school_type_id" class="form-select form-select-sm">
                        <option value="">All School Types</option>
                        @foreach($schoolTypes as $stype)
                            <option value="{{ $stype->id }}" {{ request('school_type_id') == $stype->id ? 'selected' : '' }}>
                                {{ $stype->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Per Page -->
                <div class="col-md-4 col-lg-1">
                    <label class="form-label small fw-semibold text-muted mb-1">Per Page</label>
                    <select name="per_page" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                    @if(request()->anyFilled(['search', 'category', 'state', 'course_id', 'school_type_id']))
                        <a href="{{ route('admin.filtered-pages.index') }}" class="btn btn-sm btn-outline-danger px-3 rounded-pill">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    @endif
                </div>

                <!-- Active Filters Info -->
                <div class="text-muted small">
                    Showing <strong>{{ $filteredPages->firstItem() ?? 0 }}</strong> to <strong>{{ $filteredPages->lastItem() ?? 0 }}</strong> of <strong>{{ $filteredPages->total() }}</strong> total pages
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase fw-semibold">
                        <tr>
                            <th class="ps-4" style="width: 70px;">ID</th>
                            <th>Page / Title</th>
                            <th>Category</th>
                            <th>Target Filters / Parameters</th>
                            <th>Frontend URL</th>
                            <th class="text-end pe-4" style="width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filteredPages as $page)
                        <tr>
                            <td class="ps-4 text-muted fw-bold">#{{ $page->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($page->image)
                                        <img src="{{ asset($page->image) }}" alt="image" class="page-thumb">
                                    @else
                                        <div class="page-thumb-placeholder">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $page->title }}</div>
                                        @if($page->sub_title)
                                            <div class="text-muted small text-truncate" style="max-width: 280px;" title="{{ $page->sub_title }}">
                                                {{ $page->sub_title }}
                                            </div>
                                        @endif
                                        <div class="mt-1">
                                            <code class="small text-secondary bg-light px-2 py-0.5 rounded border">/{{ $page->slug }}</code>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $catSlug = \Illuminate\Support\Str::slug($page->category);
                                    $catClass = 'badge-category-' . ($page->category ? str_replace(' ', '-', $page->category) : 'default');
                                @endphp
                                <span class="badge rounded-pill px-3 py-1.5 fw-semibold {{ $catClass }}" style="font-size: 0.8rem;">
                                    {{ $page->category ?: 'Unassigned' }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    @if($page->state)
                                        <span class="criteria-tag"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $page->state }}@if($page->city), {{ $page->city }}@endif</span>
                                    @endif
                                    @if($page->course)
                                        <span class="criteria-tag"><i class="fas fa-graduation-cap text-info me-1"></i>Course: {{ $page->course->name }}</span>
                                    @endif
                                    @if($page->schoolType)
                                        <span class="criteria-tag"><i class="fas fa-school text-primary me-1"></i>Type: {{ $page->schoolType->title }}</span>
                                    @endif
                                    @if($page->ownership_type)
                                        <span class="criteria-tag"><i class="fas fa-building text-secondary me-1"></i>{{ $page->ownership_type }}</span>
                                    @endif
                                    @if($page->curriculum)
                                        <span class="criteria-tag"><i class="fas fa-book text-warning me-1"></i>{{ $page->curriculum }}</span>
                                    @endif
                                    @if($page->university_type)
                                        <span class="criteria-tag"><i class="fas fa-university text-purple me-1"></i>{{ $page->university_type }}</span>
                                    @endif
                                    @if($page->degree)
                                        <span class="criteria-tag"><i class="fas fa-award text-success me-1"></i>{{ $page->degree }}</span>
                                    @endif
                                    @if($page->stream)
                                        <span class="criteria-tag"><i class="fas fa-stream text-primary me-1"></i>{{ $page->stream->title }}</span>
                                    @endif
                                    @if($page->coachingCategory)
                                        <span class="criteria-tag"><i class="fas fa-chalkboard-teacher text-warning me-1"></i>{{ $page->coachingCategory->title }}</span>
                                    @endif
                                    @if($page->programType)
                                        <span class="criteria-tag"><i class="fas fa-laptop-code text-info me-1"></i>{{ $page->programType->title }}</span>
                                    @endif
                                    @if(!$page->state && !$page->course && !$page->schoolType && !$page->ownership_type && !$page->curriculum && !$page->university_type && !$page->degree && !$page->stream && !$page->coachingCategory && !$page->programType)
                                        <span class="text-muted small"><em>No extra criteria</em></span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $frontendBase = env('FRONTEND_URL', 'http://127.0.0.1:8000');
                                    $frontendUrl = rtrim($frontendBase, '/') . '/' . ltrim($page->slug, '/');
                                @endphp
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $frontendUrl }}" target="_blank" class="btn btn-outline-info rounded-start px-2 py-1" title="Open Frontend Page">
                                        <i class="fas fa-external-link-alt me-1"></i> View
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary rounded-end px-2 py-1" onclick="copyToClipboard('{{ $frontendUrl }}', this)" title="Copy Link to Clipboard">
                                        <i class="fas fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.filtered-pages.edit', $page) }}" class="btn btn-sm btn-outline-primary rounded-3" title="Edit Filtered Page">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.filtered-pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this filtered page?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete Filtered Page">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-filter fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold">No Filtered Pages Found</h6>
                                    <p class="small mb-3">No filtered pages match your selected filter criteria.</p>
                                    @if(request()->anyFilled(['search', 'category', 'state', 'course_id', 'school_type_id']))
                                        <a href="{{ route('admin.filtered-pages.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fas fa-undo me-1"></i> Reset All Filters
                                        </a>
                                    @else
                                        <a href="{{ route('admin.filtered-pages.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                            <i class="fas fa-plus me-1"></i> Create First Page
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($filteredPages->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="text-muted small">
                Showing {{ $filteredPages->firstItem() ?? 0 }} to {{ $filteredPages->lastItem() ?? 0 }} of {{ $filteredPages->total() }} entries
            </div>
            <div>
                {{ $filteredPages->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard(text, btnElement) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess(btnElement);
        }).catch(function() {
            fallbackCopy(text, btnElement);
        });
    } else {
        fallbackCopy(text, btnElement);
    }
}

function fallbackCopy(text, btnElement) {
    var tempInput = document.createElement("input");
    tempInput.style = "position: absolute; left: -1000px; top: -1000px";
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
        document.execCommand("copy");
        showCopySuccess(btnElement);
    } catch (err) {
        alert("Copied URL: " + text);
    }
    document.body.removeChild(tempInput);
}

function showCopySuccess(btnElement) {
    if (typeof toastr !== 'undefined') {
        toastr.success('Page URL copied to clipboard!');
    }
    if (btnElement) {
        var originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-check text-success me-1"></i> Copied!';
        btnElement.classList.add('btn-light');
        setTimeout(function() {
            btnElement.innerHTML = originalHtml;
            btnElement.classList.remove('btn-light');
        }, 1800);
    }
}
</script>
@endsection

