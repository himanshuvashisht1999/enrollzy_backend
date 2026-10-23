@extends('admin.layouts.master')

@section('title', 'Parties Master (Customers, Vendors, Founders, Investors)')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-address-book text-success me-2"></i>Parties Directory</h3>
            <p class="text-muted small mb-0">Unified Contact & Legal Master for Customers, Vendors, Founders, and Investors</p>
        </div>
        <a href="{{ route('admin.accounting.parties.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus me-1"></i> Add Party
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.parties.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search Party</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, GSTIN..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Party Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>Customer (Clients/Institutes)</option>
                        <option value="vendor" {{ request('type') == 'vendor' ? 'selected' : '' }}>Vendor (Suppliers/Agencies)</option>
                        <option value="founder" {{ request('type') == 'founder' ? 'selected' : '' }}>Founder</option>
                        <option value="investor" {{ request('type') == 'investor' ? 'selected' : '' }}>Investor</option>
                        <option value="employee" {{ request('type') == 'employee' ? 'selected' : '' }}>Employee / Staff</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.accounting.parties.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Parties Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Party Name & Contact</th>
                            <th>Role Types</th>
                            <th>GSTIN / PAN</th>
                            <th>State / Place</th>
                            <th class="text-end">Current Balance</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parties as $party)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.accounting.parties.show', $party->id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $party->name }}
                                    </a>
                                    @if($party->legal_name && $party->legal_name !== $party->name)
                                        <div class="small text-muted">{{ $party->legal_name }}</div>
                                    @endif
                                    <div class="small text-muted">
                                        @if($party->email) <i class="fas fa-envelope me-1"></i>{{ $party->email }} @endif
                                        @if($party->phone) &bull; <i class="fas fa-phone me-1"></i>{{ $party->phone }} @endif
                                    </div>
                                </td>
                                <td>
                                    @if($party->is_customer) <span class="badge bg-success-subtle text-success me-1">Customer</span> @endif
                                    @if($party->is_vendor) <span class="badge bg-danger-subtle text-danger me-1">Vendor</span> @endif
                                    @if($party->is_founder) <span class="badge bg-warning-subtle text-warning me-1">Founder</span> @endif
                                    @if($party->is_investor) <span class="badge bg-info-subtle text-info me-1">Investor</span> @endif
                                    @if($party->is_employee) <span class="badge bg-secondary-subtle text-secondary me-1">Employee</span> @endif
                                </td>
                                <td>
                                    @if($party->gstin)
                                        <div class="font-monospace small"><strong class="text-muted">GST:</strong> {{ $party->gstin }}</div>
                                    @endif
                                    @if($party->pan)
                                        <div class="font-monospace small"><strong class="text-muted">PAN:</strong> {{ $party->pan }}</div>
                                    @endif
                                    @if(!$party->gstin && !$party->pan)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $party->state ?? '—' }}
                                    @if($party->state_code)
                                        <span class="badge bg-light text-dark border ms-1 font-monospace">{{ $party->state_code }}</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $party->current_balance < 0 ? 'text-danger' : 'text-dark' }}">
                                    ₹{{ number_format($party->current_balance, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $party->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($party->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounting.parties.show', $party->id) }}" class="btn btn-outline-secondary" title="View Statement">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.accounting.parties.edit', $party->id) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.accounting.parties.destroy', $party->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this party?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No parties found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($parties->hasPages())
                <div class="p-3 border-top">
                    {{ $parties->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
