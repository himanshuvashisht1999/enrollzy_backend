@extends('admin.layouts.master')

@section('title', 'Organisation Billing')

{{-- Billing → Organisation Billing (BillingOrganisationController): the billing details we print on invoices to an
     organisation, or to each of its campuses. For billing only; the website never shows them. --}}
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-dark fw-bold">Organisation Billing</h4>
        <p class="text-muted small mb-0">Address, GSTIN, TAN, PAN and CIN for invoices. An organisation with campuses is billed per campus. Used for billing only, never shown on the website.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.billing.organisations.index') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by organisation, legal name or GSTIN..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Billing details</label>
                <select name="setup" class="form-select">
                    <option value="">All ({{ $counts['all'] }})</option>
                    <option value="done" {{ $setup === 'done' ? 'selected' : '' }}>Set up ({{ $counts['done'] }})</option>
                    <option value="missing" {{ $setup === 'missing' ? 'selected' : '' }}>Missing ({{ $counts['all'] - $counts['done'] }})</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i> Filter</button>
                @if($search !== '' || $setup)
                    <a href="{{ route('admin.billing.organisations.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Organisation</th>
                        <th>Legal name</th>
                        <th>Tax details (organisation)</th>
                        <th>Billed as</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organisations as $x)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('admin.billing.organisations.edit', $x->org->id) }}" class="fw-bold text-dark text-decoration-none">{{ $x->org->name }}</a>
                            @if($x->own && $x->own->city)
                                <div class="small text-muted">{{ implode(', ', array_filter([$x->own->city, $x->own->state])) }}</div>
                            @endif
                        </td>
                        <td>{!! $x->own && $x->own->legal_name ? e($x->own->legal_name) : '<span class="text-muted small">—</span>' !!}</td>
                        <td>
                            @foreach(['gstin' => 'GSTIN', 'pan_number' => 'PAN', 'tan_number' => 'TAN', 'cin_number' => 'CIN'] as $f => $label)
                                @if($x->own && $x->own->$f)
                                    <div class="small"><strong>{{ $label }}:</strong> <span class="badge bg-light text-dark border font-monospace">{{ $x->own->$f }}</span></div>
                                @endif
                            @endforeach
                            @if(!$x->own || !($x->own->gstin || $x->own->pan_number || $x->own->tan_number || $x->own->cin_number))
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($x->campuses)
                                <span class="badge {{ $x->done ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle' }} border px-2 py-1">
                                    {{ $x->ready }} of {{ $x->campuses }} {{ Str::plural('campus', $x->campuses) }} set up
                                </span>
                            @else
                                <span class="badge {{ $x->done ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border px-2 py-1">
                                    Organisation · {{ $x->done ? 'set up' : 'missing' }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.billing.organisations.edit', $x->org->id) }}" class="btn btn-sm btn-outline-info" title="Edit billing details">
                                <i class="fas fa-edit me-1"></i> Billing details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-university fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                            <h6 class="text-muted">No organisations found</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($organisations->hasPages())
    <div class="card-footer bg-white border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="small text-muted">Showing {{ $organisations->firstItem() }} to {{ $organisations->lastItem() }} of {{ $organisations->total() }} organisations</div>
            <div>{{ $organisations->links() }}</div>
        </div>
    </div>
    @endif
</div>
@endsection
