@extends('admin.layouts.master')

@section('title', 'Billing Clients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-dark fw-bold">Billing Clients</h4>
        <p class="text-muted small mb-0">Manage your billing clients, company tax details (GSTIN/TAN), and contact information.</p>
    </div>
    <a href="{{ route('admin.billing.clients.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Add New Client
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.billing.clients.index') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, email, phone, GSTIN, TAN..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Company Type</label>
                <select name="company_type" class="form-select">
                    <option value="">All Company Types</option>
                    @foreach($companyTypes as $type)
                        <option value="{{ $type }}" {{ request('company_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'company_type', 'status']))
                    <a href="{{ route('admin.billing.clients.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Clients Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Client / Company</th>
                        <th>Contact Person</th>
                        <th>Contact Details</th>
                        <th>Tax Details (GSTIN / TAN)</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; font-size: 16px;">
                                    {{ strtoupper(substr($client->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.billing.clients.show', $client->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                        {{ $client->name }}
                                    </a>
                                    @if($client->company_type)
                                        <div><span class="badge bg-secondary-subtle text-secondary border mt-1" style="font-size: 11px;">{{ $client->company_type }}</span></div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($client->contact_person)
                                <div class="text-dark fw-medium">{{ $client->contact_person }}</div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($client->email)
                                <div class="small"><i class="fas fa-envelope text-muted me-1"></i> <a href="mailto:{{ $client->email }}" class="text-decoration-none text-muted">{{ $client->email }}</a></div>
                            @endif
                            @if($client->phone)
                                <div class="small"><i class="fas fa-phone text-muted me-1"></i> {{ $client->phone }}</div>
                            @endif
                            @if(!$client->email && !$client->phone)
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($client->gstin)
                                <div class="small"><strong>GSTIN:</strong> <span class="badge bg-light text-dark border font-monospace">{{ $client->gstin }}</span></div>
                            @endif
                            @if($client->tan_number)
                                <div class="small mt-1"><strong>TAN:</strong> <span class="badge bg-light text-dark border font-monospace">{{ $client->tan_number }}</span></div>
                            @endif
                            @if($client->cin_number)
                                <div class="small mt-1"><strong>CIN:</strong> <span class="badge bg-light text-dark border font-monospace">{{ $client->cin_number }}</span></div>
                            @endif
                            @if(!$client->gstin && !$client->tan_number && !$client->cin_number)
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($client->city || $client->state)
                                <div class="small text-dark">{{ implode(', ', array_filter([$client->city, $client->state])) }}</div>
                                @if($client->country && $client->country !== 'India')
                                    <div class="small text-muted">{{ $client->country }}</div>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($client->status)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.billing.clients.show', $client->id) }}" class="btn btn-outline-secondary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.billing.clients.edit', $client->id) }}" class="btn btn-outline-info" title="Edit Client">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.billing.clients.destroy', $client->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete Client" onclick="return confirm('Are you sure you want to delete client {{ addslashes($client->name) }}?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                            <h6 class="text-muted">No billing clients found</h6>
                            <p class="small text-muted mb-3">Add your first billing client to get started with invoicing and tax management.</p>
                            <a href="{{ route('admin.billing.clients.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Add Client
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($clients->hasPages())
    <div class="card-footer bg-white border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Showing {{ $clients->firstItem() }} to {{ $clients->lastItem() }} of {{ $clients->total() }} clients
            </div>
            <div>
                {{ $clients->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
