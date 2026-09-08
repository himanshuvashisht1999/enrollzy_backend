@extends('admin.layouts.master')

@section('title', 'Journal Vouchers (General Journal)')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-book text-dark me-2"></i>Journal Vouchers (JV)</h3>
            <p class="text-muted small mb-0">Double-Entry Journal Engine & Manual Adjustment Vouchers</p>
        </div>
        <a href="{{ route('admin.accounting.journals.create') }}" class="btn btn-dark btn-sm">
            <i class="fas fa-plus me-1"></i> New Journal Voucher
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

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.journals.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Journal # or description" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Entry Type</label>
                    <select name="entry_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="manual" {{ request('entry_type') == 'manual' ? 'selected' : '' }}>Manual Voucher (CA / Adjustments)</option>
                        <option value="auto" {{ request('entry_type') == 'auto' ? 'selected' : '' }}>System Auto-Posted</option>
                        <option value="reversal" {{ request('entry_type') == 'reversal' ? 'selected' : '' }}>Reversal Voucher</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.accounting.journals.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Journals Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Journal #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Total Debit</th>
                            <th class="text-end">Total Credit</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $jv)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.accounting.journals.show', $jv->id) }}" class="fw-bold font-monospace text-decoration-none">
                                        {{ $jv->journal_number }}
                                    </a>
                                </td>
                                <td>{{ $jv->journal_date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge {{ $jv->entry_type === 'manual' ? 'bg-info text-dark' : ($jv->entry_type === 'reversal' ? 'bg-danger' : 'bg-light text-dark border') }} text-uppercase">
                                        {{ $jv->entry_type }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($jv->description, 45) }}</td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($jv->total_debit, 2) }}</td>
                                <td class="text-end fw-bold text-danger">₹{{ number_format($jv->total_credit, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $jv->status === 'posted' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($jv->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.accounting.journals.show', $jv->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No journal entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($journals->hasPages())
                <div class="p-3 border-top">
                    {{ $journals->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
