@extends('admin.layouts.master')

@section('title', 'Financial Reports & Tax Registers')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Financial Reports & Statements</h3>
            <p class="text-muted small mb-0">Statutory Accounting Statements, General Ledger, Ageing & Tax Registers</p>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row g-4">
        <!-- 1. Trial Balance -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-primary">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="fas fa-balance-scale fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">Core Statement</span>
                        </div>
                        <h5 class="fw-bold mb-2">Trial Balance</h5>
                        <p class="text-muted small">Summary of all debit and credit account balances from the general ledger to verify mathematical accuracy.</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.trial_balance') }}" class="btn btn-outline-primary btn-sm w-100">
                            View Trial Balance &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Profit & Loss -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-success">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="fas fa-chart-line fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">Income Statement</span>
                        </div>
                        <h5 class="fw-bold mb-2">Profit & Loss (P&L)</h5>
                        <p class="text-muted small">Comprehensive statement showing revenues, cost of services, operating expenses, and net profit / loss.</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.profit_loss') }}" class="btn btn-outline-success btn-sm w-100">
                            View Profit & Loss &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Balance Sheet -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-warning">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="fas fa-university fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">Balance Sheet</span>
                        </div>
                        <h5 class="fw-bold mb-2">Balance Sheet</h5>
                        <p class="text-muted small">Financial snapshot showing company assets, liabilities, and equity (Assets = Liabilities + Equity).</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.balance_sheet') }}" class="btn btn-outline-warning text-dark btn-sm w-100">
                            View Balance Sheet &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. General Ledger -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-info">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="fas fa-book-open fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">General Ledger</span>
                        </div>
                        <h5 class="fw-bold mb-2">General Ledger</h5>
                        <p class="text-muted small">Complete journal transaction line entries and running balance breakdown for any Chart of Account.</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.general_ledger') }}" class="btn btn-outline-info text-dark btn-sm w-100">
                            View General Ledger &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Customer Ageing -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-danger">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-danger-subtle text-danger p-2 rounded-circle"><i class="fas fa-user-clock fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">Receivables</span>
                        </div>
                        <h5 class="fw-bold mb-2">Customer Ageing (Debtors)</h5>
                        <p class="text-muted small">Ageing breakdown of unpaid customer invoices (Current, 1-30, 31-60, 61-90, 90+ days overdue).</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.customer_ageing') }}" class="btn btn-outline-danger btn-sm w-100">
                            View Customer Ageing &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Vendor Ageing -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-secondary">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-secondary-subtle text-secondary p-2 rounded-circle"><i class="fas fa-truck-loading fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">Payables</span>
                        </div>
                        <h5 class="fw-bold mb-2">Vendor Ageing (Creditors)</h5>
                        <p class="text-muted small">Ageing breakdown of unpaid supplier bills (Current, 1-30, 31-60, 61-90, 90+ days overdue).</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.vendor_ageing') }}" class="btn btn-outline-secondary btn-sm w-100">
                            View Vendor Ageing &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. GST Summary Report -->
        <div class="col-md-6 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-dark">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-dark-subtle text-dark p-2 rounded-circle"><i class="fas fa-receipt fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">Statutory Tax</span>
                        </div>
                        <h5 class="fw-bold mb-2">GST Summary Register (GSTR-1 & GSTR-3B)</h5>
                        <p class="text-muted small">Output GST collected on customer sales vs Input GST tax credit claimed on vendor purchases and expenses.</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.gst_report') }}" class="btn btn-outline-dark btn-sm w-100">
                            View GST Register &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. TDS Register -->
        <div class="col-md-6 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 border-info">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="fas fa-percentage fa-lg"></i></span>
                            <span class="badge bg-light text-dark border">Statutory Tax</span>
                        </div>
                        <h5 class="fw-bold mb-2">TDS Summary Register (Form 26Q / 16A)</h5>
                        <p class="text-muted small">TDS withheld on vendor payments (Sections 194C, 194J, 194I) & TDS deducted by clients on receivables.</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.accounting.reports.tds_report') }}" class="btn btn-outline-info text-dark btn-sm w-100">
                            View TDS Register &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
