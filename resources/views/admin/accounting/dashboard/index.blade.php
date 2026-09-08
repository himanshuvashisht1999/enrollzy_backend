@extends('admin.layouts.master')

@section('title', 'Accounting & Finance Dashboard')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-coins text-warning me-2"></i>Accounting & Finance</h3>
            <p class="text-muted small mb-0">Double-Entry Ledger, Financial Statements & Statutory Tax Management</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-invoice me-1"></i> New Sales Invoice
            </a>
            <a href="{{ route('admin.accounting.bills.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-file-invoice-dollar me-1"></i> New Vendor Bill
            </a>
            <a href="{{ route('admin.accounting.expenses.create') }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-receipt me-1"></i> New Expense
            </a>
            <a href="{{ route('admin.accounting.journals.create') }}" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-book me-1"></i> Journal Voucher
            </a>
        </div>
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

    <!-- 4 Main Pillar Cards -->
    <div class="row g-3 mb-4">
        <!-- Receivables -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Receivables (Debtors)</span>
                        <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="fas fa-arrow-down"></i></span>
                    </div>
                    <h3 class="fw-bold text-success mb-1">₹{{ number_format($totalReceivables, 2) }}</h3>
                    <div class="d-flex justify-content-between text-muted small mt-2">
                        <span>Overdue: <strong class="text-danger">₹{{ number_format($overdueReceivables, 2) }}</strong></span>
                        <a href="{{ route('admin.accounting.invoices.index') }}" class="text-decoration-none">View All <i class="fas fa-chevron-right small"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payables -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Payables (Creditors)</span>
                        <span class="badge bg-danger-subtle text-danger p-2 rounded-circle"><i class="fas fa-arrow-up"></i></span>
                    </div>
                    <h3 class="fw-bold text-danger mb-1">₹{{ number_format($totalPayables, 2) }}</h3>
                    <div class="d-flex justify-content-between text-muted small mt-2">
                        <span>Overdue: <strong class="text-danger">₹{{ number_format($overduePayables, 2) }}</strong></span>
                        <a href="{{ route('admin.accounting.bills.index') }}" class="text-decoration-none">View All <i class="fas fa-chevron-right small"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash & Bank Balances -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Total Cash & Bank</span>
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="fas fa-university"></i></span>
                    </div>
                    <h3 class="fw-bold text-primary mb-1">₹{{ number_format($totalBankCashBalance, 2) }}</h3>
                    <div class="d-flex justify-content-between text-muted small mt-2">
                        <span>{{ $bankAccounts->count() }} Accounts Active</span>
                        <a href="{{ route('admin.accounting.banking.index') }}" class="text-decoration-none">Banking <i class="fas fa-chevron-right small"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equity & Capital -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Total Capital & Funding</span>
                        <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="fas fa-hand-holding-usd"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-1">₹{{ number_format($totalFounderCapital + $totalInvestorFunding, 2) }}</h3>
                    <div class="d-flex justify-content-between text-muted small mt-2">
                        <span>Founders: <strong>₹{{ number_format($totalFounderCapital, 2) }}</strong></span>
                        <a href="{{ route('admin.accounting.funding.index') }}" class="text-decoration-none">Cap Table <i class="fas fa-chevron-right small"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Monthly Snapshot & Bank Accounts -->
    <div class="row g-3 mb-4">
        <!-- Monthly P&L Summary -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Performance Snapshot (This Month vs YTD)</h6>
                    <a href="{{ route('admin.accounting.reports.profit_loss') }}" class="btn btn-sm btn-outline-secondary">Full P&L Report</a>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-4 border-end">
                            <span class="text-muted small">Revenue (This Month)</span>
                            <h4 class="fw-bold text-success mt-1">₹{{ number_format($monthlyPnl['total_revenue'], 2) }}</h4>
                        </div>
                        <div class="col-md-4 border-end">
                            <span class="text-muted small">Expenses (This Month)</span>
                            <h4 class="fw-bold text-danger mt-1">₹{{ number_format($monthlyPnl['total_expenses'], 2) }}</h4>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small">Net Profit / (Loss)</span>
                            <h4 class="fw-bold {{ $monthlyPnl['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                                ₹{{ number_format($monthlyPnl['net_profit'], 2) }}
                            </h4>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-2 text-muted small">
                        <div class="col-6"><strong>Financial Year to Date (YTD) Revenue:</strong> ₹{{ number_format($ytdPnl['total_revenue'], 2) }}</div>
                        <div class="col-6 text-end"><strong>YTD Expenses:</strong> ₹{{ number_format($ytdPnl['total_expenses'], 2) }}</div>
                        <div class="col-6"><strong>YTD Net Profit:</strong> <span class="fw-bold {{ $ytdPnl['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format($ytdPnl['net_profit'], 2) }}</span></div>
                        <div class="col-6 text-end"><a href="{{ route('admin.accounting.reports.balance_sheet') }}" class="text-primary text-decoration-none">View Balance Sheet &rarr;</a></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank & Cash Balances List -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-wallet text-success me-2"></i>Bank & Cash Accounts</h6>
                    <a href="{{ route('admin.accounting.banking.create') }}" class="btn btn-sm btn-link text-decoration-none p-0">+ Add Bank</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($bankAccounts as $bank)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold">{{ $bank->account_name }}</div>
                                    <small class="text-muted">{{ $bank->bank_name }} &bull; {{ $bank->account_number_masked }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-dark">₹{{ number_format($bank->current_balance, 2) }}</div>
                                    <a href="{{ route('admin.accounting.banking.show', $bank->id) }}" class="small text-decoration-none">Statement</a>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3">No active bank accounts found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Third Row: Pending Approvals & Overdue Invoices -->
    <div class="row g-3 mb-4">
        <!-- Pending Expense Approvals -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-clock text-warning me-2"></i>Pending Expense Approvals</h6>
                    <a href="{{ route('admin.accounting.expenses.index', ['status' => 'submitted']) }}" class="btn btn-sm btn-link text-decoration-none p-0">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Expense</th>
                                    <th>Submitted By</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingExpenses as $exp)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.accounting.expenses.show', $exp->id) }}" class="fw-bold text-decoration-none">
                                                {{ $exp->expense_number }}
                                            </a>
                                            <div class="small text-muted">{{ Str::limit($exp->title, 20) }}</div>
                                        </td>
                                        <td>{{ $exp->submitter->name ?? 'Staff' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $exp->categoryAccount->name ?? 'General' }}</span></td>
                                        <td class="fw-bold">₹{{ number_format($exp->total_amount, 2) }}</td>
                                        <td>
                                            <a href="{{ route('admin.accounting.expenses.show', $exp->id) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No expenses pending approval.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Receivables -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-exclamation-circle text-danger me-2"></i>Overdue Invoices</h6>
                    <a href="{{ route('admin.accounting.reports.customer_ageing') }}" class="btn btn-sm btn-link text-decoration-none p-0">Ageing Report</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Due Date</th>
                                    <th>Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueInvoices as $inv)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="fw-bold text-decoration-none">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $inv->party->name ?? 'Customer' }}</td>
                                        <td class="text-danger fw-bold small">{{ $inv->due_date->format('d M Y') }}</td>
                                        <td class="fw-bold text-danger">₹{{ number_format($inv->balance_due, 2) }}</td>
                                        <td>
                                            <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-success">Receive</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No overdue customer invoices. Great!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Audit / Journal Transactions -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fas fa-history text-secondary me-2"></i>Recent Financial Transactions</h6>
            <a href="{{ route('admin.accounting.journals.index') }}" class="btn btn-sm btn-outline-secondary">View Journal Vouchers</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Txn #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Posted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $txn)
                            <tr>
                                <td class="fw-bold font-monospace">{{ $txn->transaction_number }}</td>
                                <td>{{ $txn->transaction_date->format('d M Y') }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', $txn->transaction_type) }}</span></td>
                                <td>{{ Str::limit($txn->description, 35) }}</td>
                                <td class="fw-bold">₹{{ number_format($txn->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $txn->status === 'posted' ? 'bg-success' : ($txn->status === 'reversed' ? 'bg-danger' : 'bg-warning') }}">
                                        {{ ucfirst($txn->status) }}
                                    </span>
                                </td>
                                <td>{{ $txn->creator->name ?? 'Admin' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No transactions posted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
