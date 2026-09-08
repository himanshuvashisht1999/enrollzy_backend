@extends('admin.layouts.master')

@section('title', 'Chart of Accounts')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-sitemap text-primary me-2"></i>Chart of Accounts (COA)</h3>
            <p class="text-muted small mb-0">Hierarchical General Ledger Architecture (Assets, Liabilities, Equity, Revenue, Expenses, Taxes)</p>
        </div>
        <a href="{{ route('admin.accounting.chart_of_accounts.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Account
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

    <!-- Category Tabs -->
    <ul class="nav nav-pills mb-3" id="coaTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" id="all-tab" data-bs-toggle="pill" data-bs-target="#tab-all" type="button">All Accounts ({{ $accounts->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="asset-tab" data-bs-toggle="pill" data-bs-target="#tab-asset" type="button">1000 Assets ({{ $groupedAccounts['asset']->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="liability-tab" data-bs-toggle="pill" data-bs-target="#tab-liability" type="button">2000 Liabilities ({{ $groupedAccounts['liability']->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="equity-tab" data-bs-toggle="pill" data-bs-target="#tab-equity" type="button">3000 Equity ({{ $groupedAccounts['equity']->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="revenue-tab" data-bs-toggle="pill" data-bs-target="#tab-revenue" type="button">4000 Revenue ({{ $groupedAccounts['revenue']->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="expense-tab" data-bs-toggle="pill" data-bs-target="#tab-expense" type="button">5000 Expenses ({{ $groupedAccounts['expense']->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="tax-tab" data-bs-toggle="pill" data-bs-target="#tab-tax" type="button">6000 Taxes ({{ $groupedAccounts['tax']->count() }})</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- ALL TAB -->
        <div class="tab-pane fade show active" id="tab-all">
            @include('admin.accounting.chart_of_accounts.partials.table', ['accountsList' => $accounts])
        </div>
        <!-- ASSETS TAB -->
        <div class="tab-pane fade" id="tab-asset">
            @include('admin.accounting.chart_of_accounts.partials.table', ['accountsList' => $groupedAccounts['asset']])
        </div>
        <!-- LIABILITIES TAB -->
        <div class="tab-pane fade" id="tab-liability">
            @include('admin.accounting.chart_of_accounts.partials.table', ['accountsList' => $groupedAccounts['liability']])
        </div>
        <!-- EQUITY TAB -->
        <div class="tab-pane fade" id="tab-equity">
            @include('admin.accounting.chart_of_accounts.partials.table', ['accountsList' => $groupedAccounts['equity']])
        </div>
        <!-- REVENUE TAB -->
        <div class="tab-pane fade" id="tab-revenue">
            @include('admin.accounting.chart_of_accounts.partials.table', ['accountsList' => $groupedAccounts['revenue']])
        </div>
        <!-- EXPENSE TAB -->
        <div class="tab-pane fade" id="tab-expense">
            @include('admin.accounting.chart_of_accounts.partials.table', ['accountsList' => $groupedAccounts['expense']])
        </div>
        <!-- TAX TAB -->
        <div class="tab-pane fade" id="tab-tax">
            @include('admin.accounting.chart_of_accounts.partials.table', ['accountsList' => $groupedAccounts['tax']])
        </div>
    </div>
</div>
@endsection
