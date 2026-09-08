@extends('admin.layouts.master')

@section('title', 'Funding & Capital Management')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-hand-holding-usd text-warning me-2"></i>Funding & Capital Accounts</h3>
            <p class="text-muted small mb-0">Track Founder Capital Infusion, 3rd-Party Equity Investments, Loans & Debt (Independent of Operational Revenue)</p>
        </div>
        <a href="{{ route('admin.accounting.funding.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Record Funding / Capital
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

    <!-- 2 Main Capital Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-warning">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Total Founder Capital</span>
                    <h3 class="fw-bold text-warning mt-2">₹{{ number_format($totalFounderCapital, 2) }}</h3>
                    <p class="small text-muted mb-0">Total equity capital contributed by company founders.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-primary">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-bold">Total 3rd-Party Funding & Debt</span>
                    <h3 class="fw-bold text-primary mt-2">₹{{ number_format($totalInvestorFunding, 2) }}</h3>
                    <p class="small text-muted mb-0">Angel, venture equity, convertible notes, and institutional funding.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-pills mb-3" id="fundingTabs">
        <li class="nav-item">
            <a class="nav-link {{ !request('type') ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.funding.index') }}">All Capital Records</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') === 'founder' ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.funding.index', ['type' => 'founder']) }}">
                Founder Capital
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') === 'investor' ? 'active' : '' }} fw-bold" href="{{ route('admin.accounting.funding.index', ['type' => 'investor']) }}">
                Investor & Debt Funding
            </a>
        </li>
    </ul>

    <!-- Funding Records Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Funding #</th>
                            <th>Investor / Founder</th>
                            <th>Funding Type</th>
                            <th>Received Date</th>
                            <th>Bank Account Credited</th>
                            <th>Equity Account</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fundingRecords as $fnd)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.accounting.funding.show', $fnd->id) }}" class="fw-bold font-monospace text-decoration-none">
                                        {{ $fnd->funding_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $fnd->party->name ?? 'Founder/Investor' }}</div>
                                    @if($fnd->reference_number)
                                        <small class="text-muted">Ref: {{ $fnd->reference_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ in_array($fnd->funding_type, ['founder_capital', 'founder_current']) ? 'bg-warning-subtle text-dark' : 'bg-primary-subtle text-primary' }}">
                                        {{ strtoupper(str_replace('_', ' ', $fnd->funding_type)) }}
                                    </span>
                                </td>
                                <td>{{ $fnd->received_date->format('d M Y') }}</td>
                                <td>
                                    <small>{{ $fnd->depositBankAccount->account_name ?? 'Bank' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $fnd->equityLedgerAccount->account_code ?? '' }} {{ $fnd->equityLedgerAccount->name ?? 'Equity' }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($fnd->amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $fnd->status === 'posted' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($fnd->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounting.funding.show', $fnd->id) }}" class="btn btn-outline-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($fnd->status === 'draft')
                                            <form action="{{ route('admin.accounting.funding.post', $fnd->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Post this capital infusion to the General Ledger?');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Post to Ledger">
                                                    <i class="fas fa-check"></i> Post
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No funding records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($fundingRecords->hasPages())
                <div class="p-3 border-top">
                    {{ $fundingRecords->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
